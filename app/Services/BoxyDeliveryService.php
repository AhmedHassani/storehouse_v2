<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BoxyDeliveryService
{
    private $baseUrl = 'https://api-pre.tryboxy.dev/api/v1/merchants/orders/request';
    private $apiKey = '01KFRC00VNERS2FF7FRRY0P7A4';
    private $apiSecret = 'yIf!7SnNxukzLP6URr-Ya5e_xBeF1Dp3lGaFuvkm7=lL47GaRYCOgfZw73QcRj^=-!@o2%I*hz6T4h10OfH0X5MM*a=NCth6=U2lJe=SBGGadEff*lUE!#UJ_ikj$$fT$IX=t&rQJPcMe1VWn$UPStFq7rd=H^=*TYQ+mhEKj2SCW4Y3Gb+FWY1wreElX@B13F_k_ng1_1xf9FtAI^M-7_UMum0y7l1!6FTYb@7B5Q6Kn%CEGxE-NeGzyGlL$RN';

    /**
     * Send order to Boxy Delivery API
     *
     * @param Order $order
     * @return array
     */
    public function sendOrder(Order $order)
    {
        try {
            if (!$order->delivery_address_id && !$order->user_id) {
                return ['success' => false, 'message' => 'No delivery address or customer found'];
            }

            // Fetch address
            // Fetch address or user fallback
            $name = 'Guest';
            $phone = '0000000000';
            $address = null;

            if ($order->delivery_address_id) {
                $address = \App\Models\CustomerAddress::find($order->delivery_address_id);
            } elseif ($order->user_id) {
                $address = \App\Models\CustomerAddress::where('user_id', $order->user_id)->latest()->first();
            }

            if (isset($address)) {
                $name = $address->contact_person_name;
                $phone = $address->contact_person_number;
            } elseif ($order->customer) {
                $name = $order->customer->f_name . ' ' . $order->customer->l_name;
                $phone = $order->customer->phone;
            }

            // Products
            $products = [];
            foreach ($order->details as $detail) {
                $productDetails = json_decode($detail->product_details, true);
                $products[] = [
                    "title" => $productDetails['name'] ?? 'Product',
                    "price" => (float) $detail->price,
                    "quantity" => (int) $detail->quantity
                ];
            }
            $contactName = "John Doe"; // Default fallback
            if ($order->customer) {
                $contactName = $order->customer->f_name . ' ' . $order->customer->l_name;
            } elseif (isset($address)) {
                $contactName = $address->contact_person_name;
            }

            $contactPhone = "07835998521"; // Default fallback
            $regionName = "الشعب"; // Default fallback
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
            }

            // Validate required fields before sending
            if (empty($contactPhone) || $contactPhone === "07835998521") {
                Log::warning("Boxy Send Failed: Missing or invalid phone number");
                return ['success' => false, 'message' => 'Missing phone number'];
            }

            if (empty($contactName) || $contactName === "John Doe") {
                Log::warning("Boxy Send Failed: Missing customer name");
                return ['success' => false, 'message' => 'Missing customer name'];
            }

            if (empty($regionName) || $regionName === "الشعب") {
                Log::warning("Boxy Send Failed: Missing region/district");
                return ['success' => false, 'message' => 'Missing region/district'];
            }

            if (empty($products)) {
                Log::warning("Boxy Send Failed: No products in order");
                return ['success' => false, 'message' => 'No products in order'];
            }

            // Calculate total amount including delivery fee BEFORE creating payload
            $totalAmount = $order->order_amount; // This includes product prices
            if ($order->delivery_charge > 0) {
                $totalAmount += $order->delivery_charge;
            }

            // Prepare payload (Static as requested)
            $payload = [
                "is_fragile" => false,
                "ready_to_pick_up" => false,
                "fee_customer_payable" => (float) $totalAmount,  // Set the total amount
                "shipment_fee_type" => "BY_MERCHANT",
                "payment_type" => "COLLECT_ON_DELIVERY",
                "pick_up_type" => "PICK_UP",
                "description" => "",
                "custom_id" => null,
                "products" => $products,
                "size" => "M",
                "type" => "DEFAULT",
                "contact" => [
                    "full_name" => $contactName,
                    "address_text" => $addressText,
                    "phone" => $contactPhone,
                    "secondary_phone" => null,
                    "region_name" => $regionName,
                    "province_code" => $provinceCode
                ]
            ];

            Log::info("========== SENDING ORDER TO BOXY ==========");
            Log::info(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            Log::info("===========================================");

            $response = Http::withHeaders([
                'api-key' => $this->apiKey,
                'api-secret' => $this->apiSecret,
                'Content-Type' => 'application/json'
            ])->post($this->baseUrl, $payload);

            Log::info("========== BOXY RESPONSE ==========");
            Log::info($response->body());
            Log::info("===================================");

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

    private function mapProvinceToCode($governate)
    {
        // Simple mapping, can be extended
        $map = [
            'Baghdad' => 'BGD',
            'baghdad' => 'BGD',
            'بغداد' => 'BGD',
            'Basra' => 'BSR',
            'Erbil' => 'EBL',
            'Najaf' => 'NJF',
            // Default fallback if code is actually stored in governate
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
            $deleteUrl = "https://api-pre.tryboxy.dev/api/v1/merchants/orders/{$uid}";

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
}
