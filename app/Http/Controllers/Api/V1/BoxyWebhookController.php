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
        // 1. Log to Database for tracking (Simple logging as requested)
        try {
            \App\Models\BoxyWebhookLog::create([
                'boxy_uid'   => $request->input('platform_code') ?? $request->input('uid') ?? $request->input('order_uid') ?? 'Unknown',
                'event_type' => $request->input('event') ?? $request->input('status') ?? 'Unknown',
                'payload'    => $request->all(),
                'headers'    => $request->headers->all(),
                'ip_address' => $request->ip()
            ]);
        } catch (\Exception $e) {
            Log::error('Boxy Webhook Logging Failed: ' . $e->getMessage());
        }

        // 2. Extract Data for Logging to File (Audit)
        $uid = $request->input('platform_code') ?? $request->input('uid') ?? $request->input('order_uid');
        $event = $request->input('event') ?? $request->input('status', 'unknown');
        
        Log::channel('boxy_webhook')->info('Boxy Webhook Received', [
            'uid' => $uid,
            'event' => $event,
            'payload' => $request->all()
        ]);

        return response()->json([
            'success' => true, 
            'message' => 'Webhook received and logged',
            'timestamp' => now()->toDateTimeString()
        ]);
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
