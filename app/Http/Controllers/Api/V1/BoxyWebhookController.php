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
        // 1. Log to Database for tracking
        try {
            \App\Models\BoxyWebhookLog::create([
                'boxy_uid'   => $request->input('uid') ?? $request->input('order_uid'),
                'event_type' => $request->input('status') ?? $request->input('event'),
                'payload'    => $request->all(),
                'headers'    => $request->headers->all(),
                'ip_address' => $request->ip()
            ]);
        } catch (\Exception $e) {
            Log::error('Boxy Webhook Logging Failed: ' . $e->getMessage());
        }

        // 2. Security Check (Optional but recommended)
        $secureKey = env('BOXY_WEBHOOK_SIGNATURE');
        $providedKey = $request->header('X-Security-Key');

        if (empty($providedKey) || !hash_equals((string)$secureKey, (string)$providedKey)) {
            Log::channel('boxy_webhook')->warning('Boxy Webhook: Unauthorized attempt (Invalid Security Key)', [
                'ip' => $request->ip()
            ]);
            // Still return 200/success to avoid Boxy retrying if you want, but 401 is more correct.
            // For now, let's keep it 200 so logs are collected even if key is wrong during setup.
        }

        // 3. Extract Data for Logging to File (Audit)
        $uid = $request->input('uid') ?? $request->input('order_uid');
        $boxyStatus = strtolower($request->input('status', 'unknown'));
        
        Log::channel('boxy_webhook')->info('Boxy Webhook: Request received and logged to DB', [
            'uid' => $uid,
            'status' => $boxyStatus,
            'payload' => $request->all()
        ]);

        /* 
         * -------------------------------------------------------------------------
         * NOTE: Active processing is currently disabled as requested.
         * The code below is commented out and will be re-enabled once 
         * the user is ready to handle specific actions.
         * -------------------------------------------------------------------------
         */

        /*
        $order = Order::where('boxy_uid', $uid)->first();
        if ($order) {
            DB::beginTransaction();
            try {
                $failedStatuses = ['canceled', 'rejected', 'wrong_address', 'returned', 'no_answer'];
                if (in_array($boxyStatus, $failedStatuses)) {
                    $this->restoreStock($order);
                }
                $order->order_status = $boxyStatus;
                if ($boxyStatus === 'delivered') {
                    $order->payment_status = 'paid';
                }
                $order->save();
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
            }
        }
        */

        return response()->json(['success' => true, 'message' => 'Webhook received and logged']);
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
