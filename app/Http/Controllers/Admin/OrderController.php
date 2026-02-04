<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\DeliveryMan;
use App\Traits\WalletTransaction;
use Box\Spout\Common\Exception\InvalidArgumentException;
use Box\Spout\Common\Exception\IOException;
use Box\Spout\Common\Exception\UnsupportedTypeException;
use Box\Spout\Writer\Exception\WriterNotOpenedException;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\HttpFoundation\StreamedResponse;
use function App\CentralLogics\translate;

class OrderController extends Controller
{
    use WalletTransaction;
    public function __construct(
        private Order $order,
        private OrderDetail $orderDetail,
        private Product $product,
        private Branch $branch,
        private DeliveryMan $deliveryMan
    ) {
    }

    /**
     * @param Request $request
     * @param $status
     * @return Application|Factory|View
     */
    public function list(Request $request, $status): Factory|View|Application
    {
        $queryParam = [];
        $search = $request['search'];
        $branchId = $request['branch_id'];
        $startDate = $request['start_date'];
        $endDate = $request['end_date'];
        $branches = $this->branch->all();

        $this->order->where(['checked' => 0])->update(['checked' => 1]);

        if ($status != 'all') {
            $query = $this->order->with(['customer', 'branch'])
                ->when((!is_null($branchId) && $branchId != 'all'), function ($query) use ($branchId) {
                    return $query->where('branch_id', $branchId);
                })->when((!is_null($startDate) && !is_null($endDate)), function ($query) use ($startDate, $endDate) {
                    return $query->whereDate('created_at', '>=', $startDate)
                        ->whereDate('created_at', '<=', $endDate);
                })->where(['order_status' => $status]);

        } else {
            $query = $this->order->with(['customer', 'branch'])
                ->when((!is_null($branchId) && $branchId != 'all'), function ($query) use ($branchId) {
                    return $query->where('branch_id', $branchId);
                })->when((!is_null($startDate) && !is_null($endDate)), function ($query) use ($startDate, $endDate) {
                    return $query->whereDate('created_at', '>=', $startDate)
                        ->whereDate('created_at', '<=', $endDate);
                });
        }

        $queryParam = ['branch_id' => $branchId, 'start_date' => $startDate, 'end_date' => $endDate];

        if ($request->has('search')) {
            $key = explode(' ', $request['search']);
            $query = $query->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('id', 'like', "%{$value}%")
                        ->orWhere('order_status', 'like', "%{$value}%")
                        ->orWhere('payment_status', 'like', "{$value}%");
                }
            });
            $queryParam = ['search' => $request['search'], 'branch_id' => $request['branch_id'], 'start_date' => $request['start_date'], 'end_date' => $request['end_date']];
        }

        $orders = $query->where('order_type', '!=', 'pos')->orderByDesc('id')->paginate(Helpers::pagination_limit())->appends($queryParam);
        return view('admin-views.order.list', compact('orders', 'branches', 'status', 'branchId', 'search', 'startDate', 'endDate'));
    }

    /**
     * @param $id
     * @return Application|Factory|View|RedirectResponse
     */
    public function details($id): View|Factory|RedirectResponse|Application
    {
        $order = $this->order->with('details')->where(['id' => $id])->first();
        $deliverymen = $this->deliveryMan
            ->where(['application_status' => 'approved'])
            ->where(function ($query) use ($order) {
                $query->where('branch_id', $order->branch_id)
                    ->orWhere('branch_id', 0);
            })
            ->get();

        if (isset($order)) {
            $dynamic_fields = \App\Models\OrderDynamicField::active()->get();
            return view('admin-views.order.order-view', compact('order', 'deliverymen', 'dynamic_fields'));
        } else {
            Toastr::info(translate('No more orders!'));
            return back();
        }
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function status(Request $request): RedirectResponse
    {
        $order = $this->order->find($request->id);

        if (in_array($order->order_status, ['returned', 'delivered', 'failed', 'canceled'])) {
            Toastr::warning(translate('you_can_not_change_the_status_of ' . $order->order_status . ' order'));
            return back();
        }

        if ($request->order_status == 'delivered' && $order['payment_status'] != 'paid') {
            Toastr::warning(translate('you_can_not_delivered_a_order_when_order_status_is_not_paid. please_update_payment_status_first'));
            return back();
        }

        if ($request->order_status == 'delivered' && $order['transaction_reference'] == null && !in_array($order['payment_method'], ['cash_on_delivery', 'wallet'])) {
            Toastr::warning(translate('add_your_payment_reference_first'));
            return back();
        }

        if (($request->order_status == 'out_for_delivery' || $request->order_status == 'delivered') && $order['delivery_man_id'] == null && $order['order_type'] != 'self_pickup') {
            Toastr::warning(translate('Please assign delivery man first!'));
            return back();
        }

        if ($request->order_status == 'returned' || $request->order_status == 'failed' || $request->order_status == 'canceled') {
            foreach ($order->details as $detail) {
                if ($detail['is_stock_decreased'] == 1) {
                    $product = $this->product->find($detail['product_id']);

                    if ($product != null) {
                        $varStore = [];
                        if (count(json_decode($detail['variation'])) > 0) {
                            $type = json_decode($detail['variation'])[0]->type;
                            foreach (json_decode($product['variations'], true) as $var) {
                                if ($type == $var['type']) {
                                    $var['stock'] += $detail['quantity'];
                                }
                                $varStore[] = $var;
                            }
                        }
                        $this->product->where(['id' => $product['id']])->update([
                            'variations' => json_encode($varStore),
                            'total_stock' => $product['total_stock'] + $detail['quantity'],
                        ]);
                        $this->orderDetail->where(['id' => $detail['id']])->update([
                            'is_stock_decreased' => 0
                        ]);
                    }
                }
            }
        } else {
            foreach ($order->details as $detail) {
                if ($detail['is_stock_decreased'] == 0) {
                    $product = $this->product->find($detail['product_id']);

                    if ($product != null) {
                        //check stock
                        foreach ($order->details as $c) {
                            $product = $this->product->find($c['product_id']);
                            $type = json_decode($c['variation'])[0]->type;
                            foreach (json_decode($product['variations'], true) as $var) {
                                if ($type == $var['type'] && $var['stock'] < $c['quantity']) {
                                    Toastr::error(translate('Stock is insufficient!'));
                                    return back();
                                }
                            }
                        }

                        $type = json_decode($detail['variation'])[0]->type;
                        $varStore = [];
                        foreach (json_decode($product['variations'], true) as $var) {
                            if ($type == $var['type']) {
                                $var['stock'] -= $detail['quantity'];
                            }
                            $varStore[] = $var;
                        }
                        $this->product->where(['id' => $product['id']])->update([
                            'variations' => json_encode($varStore),
                            'total_stock' => $product['total_stock'] - $detail['quantity'],
                        ]);
                        $this->orderDetail->where(['id' => $detail['id']])->update([
                            'is_stock_decreased' => 1
                        ]);
                    }
                }
            }
        }

        $order->order_status = $request->order_status;
        DB::beginTransaction();
        $order->save();
        if ($request->order_status == 'delivered') {
            if ($order->is_guest != 1) {
                $this->customerCreditWalletTransactionsForOrderComplete(customer: $order->customer, order: $order);
            }
        }
        DB::commit();

        $customerFcmToken = $order->is_guest == 0 ? ($order->customer ? $order->customer->cm_firebase_token : null) : ($order->guest ? $order->guest->fcm_token : null);
        $value = Helpers::order_status_update_message($request->order_status);
        try {
            if ($value) {
                $data = [
                    'title' => translate('Order'),
                    'description' => $value,
                    'order_id' => $order['id'],
                    'image' => '',
                    'type' => 'order',
                ];
                if ($customerFcmToken != null) {
                    Helpers::send_push_notif_to_device($customerFcmToken, $data);
                }
            }
        } catch (\Exception $e) {
            Toastr::warning(translate('Push notification failed for Customer!'));
        }

        //delivery man notification
        if (in_array($request->order_status, ['processing', 'out_for_delivery', 'returned', 'failed', 'canceled'])) {

            $deliverymanFcmToken = $order->delivery_man?->fcm_token ?? null;
            $value = translate('One of your order is ' . $request->order_status);

            try {
                if (!is_null($deliverymanFcmToken)) {
                    $data = [
                        'title' => translate('your order is ' . $request->order_status),
                        'description' => $value,
                        'order_id' => $order['id'],
                        'image' => '',
                        'type' => 'order',
                    ];
                    Helpers::send_push_notif_to_device($deliverymanFcmToken, $data);
                }
            } catch (\Exception $e) {
                Toastr::warning(translate('Push notification failed for DeliveryMan!'));
            }
        }

        Toastr::success(translate('Order status updated!'));
        return back();
    }

    /**
     * @param $order_id
     * @param $delivery_man_id
     * @return JsonResponse
     */
    public function addDeliveryman($order_id, $delivery_man_id): JsonResponse
    {
        if ($delivery_man_id == 0) {
            return response()->json([], 401);
        }
        $order = $this->order->find($order_id);

        if ($order->order_status == 'pending' || $order->order_status == 'confirmed' || $order->order_status == 'delivered' || $order->order_status == 'returned' || $order->order_status == 'failed' || $order->order_status == 'canceled') {
            return response()->json(['status' => false], 200);
        }

        $order->delivery_man_id = $delivery_man_id;
        $order->save();

        $deliverymanFcmToken = $order->delivery_man->fcm_token;
        $customerFcmToken = $order->is_guest == 0 ? ($order->customer ? $order->customer->cm_firebase_token : null) : ($order->guest ? $order->guest->fcm_token : null);

        $value = Helpers::order_status_update_message('del_assign');
        try {
            if ($value) {
                $data = [
                    'title' => translate('Order'),
                    'description' => $value,
                    'order_id' => $order['id'],
                    'image' => '',
                    'type' => 'order',
                ];
                Helpers::send_push_notif_to_device($deliverymanFcmToken, $data);
                $customerNotifyMessage = Helpers::order_status_update_message('customer_notify_message');
                if ($customerNotifyMessage) {
                    $data['description'] = $customerNotifyMessage;
                    if ($customerFcmToken != null) {
                        Helpers::send_push_notif_to_device($customerFcmToken, $data);
                    }
                }
            }
        } catch (\Exception $e) {
            Toastr::warning(translate('Push notification failed for DeliveryMan!'));
        }

        return response()->json(['status' => true], 200);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function paymentStatus(Request $request): RedirectResponse
    {
        $order = $this->order->find($request->id);
        if ($request->payment_status == 'paid' && $order['transaction_reference'] == null && $order['payment_method'] != 'cash_on_delivery' && $order['payment_method'] != 'wallet') {
            Toastr::warning('Add your payment reference code first!');
            return back();
        }
        $order->payment_status = $request->payment_status;
        $order->save();
        Toastr::success(translate('Payment status updated!'));
        return back();
    }

    /**
     * @param Request $request
     * @param $id
     * @return RedirectResponse
     */
    public function updateShipping(Request $request, $id): RedirectResponse
    {
        $request->validate([
            'contact_person_name' => 'required',
            'address_type' => 'required',
            'contact_person_number' => 'required',
            'address' => 'required'
        ]);

        $address = [
            'contact_person_name' => $request->contact_person_name,
            'contact_person_number' => $request->contact_person_number,
            'address_type' => $request->address_type,
            'address' => $request->address,
            'longitude' => $request->longitude,
            'latitude' => $request->latitude,
            'road' => $request->road,
            'house' => $request->house,
            'floor' => $request->floor,
            'created_at' => now(),
            'updated_at' => now()
        ];

        DB::table('customer_addresses')->where('id', $id)->update($address);
        Toastr::success(translate('Address updated!'));
        return back();
    }

    /**
     * @param $id
     * @return Application|Factory|View
     */
    public function generateInvoice($id): View|Factory|Application
    {
        $order = $this->order->where('id', $id)->first();
        return view('admin-views.order.invoice', compact('order'));
    }

    /**
     * @param Request $request
     * @param $id
     * @return RedirectResponse
     */
    public function addPaymentReferenceCode(Request $request, $id): RedirectResponse
    {
        $this->order->where(['id' => $id])->update([
            'transaction_reference' => $request['transaction_reference']
        ]);

        Toastr::success(translate('Payment reference code is added!'));
        return back();
    }

    /**
     * @param $id
     * @return RedirectResponse
     */
    public function branchFilter($id): RedirectResponse
    {
        session()->put('branch_filter', $id);
        return back();
    }

    /**
     * @param Request $request
     * @param $status
     * @return StreamedResponse|string
     * @throws IOException
     * @throws InvalidArgumentException
     * @throws UnsupportedTypeException
     * @throws WriterNotOpenedException
     */
    public function exportOrders(Request $request, $status): StreamedResponse|string
    {
        $queryParam = [];
        $search = $request['search'];
        $branchId = $request['branch_id'];
        $startDate = $request['start_date'];
        $endDate = $request['end_date'];

        if ($status != 'all') {
            $query = $this->order->with(['customer', 'branch'])
                ->when((!is_null($branchId) && $branchId != 'all'), function ($query) use ($branchId) {
                    return $query->where('branch_id', $branchId);
                })->when((!is_null($startDate) && !is_null($endDate)), function ($query) use ($startDate, $endDate) {
                    return $query->whereDate('created_at', '>=', $startDate)
                        ->whereDate('created_at', '<=', $endDate);
                })->where(['order_status' => $status]);
        } else {
            $query = $this->order->with(['customer', 'branch'])
                ->when((!is_null($branchId) && $branchId != 'all'), function ($query) use ($branchId) {
                    return $query->where('branch_id', $branchId);
                })->when((!is_null($startDate) && !is_null($endDate)), function ($query) use ($startDate, $endDate) {
                    return $query->whereDate('created_at', '>=', $startDate)
                        ->whereDate('created_at', '<=', $endDate);
                });
        }

        if ($request->has('search')) {
            $key = explode(' ', $request['search']);
            $query = $query->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('id', 'like', "%{$value}%")
                        ->orWhere('order_status', 'like', "%{$value}%")
                        ->orWhere('payment_status', 'like', "%{$value}%");
                }
            });
            $queryParam = ['search' => $request['search']];
        }

        $orders = $query->notPos()->orderBy('id', 'desc')->get();

        $storage = [];

        foreach ($orders as $order) {
            $branch = $order->branch ? $order->branch->name : '';
            if ($order->is_guest == 0) {
                $customer = $order->customer ? $order->customer->f_name . ' ' . $order->customer->l_name : 'Customer Deleted';
            } else {
                $customer = 'Guest Customer';
            }
            $deliveryman = $order->delivery_man ? $order->delivery_man->f_name . ' ' . $order->delivery_man->l_name : '';

            $storage[] = [
                'order_id' => $order['id'],
                'customer' => $customer,
                'order_amount' => $order['order_amount'],
                'coupon_discount_amount' => $order['coupon_discount_amount'],
                'payment_status' => $order['payment_status'],
                'order_status' => $order['order_status'],
                'total_tax_amount' => $order['total_tax_amount'],
                'payment_method' => $order['payment_method'],
                'transaction_reference' => $order['transaction_reference'],
                'delivery_man' => $deliveryman,
                'delivery_charge' => $order['delivery_charge'],
                'coupon_code' => $order['coupon_code'],
                'order_type' => $order['order_type'],
                'branch' => $branch,
                'extra_discount' => $order['extra_discount'],
            ];
        }
        return (new FastExcel($storage))->download('orders.xlsx');
    }

    public function updatePayment(Request $request, $id): RedirectResponse
    {
        $order = $this->order->find($id);
        $amount = $request->amount;

        if ($amount <= 0) {
            Toastr::error(translate('يجب أن يكون المبلغ أكبر من 0'));
            return back();
        }

        $order->paid_amount += $amount;

        $total_due = $order->order_amount;

        if ($order->paid_amount >= $total_due) {
            $order->payment_status = 'paid';
        } else {
            $order->payment_status = 'partially_paid';
        }

        $order->save();
        Toastr::success(translate('تم تحديث الدفع بنجاح!'));
        return back();
    }

    public function updateDynamicFields(Request $request, $id): RedirectResponse
    {
        $order = $this->order->find($id);

        // Handle standard Meta fields
        $metaFields = ['sale_channel', 'sale_agent', 'is_organic', 'video_link', 'delivery_date', 'agent_username'];
        foreach ($metaFields as $field) {
            if ($request->has($field)) {
                $order->$field = $request->$field;
            }
        }
        $order->save();

        // Handle Dynamic Values
        if ($request->has('dynamic_values')) {
            foreach ($request->dynamic_values as $fieldId => $value) {
                // Determine value based on field type (handle arrays for checkboxes)
                $fieldValue = is_array($value) ? implode(',', $value) : $value;

                \App\Models\OrderDynamicFieldValue::updateOrCreate(
                    ['order_id' => $order->id, 'field_id' => $fieldId],
                    ['field_value' => $fieldValue]
                );
            }
        }

        Toastr::success(translate('Order data updated successfully!'));
        return back();
    }
    public function bulkStatus(Request $request): RedirectResponse
    {
        $orderIds = $request->order_ids;
        $status = $request->order_status;
        $successCount = 0;
        $skippedCount = 0;
        $failReason = '';

        if (empty($orderIds)) {
            Toastr::warning(translate('No orders selected!'));
            return back();
        }

        foreach ($orderIds as $id) {
            $order = $this->order->find($id);

            // Validation checks (Skip invalid orders)
            if (in_array($order->order_status, ['returned', 'delivered', 'failed', 'canceled'])) {
                $skippedCount++;
                continue;
            }
            if ($status == 'delivered' && $order['payment_status'] != 'paid' && !in_array($order['payment_method'], ['cash_on_delivery', 'wallet'])) {
                $skippedCount++;
                $failReason = ' (Unpaid)';
                continue;
            }
            if (($status == 'out_for_delivery' || $status == 'delivered') && $order['delivery_man_id'] == null && $order['order_type'] != 'self_pickup') {
                $skippedCount++;
                $failReason = ' (No Delivery Man)';
                continue;
            }

            // Stock handling
            if ($status == 'returned' || $status == 'failed' || $status == 'canceled') {
                foreach ($order->details as $detail) {
                    if ($detail['is_stock_decreased'] == 1) {
                        $product = $this->product->find($detail['product_id']);
                        if ($product != null) {
                            $varStore = [];
                            if (count(json_decode($detail['variation'])) > 0) {
                                $type = json_decode($detail['variation'])[0]->type;
                                foreach (json_decode($product['variations'], true) as $var) {
                                    if ($type == $var['type']) {
                                        $var['stock'] += $detail['quantity'];
                                    }
                                    $varStore[] = $var;
                                }
                            }
                            $this->product->where(['id' => $product['id']])->update([
                                'variations' => json_encode($varStore),
                                'total_stock' => $product['total_stock'] + $detail['quantity'],
                            ]);
                            $this->orderDetail->where(['id' => $detail['id']])->update([
                                'is_stock_decreased' => 0
                            ]);
                        }
                    }
                }
            } else {
                foreach ($order->details as $detail) {
                    if ($detail['is_stock_decreased'] == 0) {
                        $product = $this->product->find($detail['product_id']);
                        if ($product != null) {
                            // Check stock
                            $insufficient = false;
                            foreach ($order->details as $c) {
                                $p = $this->product->find($c['product_id']);
                                $type = json_decode($c['variation'])[0]->type;
                                foreach (json_decode($p['variations'], true) as $var) {
                                    if ($type == $var['type'] && $var['stock'] < $c['quantity']) {
                                        $insufficient = true;
                                    }
                                }
                            }
                            if ($insufficient) {
                                $skippedCount++;
                                $failReason = ' (Stock Low)';
                                continue 2; // Skip this order if stock insufficient
                            }

                            $type = json_decode($detail['variation'])[0]->type;
                            $varStore = [];
                            foreach (json_decode($product['variations'], true) as $var) {
                                if ($type == $var['type']) {
                                    $var['stock'] -= $detail['quantity'];
                                }
                                $varStore[] = $var;
                            }
                            $this->product->where(['id' => $product['id']])->update([
                                'variations' => json_encode($varStore),
                                'total_stock' => $product['total_stock'] - $detail['quantity'],
                            ]);
                            $this->orderDetail->where(['id' => $detail['id']])->update([
                                'is_stock_decreased' => 1
                            ]);
                        }
                    }
                }
            }


            $order->order_status = $status;
            DB::beginTransaction();
            $order->save();
            if ($status == 'delivered') {
                if ($order->is_guest != 1) {
                    $this->customerCreditWalletTransactionsForOrderComplete(customer: $order->customer, order: $order);
                }
            }
            DB::commit();

            // Notifications
            $customerFcmToken = $order->is_guest == 0 ? ($order->customer ? $order->customer->cm_firebase_token : null) : ($order->guest ? $order->guest->fcm_token : null);
            $value = Helpers::order_status_update_message($status);
            try {
                if ($value && $customerFcmToken) {
                    $data = [
                        'title' => translate('Order'),
                        'description' => $value,
                        'order_id' => $order['id'],
                        'image' => '',
                        'type' => 'order',
                    ];
                    Helpers::send_push_notif_to_device($customerFcmToken, $data);
                }
            } catch (\Exception $e) {
            }

            $successCount++;
        }

        if ($successCount > 0) {
            $msg = translate($successCount . ' Orders updated!');
            if ($skippedCount > 0) {
                $msg .= ' ' . translate($skippedCount . ' Skipped' . $failReason);
            }
            Toastr::success($msg);
        } else {
            Toastr::warning(translate('No orders updated. Skipped: ' . $skippedCount . $failReason));
        }
        return back();
    }

    /**
     * Cancel order on Boxy Delivery platform
     *
     * @param $id
     * @return RedirectResponse
     */
    public function cancelBoxyOrder($id): RedirectResponse
    {
        $order = $this->order->find($id);

        if (!$order) {
            Toastr::error(translate('Order not found'));
            return back();
        }

        if (!$order->boxy_uid) {
            Toastr::error(translate('This order was not sent to Boxy Delivery'));
            return back();
        }

        try {
            $boxyService = new \App\Services\BoxyDeliveryService();
            $response = $boxyService->deleteOrder($order->boxy_uid);

            if ($response['success']) {
                // Update order status to canceled (deleted)
                $order->order_status = 'canceled';
                $order->save();

                Toastr::success(translate('Order deleted on Boxy Delivery successfully'));
            } else {
                Toastr::error(translate('Failed to delete order on Boxy: ') . ($response['message'] ?? 'Unknown error'));
            }
        } catch (\Exception $e) {
            Toastr::error(translate('Error deleting Boxy order: ') . $e->getMessage());
        }

        return back();
    }
}
