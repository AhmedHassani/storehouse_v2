<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BoxyDeliveryService
{
    private $baseUrl = 'https://api-stg.tryboxy.dev/api/v1/merchants/orders/request';
    private $apiKey = '01KPRA7HNJVNSC0MDK1V5RRTK1';
    private $apiSecret = 'M@AInv7*_6=#hzWwbQ&jIUC=vkAwqdhMEx#wlFZdLWv4bRxG64ig4LoEd@Aw-DSo&bY$Rztoh+M00ShRq?-VUOu^fhqFGCBSTV%G7sEAPWY^OSBWl?mC2VD%w7a07elWyeBPQKF&rGNBBR#75fksgkt2wlu?b6gS^Q+1Jm7TaO%7FxZHaxAC0Qfzn%wozX2oZOAHbS_poL@nV*e_OuTPPAHg=mTvW=6&oGIK4zmMrny5s&@SfXBNsHQtMT&t9hk';

    /**
     * Send order to Boxy Delivery API
     *
     * @param Order $order
     * @return array
     */
    /**
     * Send order to Boxy Delivery API
     *
     * @param Order $order
     * @return array
     */
    public function sendOrder(Order $order)
    {
        try {
            $payload = $this->preparePayload($order);
            if (isset($payload['error'])) {
                return ['success' => false, 'message' => $payload['error']];
            }

            Log::info("========== SENDING ORDER TO BOXY ==========");
            Log::info("URL: " . $this->baseUrl);
            Log::info("Payload: " . json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            Log::info("===========================================");

            $response = Http::withHeaders([
                'api-key' => $this->apiKey,
                'api-secret' => $this->apiSecret,
                'Content-Type' => 'application/json'
            ])->post($this->baseUrl, $payload);

            if (!$response->successful()) {
                Log::error("========== BOXY SEND FAILED ==========");
                Log::error("Status: " . $response->status());
                Log::error("Response: " . $response->body());
                Log::error("======================================");
            } else {
                Log::info("========== BOXY RESPONSE SUCCESS ==========");
                Log::info($response->body());
                Log::info("===========================================");
            }

            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()];
            } else {
                return ['success' => false, 'message' => $response->body()];
            }

        } catch (\Exception $e) {
            Log::error("Boxy API Error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Update existing order in Boxy Delivery API
     *
     * @param Order $order
     * @return array
     */
    public function updateOrder(Order $order)
    {
        if (empty($order->boxy_uid)) {
            return ['success' => false, 'message' => 'Boxy UID not found for this order'];
        }

        try {
            $payload = $this->preparePayload($order);
            if (isset($payload['error'])) {
                return ['success' => false, 'message' => $payload['error']];
            }

            // Based on latest curl example, update uses the order uid in the path
            $updateUrl = "https://api-stg.tryboxy.dev/api/v1/merchants/orders/{$order->boxy_uid}";
            

            Log::info("========== UPDATING ORDER IN BOXY ==========");
            Log::info("URL: " . $updateUrl);
            Log::info("Payload: " . json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            Log::info("=============================================");

            $response = Http::withHeaders([
                'api-key' => $this->apiKey,
                'api-secret' => $this->apiSecret,
                'Content-Type' => 'application/json'
            ])->patch($updateUrl, $payload);

            if (!$response->successful()) {
                Log::error("========== BOXY UPDATE FAILED ==========");
                Log::error("Status: " . $response->status());
                Log::error("Response: " . $response->body());
                Log::error("========================================");
            } else {
                Log::info("========== BOXY UPDATE SUCCESS ==========");
                Log::info($response->body());
                Log::info("===========================================");
            }

            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()];
            } else {
                return [
                    'success' => false, 
                    'message' => 'Boxy API Error (' . $response->status() . ')', 
                    'body' => $response->json() ?: $response->body()
                ];
            }

        } catch (\Exception $e) {
            Log::error("Boxy Update API Error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Send a pick-up request to Boxy using the bulk-request endpoint
     * POST /api/v1/merchants/pick-ups/bulk-request?filter_uids[]=UID
     *
     * @param Order $order
     * @return array
     */
    public function setReadyToPickUp(Order $order): array
    {
        if (empty($order->boxy_uid)) {
            return ['success' => false, 'message' => 'No Boxy UID found'];
        }

        try {
            $url = "https://api-stg.tryboxy.dev/api/v1/merchants/pick-ups/bulk-request?filter_uids[]={$order->boxy_uid}";

            Log::info("===== BOXY BULK PICK-UP REQUEST =====");
            Log::info("URL: " . $url);
            Log::info("=====================================");

            $response = Http::withHeaders([
                'api-key'    => $this->apiKey,
                'api-secret' => $this->apiSecret,
            ])->post($url);

            Log::info("===== BOXY RESPONSE =====");
            Log::info("Status: " . $response->status());
            Log::info("Body: " . $response->body());
            Log::info("=========================");

            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()];
            }

            return [
                'success' => false,
                'message' => 'Boxy API Error (' . $response->status() . ')',
                'body'    => $response->json() ?: $response->body(),
            ];

        } catch (\Exception $e) {
            Log::error("Boxy setReadyToPickUp Error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }


    /**
     * Bulk pick-up request for multiple orders
     * POST /api/v1/merchants/pick-ups/bulk-request?filter_uids[]=UID1&filter_uids[]=UID2
     *
     * @param array $boxyUids
     * @return array
     */
    public function bulkSetReadyToPickUp(array $boxyUids): array
    {
        if (empty($boxyUids)) {
            return ['success' => false, 'message' => 'No Boxy UIDs provided'];
        }

        try {
            $queryString = implode('&', array_map(fn($uid) => 'filter_uids[]=' . urlencode($uid), $boxyUids));
            $url = "https://api-stg.tryboxy.dev/api/v1/merchants/pick-ups/bulk-request?{$queryString}";

            Log::info("===== BOXY BULK PICK-UP (MULTIPLE) =====");
            Log::info("URL: " . $url);
            Log::info("UIDs: " . implode(', ', $boxyUids));
            Log::info("=========================================");

            $response = Http::withHeaders([
                'api-key'    => $this->apiKey,
                'api-secret' => $this->apiSecret,
            ])->post($url);

            Log::info("===== BOXY RESPONSE =====");
            Log::info("Status: " . $response->status());
            Log::info("Body: " . $response->body());
            Log::info("=========================");

            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()];
            }

            return [
                'success' => false,
                'message' => 'Boxy API Error (' . $response->status() . ')',
                'body'    => $response->json() ?: $response->body(),
            ];

        } catch (\Exception $e) {
            Log::error("Boxy bulkSetReadyToPickUp Error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Fetch order pick-up labels (PDF) from Boxy
     *
     * @param string $uid
     * @return mixed
     */
    public function getOrderLabel(string $uid)
    {
        try {
            $url = "https://api-stg.tryboxy.dev/api/v1/merchants/orders/pick-up-labels?order_uid[]={$uid}";
            
            $response = Http::withHeaders([
                'api-key' => $this->apiKey,
                'api-secret' => $this->apiSecret,
            ])->get($url);

            if ($response->successful()) {
                return $response->body(); // Binary PDF
            }
            
            Log::error("Boxy Label Error: " . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error("Boxy Label Exception: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Prepare payload for Boxy API
     *
     * @param Order $order
     * @return array
     */
    private function preparePayload(Order $order)
    {
        if (!$order->delivery_address_id && !$order->user_id) {
            return ['error' => 'No delivery address or customer found'];
        }

        // Fetch address or user fallback
        $address = null;
        if ($order->delivery_address_id) {
            $address = \App\Models\CustomerAddress::find($order->delivery_address_id);
        } elseif ($order->user_id) {
            $address = \App\Models\CustomerAddress::where('user_id', $order->user_id)->latest()->first();
        }

        $contactName = "Guest";
        if ($order->customer) {
            $contactName = $order->customer->f_name . ' ' . $order->customer->l_name;
        } elseif (isset($address)) {
            $contactName = $address->contact_person_name;
        }

        $contactPhone = "";
        $regionName = "الشعب"; 
        $addressText = "";
        $provinceCode = "BGD";

        if (isset($address)) {
            $contactPhone = $address->contact_person_number;
            $regionName = $address->district;
            $addressText = $address->address;
            if ($address->province_code) {
                $provinceCode = $address->province_code;
            } else {
                $provinceCode = $this->mapProvinceToCode($address->governate);
            }
        } elseif ($order->customer) {
            $contactPhone = $order->customer->phone;
        }

        if (empty($contactPhone)) return ['error' => 'Missing phone number'];
        if (empty($contactName)) return ['error' => 'Missing customer name'];
        if (empty($regionName)) return ['error' => 'Missing region/district'];

        // Format phone number to 964...
        $formattedPhone = preg_replace('/[^0-9]/', '', $contactPhone);
        if (empty($formattedPhone)) return ['error' => 'Invalid phone number format'];
        
        if (str_starts_with($formattedPhone, '0')) {
            $formattedPhone = '964' . substr($formattedPhone, 1);
        } elseif (!str_starts_with($formattedPhone, '964')) {
            $formattedPhone = '964' . $formattedPhone;
        }

        // Products
        $products = [];
        foreach ($order->details as $detail) {
            $productDetails = json_decode($detail->product_details, true);
            $products[] = [
                "title" => $productDetails['name'] ?? 'Product',
                "price" => (int) $detail->price,
                "quantity" => (int) $detail->quantity
            ];
        }

        if (empty($products)) return ['error' => 'No products in order'];

        return [
            "is_fragile" => false,
            "ready_to_pick_up" => in_array($order->order_status, ['scheduled', 'confirmed', 'processing']),
            "fee_customer_payable" => null,
            "shipment_fee_type" => "BY_MERCHANT",
            "payment_type" => "COLLECT_ON_DELIVERY",
            "pick_up_type" => "PICK_UP",
            "description" => $order->order_note ?? "طلب من النظام",
            "custom_id" => null,
            "products" => $products,
            "saved_pick_up_address_uid" => "01KPRAQRXY335JNKR6K7YZTHGZ", 
            "size" => "M",
            "contact" => [
                "full_name" => (string) $contactName,
                "address_text" => (!empty($addressText)) ? (string) $addressText : "بغداد - " . $regionName,
                "phone" => (string) $formattedPhone,
                "secondary_phone" => null,
                "email" => $order->customer->email ?? null,
                "region_name" => (string) $regionName,
                "province_code" => (string) $provinceCode
            ]
        ];
    }

    private function mapProvinceToCode($governate)
    {
        // Comprehensive Iraq Province Mapping for Boxy
        $map = [
            'بغداد' => 'BGD',
            'Baghdad' => 'BGD',
            'baghdad' => 'BGD',
            'البصرة' => 'BSR',
            'Basra' => 'BSR',
            'basra' => 'BSR',
            'أربيل' => 'EBL',
            'Erbil' => 'EBL',
            'erbil' => 'EBL',
            'النجف' => 'NJF',
            'Najaf' => 'NJF',
            'najaf' => 'NJF',
            'كربلاء' => 'KRL',
            'Karbala' => 'KRL',
            'karbala' => 'KRL',
            'نينوى' => 'NIN',
            'Nineveh' => 'NIN',
            'nineveh' => 'NIN',
            'السليمانية' => 'SLI',
            'Sulaymaniyah' => 'SLI',
            'sulaymaniyah' => 'SLI',
            'بابل' => 'BBL',
            'Babylon' => 'BBL',
            'babylon' => 'BBL',
            'الأنبار' => 'ANB',
            'Anbar' => 'ANB',
            'anbar' => 'ANB',
            'كركوك' => 'KIK',
            'Kirkuk' => 'KIK',
            'kirkuk' => 'KIK',
            'صلاح الدين' => 'SAL',
            'Saladin' => 'SAL',
            'saladin' => 'SAL',
            'ديالى' => 'DIL',
            'Diyala' => 'DIL',
            'diyala' => 'DIL',
            'ذي قار' => 'DQA',
            'Thi-Qar' => 'DQA',
            'thi-qar' => 'DQA',
            'ميسان' => 'MSN',
            'Maysan' => 'MSN',
            'maysan' => 'MSN',
            'واسط' => 'WAS',
            'Wasit' => 'WAS',
            'wasit' => 'WAS',
            'المثنى' => 'MUT',
            'Muthanna' => 'MUT',
            'muthanna' => 'MUT',
            'القادسية' => 'QAD',
            'Qadisiyah' => 'QAD',
            'qadisiyah' => 'QAD',
            'دهوك' => 'DHK',
            'Duhok' => 'DHK',
            'duhok' => 'DHK',
            'حلبجة' => 'HJB',
            'Halabja' => 'HJB',
            'halabja' => 'HJB',
        ];

        return $map[$governate] ?? $governate ?? 'BGD';
    }

    /**
     * Delete order on Boxy Delivery platform
     *
     * @param string $uid Boxy order UID
     * @return array
     */
    public function deleteOrder(string $uid)
    {
        try {
            $deleteUrl = "https://api-stg.tryboxy.dev/api/v1/merchants/orders/{$uid}";

            Log::info("========== DELETING BOXY ORDER =========");
            Log::info("UID: " . $uid);
            Log::info("==========================================");

            $response = Http::withHeaders([
                'api-key' => $this->apiKey,
                'api-secret' => $this->apiSecret,
            ])->delete($deleteUrl);

            Log::info("========== BOXY DELETE RESPONSE =========");
            Log::info($response->body());
            Log::info("=========================================");

            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()];
            } else {
                return ['success' => false, 'message' => $response->body()];
            }

        } catch (\Exception $e) {
            Log::error("Boxy Delete Error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Cancel order on Boxy Delivery platform
     *
     * @param string $uid Boxy order UID
     * @return array
     */
    public function cancelOrder(string $uid)
    {
        try {
            $cancelUrl = "https://api-stg.tryboxy.dev/api/v1/merchants/orders/{$uid}/cancel";

            Log::info("========== CANCELING BOXY ORDER =========");
            Log::info("UID: " . $uid);
            Log::info("==========================================");

            $response = Http::withHeaders([
                'api-key' => $this->apiKey,
                'api-secret' => $this->apiSecret,
            ])->post($cancelUrl);

            Log::info("========== BOXY CANCEL RESPONSE =========");
            Log::info($response->body());
            Log::info("=========================================");

            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()];
            } else {
                return ['success' => false, 'message' => $response->body()];
            }

        } catch (\Exception $e) {
            Log::error("Boxy Cancel Error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
