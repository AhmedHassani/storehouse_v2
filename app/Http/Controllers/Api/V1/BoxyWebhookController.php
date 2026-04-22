<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderDetail;
use App\CentralLogics\Helpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use function App\CentralLogics\translate;

class BoxyWebhookController extends Controller
{
    /**
     * Handle Boxy Webhook status updates
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(Request $request)
    {
        // 1. Force HTTPS (Security Best Practice)
        if (!$request->secure() && env('APP_ENV') === 'production') {
            Log::channel('boxy_webhook')->error('Insecure connection attempt (Non-HTTPS)');
            return response()->json(['success' => false, 'message' => 'Secure connection required'], 403);
        }

        // 2. Security Check (Static Security Key)
        $secureKey = env('BOXY_WEBHOOK_SIGNATURE');
        $providedKey = $request->header('X-Security-Key');

        if (empty($providedKey) || !hash_equals((string)$secureKey, (string)$providedKey)) {
            Log::channel('boxy_webhook')->warning('Boxy Webhook: Unauthorized attempt (Invalid Security Key)', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
            return response()->json(['success' => false, 'message' => 'Unauthorized: Invalid Security Key'], 401);
        }

        // 3. Extract Data
        $uid = $request->input('uid');
        $boxyStatus = strtolower($request->input('status')); // Using Boxy status directly
        $reason = $request->input('reason', '');

        // 4. Logging for Audit
        Log::channel('boxy_webhook')->info('Boxy Webhook: Request received', [
            'uid' => $uid,
            'status' => $boxyStatus,
            'reason' => $reason,
            'ip' => $request->ip()
        ]);

        // 5. Find Order
        $order = Order::where('boxy_uid', $uid)->first();

        if (!$order) {
            Log::channel('boxy_webhook')->error('Order not found', ['boxy_uid' => $uid]);
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        // Avoid duplicate processing if status is same
        if ($order->order_status === $boxyStatus) {
            return response()->json(['success' => true, 'message' => 'Status already up to date']);
        }

        DB::beginTransaction();
        try {
            // 6. Inventory Management (Restore stock for failed/canceled/returned statuses)
            // We use Boxy status names here
            $failedStatuses = ['canceled', 'rejected', 'wrong_address', 'returned', 'no_answer'];
            if (in_array($boxyStatus, $failedStatuses)) {
                $this->restoreStock($order);
            }

            // 7. Update Order status directly to Boxy status
            $order->order_status = $boxyStatus;
            if ($boxyStatus === 'delivered') {
                $order->payment_status = 'paid';
            }
            $order->save();

            // 8. Send Notifications
            $fcm_token = $order->customer ? $order->customer->cm_fcm_token : null;
            if ($fcm_token) {
                // Note: Helpers::order_status_update_message might need to be updated to support Boxy status strings
                $value = Helpers::order_status_update_message($boxyStatus) ?: "Your order status is now: " . $boxyStatus;
                
                $data = [
                    'title' => translate('Order Update'),
                    'description' => $value,
                    'order_id' => $order->id,
                    'image' => '',
                    'type' => 'order_status',
                ];
                Helpers::send_push_notif_to_device($fcm_token, $data);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Order status updated to ' . $boxyStatus]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('boxy_webhook')->error('Transaction failed', [
                'error' => $e->getMessage(),
                'order_id' => $order->id
            ]);
            return response()->json(['success' => false, 'message' => 'Internal server error'], 500);
        }
    }

    /**
     * Helper to restore stock
     */
    private function restoreStock($order)
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
