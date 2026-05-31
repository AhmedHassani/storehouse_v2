<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Services\BoxyDeliveryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BoxyWebhookController extends Controller
{
    /**
     * Handle Boxy Webhook status updates.
     *
     * Two kinds of payloads arrive at this endpoint:
     *
     *  A) "self" type  – sent by YOUR own system when a self-delivery driver
     *     updates an order status. The payload contains:
     *       { "type": "self", "order_id": 100081, "status": "delivered", ... }
     *     Action: update local order status → also sync back to Boxy if the
     *             order was also submitted to Boxy (has a boxy_uid).
     *
     *  B) Boxy events – sent by the Boxy platform when their drivers update
     *     an order. The payload contains:
     *       { "event": "order.delivered", "platform_code": "BXO-...", ... }
     *     Action: find the local order by boxy_platform_code / boxy_uid and
     *             update its order_status accordingly.
     *
     * @param  Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(Request $request)
    {
        // ── 1. Always log to DB ───────────────────────────────────────────
        try {
            \App\Models\BoxyWebhookLog::create([
                'boxy_uid'   => $request->input('platform_code')
                             ?? $request->input('uid')
                             ?? $request->input('order_uid')
                             ?? 'Unknown',
                'event_type' => $request->input('event')
                             ?? $request->input('status')
                             ?? 'Unknown',
                'payload'    => $request->all(),
                'headers'    => $request->headers->all(),
                'ip_address' => $request->ip(),
            ]);
        } catch (\Exception $e) {
            Log::error('Boxy Webhook Logging Failed: ' . $e->getMessage());
        }

        // ── 2. Channel log ────────────────────────────────────────────────
        Log::channel('boxy_webhook')->info('Boxy Webhook Received', [
            'type'    => $request->input('type'),
            'event'   => $request->input('event'),
            'status'  => $request->input('status'),
            'order_id'=> $request->input('order_id'),
            'platform_code' => $request->input('platform_code'),
        ]);

        // ================================================================
        // BRANCH A – self-delivery status update
        // ================================================================
        if ($request->input('type') === 'self') {
            return $this->handleSelfEvent($request);
        }

        // ================================================================
        // BRANCH B – Boxy-native event
        // ================================================================
        return $this->handleBoxyEvent($request);
    }

    // ─────────────────────────────────────────────────────────────────────
    // Branch A: self-delivery
    // ─────────────────────────────────────────────────────────────────────
    private function handleSelfEvent(Request $request)
    {
        $order_id        = $request->input('order_id');
        $incoming_status = $request->input('status');

        // self-status → local order_status
        $status_map = [
            'out_for_delivery'   => 'out_for_delivery',
            'delivered'          => 'delivered',
            'returned'           => 'returned',
            'partially_returned' => 'returned',  // closest match
            'not_received'       => 'failed',     // closest match
            'postponed'          => 'pending',    // closest match
        ];

        if (!$order_id || !$incoming_status || !isset($status_map[$incoming_status])) {
            Log::channel('boxy_webhook')->warning('Self webhook: missing order_id or unknown status', [
                'order_id' => $order_id,
                'status'   => $incoming_status,
            ]);
            return response()->json(['success' => true, 'message' => 'Self webhook logged, no action taken']);
        }

        $mapped_status = $status_map[$incoming_status];
        $order         = Order::find($order_id);

        if (!$order) {
            Log::channel('boxy_webhook')->warning("Self webhook: order #{$order_id} not found");
            return response()->json(['success' => true, 'message' => 'Order not found']);
        }

        // Skip terminal orders
        if (in_array($order->order_status, ['returned', 'failed', 'canceled', 'delivered', 'deleted'])) {
            Log::channel('boxy_webhook')->info(
                "Self webhook: order #{$order_id} already in terminal state ({$order->order_status}), skipped."
            );
            return response()->json(['success' => true, 'message' => 'Order already in terminal state']);
        }

        $previous        = $order->order_status;
        $order->order_status = $mapped_status;
        $order->save();

        Log::channel('boxy_webhook')->info(
            "Self order #{$order_id}: [{$previous}] → [{$mapped_status}]"
        );

        // Restore stock for failed/returned/canceled/deleted orders
        if (in_array($mapped_status, ['returned', 'failed', 'canceled', 'deleted'])) {
            $this->restoreStock($order);
        }

        // Sync back to Boxy if this order was also sent there
        if (!empty($order->boxy_uid)) {
            $this->syncStatusToBoxy($order, $incoming_status);
        }

        return response()->json([
            'success'   => true,
            'message'   => "Self order #{$order_id} updated to {$mapped_status}",
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // Branch B: Boxy-native event
    // ─────────────────────────────────────────────────────────────────────
    private function handleBoxyEvent(Request $request)
    {
        $platform_code = $request->input('platform_code');
        $boxy_event    = $request->input('event');

        // Boxy event name → local order_status
        // Events not listed here are logged only (no status change).
        $event_status_map = [

            // ── Collection from merchant ──────────────────────────────────
            'order.collecting'                  => 'processing',
            'order.out_for_collecting'          => 'processing',
            'order.collected'                   => 'processing',

            // ── Warehouse / sorting ───────────────────────────────────────
            'order.received_warehouse'          => 'processing',
            'order.sorted'                      => 'processing',
            'order.received_carrier_warehouse'  => 'processing',
            'order.inventory'                   => 'processing',

            // ── Scheduled / confirmed ─────────────────────────────────────
            'order.scheduled'                   => 'confirmed',
            'order.hold_resolved'               => 'confirmed',
            'order.resend'                      => 'confirmed',

            // ── In transit / out for delivery ─────────────────────────────
            'order.transferred_carrier'         => 'out_for_delivery',
            'order.in_transit'                  => 'out_for_delivery',
            'order.out_for_delivery'            => 'out_for_delivery',

            // ── Delivered ─────────────────────────────────────────────────
            'order.delivered'                   => 'delivered',
            'order.partially_delivered'         => 'delivered',

            // ── On hold / postponed ───────────────────────────────────────
            'order.on_hold'                     => 'on_hold',
            'order.exception'                   => 'on_hold',
            'order.postponed'                   => 'pending',

            // ── Retry ─────────────────────────────────────────────────────
            'order.retry_request'               => 'pending',
            'order retry request'               => 'pending',   // space variant

            // ── Return-to-origin (RTO) lifecycle ──────────────────────────
            'order.rto_requested'               => 'returned',
            'order.rto_request_pending'         => 'returned',
            'order rto request pending'         => 'returned',  // space variant
            'order.rto_scheduled'               => 'returned',
            'order.rto_collecting'              => 'returned',
            'order rto collecting'              => 'returned',  // space variant
            'order.rto_in_transit'              => 'returned',
            'order.rto_received_warehouse'      => 'returned',
            'order.rto_received_carrier_warehouse' => 'returned',
            'order.rto_partially_delivered'     => 'returned',
            'order.rto_delivered'               => 'returned',

            // ── Cancellation / deletion ───────────────────────────────────
            'order.cancelled'                   => 'canceled',
            'order.canceled'                    => 'canceled',
            'order.deleted'                     => 'canceled',

            // ── Claim events (log only — no order status change) ──────────
            // claim.filed, claim.approved, claim.rejected, claim.closed,
            // claim.compensated, claim compensation failed, order.claim settled
            // → NOT included here → will be logged only

            // ── Informational only (no status change needed) ──────────────
            // order.new, order.requested, label.generated
            // → NOT included here → will be logged only
        ];

        if (!$platform_code || !$boxy_event) {
            Log::channel('boxy_webhook')->info('Boxy event: missing platform_code or event — logged only');
            return response()->json(['success' => true, 'message' => 'Logged, no action taken']);
        }

        if (!array_key_exists($boxy_event, $event_status_map)) {
            Log::channel('boxy_webhook')->info("Boxy event [{$boxy_event}] not mapped — logged only");
            return response()->json(['success' => true, 'message' => 'Event not mapped, logged only']);
        }

        $mapped_status = $event_status_map[$boxy_event];

        // Find the local order by Boxy's platform_code
        $order = Order::where('boxy_platform_code', $platform_code)
                      ->orWhere('boxy_uid', $platform_code)
                      ->first();

        if (!$order) {
            Log::channel('boxy_webhook')->warning(
                "Boxy event [{$boxy_event}]: no local order found for platform_code={$platform_code}"
            );
            return response()->json(['success' => true, 'message' => 'No matching order found']);
        }

        // Skip terminal orders
        if (in_array($order->order_status, ['returned', 'failed', 'canceled', 'delivered', 'deleted'])) {
            Log::channel('boxy_webhook')->info(
                "Boxy event [{$boxy_event}]: order #{$order->id} already in terminal state ({$order->order_status}), skipped."
            );
            return response()->json(['success' => true, 'message' => 'Order already in terminal state']);
        }

        $previous            = $order->order_status;
        $order->order_status = $mapped_status;
        $order->save();

        Log::channel('boxy_webhook')->info(
            "Boxy event [{$boxy_event}] → order #{$order->id} [{$previous}] → [{$mapped_status}]"
        );

        // Restore stock for failed/returned/canceled/deleted
        if (in_array($mapped_status, ['returned', 'failed', 'canceled', 'deleted'])) {
            $this->restoreStock($order);
        }

        return response()->json([
            'success'   => true,
            'message'   => "Order #{$order->id} updated to {$mapped_status}",
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // Push a self-delivery status back to Boxy (keeps both sides in sync)
    // ─────────────────────────────────────────────────────────────────────
    private function syncStatusToBoxy(Order $order, string $selfStatus): void
    {
        // Only push statuses that Boxy cares about
        $syncable = ['delivered', 'returned', 'out_for_delivery'];
        if (!in_array($selfStatus, $syncable)) {
            return;
        }

        try {
            $result = app(BoxyDeliveryService::class)->updateOrder($order);

            if (!($result['success'] ?? false)) {
                Log::channel('boxy_webhook')->warning(
                    "Failed to sync self status [{$selfStatus}] to Boxy for order #{$order->id}: " .
                    json_encode($result)
                );
            } else {
                Log::channel('boxy_webhook')->info(
                    "Synced self status [{$selfStatus}] to Boxy for order #{$order->id}"
                );
            }
        } catch (\Exception $e) {
            Log::error("syncStatusToBoxy error for order #{$order->id}: " . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // Restore product stock for returned / failed / canceled orders
    // ─────────────────────────────────────────────────────────────────────
    private function restoreStock(Order $order): void
    {
        foreach ($order->details as $detail) {
            if ($detail['is_stock_decreased'] == 1) {
                $product = Product::find($detail['product_id']);
                if ($product) {
                    $type = json_decode($detail['variation'])[0]->type ?? null;
                    if ($type) {
                        $var_data = json_decode($product['variations'], true);
                        foreach ($var_data as &$item) {
                            if ($item['type'] == $type) {
                                $item['stock'] += $detail['quantity'];
                            }
                        }
                        $product->variations = json_encode($var_data);
                    } else {
                        $product->total_stock += $detail['quantity'];
                    }
                    $product->save();
                    $detail->is_stock_decreased = 0;
                    $detail->save();
                }
            }
        }
    }
}
