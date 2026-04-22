<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderMetaOption;
use App\Models\Product;
use App\Models\User;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\HttpFoundation\StreamedResponse;
use function App\CentralLogics\translate;

class POSController extends Controller
{
    public function __construct(
        private Branch $branch,
        private Category $category,
        private Order $order,
        private OrderDetail $orderDetail,
        private Product $product,
        private User $user
    ) {
    }

    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    public function index(Request $request)
    {
        $category = $request->query('category_id', 0);
        $categories = $this->category->where(['position' => 0])->active()->get();
        $keyword = $request->keyword;
        $key = explode(' ', $keyword);


        $products = $this->product
            // Removed stock filter - show all products including 0 or negative stock
            ->when($request->has('category_id') && $request['category_id'] != 0, function ($query) use ($request) {
                $query->whereJsonContains('category_ids', [['id' => (string) $request['category_id']]]);
            })
            ->when($keyword, function ($query) use ($key) {
                return $query->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('name', 'like', "%{$value}%");
                    }
                });
            })
            ->when($request->has('featured') && $request->featured == 1, function ($query) {
                $query->where('is_featured', 1);
            })
            ->active()
            ->latest()
            ->paginate(Helpers::getPagination());

        $branches = $this->branch->all();
        $users = $this->user->all();

        $sale_channels = OrderMetaOption::where('type', 'sale_channel')->get();
        $sale_agents = OrderMetaOption::where('type', 'sale_agent')->get();
        $dynamic_fields = \App\Models\OrderDynamicField::active()->get();

        $provinces = \App\Models\DeliveryLocationCity::select('province')->distinct()->get();

        return view('admin-views.pos.index', compact('categories', 'products', 'category', 'keyword', 'branches', 'users', 'sale_channels', 'sale_agents', 'dynamic_fields', 'provinces'));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function quickView(Request $request): JsonResponse
    {
        $product = $this->product->findOrFail($request->product_id);

        return response()->json([
            'success' => 1,
            'view' => view('admin-views.pos._quick-view-data', compact('product'))->render(),
        ]);
    }

    /**
     * @param Request $request
     * @return float[]|int[]
     */
    public function variantPrice(Request $request): array
    {
        $product = $this->product->find($request->id);
        $str = '';
        $price = 0;
        $stock = 0;

        foreach (json_decode($product->choice_options) as $key => $choice) {
            if ($str != null) {
                $str .= '-' . str_replace(' ', '', $request[$choice->name]);
            } else {
                $str .= str_replace(' ', '', $request[$choice->name]);
            }
        }

        if ($str != null) {
            $count = count(json_decode($product->variations));
            for ($i = 0; $i < $count; $i++) {
                if (json_decode($product->variations)[$i]->type == $str) {
                    $price = json_decode($product->variations)[$i]->price - Helpers::discount_calculate($product, $product->price);
                    $stock = json_decode($product->variations)[$i]->stock;
                }
            }
        } else {
            $price = $product->price - Helpers::discount_calculate($product, $product->price);
            $stock = $product->total_stock;
        }

        return array('price' => ($price * $request->quantity), 'stock' => $stock);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getCustomers(Request $request): JsonResponse
    {
        $key = explode(' ', $request['q']);
        $data = DB::table('users')
            ->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('f_name', 'like', "%{$value}%")
                        ->orWhere('l_name', 'like', "%{$value}%")
                        ->orWhere('phone', 'like', "%{$value}%");
                }
            })
            ->whereNotNull(['f_name', 'l_name', 'phone'])
            ->limit(8)
            ->latest()
            ->get([DB::raw('id, CONCAT(f_name, " ", l_name, " (", phone ,")") as text')]);

        $data[] = (object) ['id' => false, 'text' => translate('walk_in_customer')];

        return response()->json($data);
    }

    public function getAreas(Request $request): JsonResponse
    {
        $province = $request->province;
        $areas = \App\Models\DeliveryLocationCity::where('province', $province)->select('area_name')->distinct()->get();
        return response()->json($areas);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function updateTax(Request $request): RedirectResponse
    {
        if ($request->tax < 0) {
            \Brian2694\Toastr\Facades\Toastr::error(translate('Tax_can_not_be_less_than_0_percent'));
            return back();
        } elseif ($request->tax > 100) {
            Toastr::error(translate('Tax_can_not_be_more_than_100_percent'));
            return back();
        }

        $cart = $request->session()->get('cart', collect([]));
        $cart['tax'] = $request->tax;
        $request->session()->put('cart', $cart);
        return back();
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function updateDiscount(Request $request): RedirectResponse
    {
        $subTotal = session()->get('subtotal');
        $total = session()->get('total');

        if ($request->type == 'percent' && $request->discount < 0) {
            Toastr::error(translate('Discount_can_not_be_less_than_0_percent'));
            return back();
        } elseif ($request->type == 'amount' && $request->discount < 0) {
            Toastr::error(translate('Extra_discount_can_not_be_less_than_0'));
            return back();
        } elseif ($request->type == 'percent' && $request->discount > 100) {
            Toastr::error(translate('Extra_discount_can_not_be_more_than_100_percent'));
            return back();
        } elseif ($request->type == 'amount' && $request->discount > $total) {
            Toastr::error(translate('Extra_discount_can_not_be_more_than_total_price'));
            return back();
        } elseif ($request->type == 'percent' && ($request->session()->get('cart')) == null) {
            Toastr::error(translate('Product_already_added_in_cart'));
            return back();
        } elseif ($request->type == 'percent' && $request->discount > 0) {
            $extraDiscount = ($subTotal * $request->discount) / 100;
            if ($extraDiscount >= $total) {
                Toastr::error(translate('Extra_discount_can_not_be_more_or_equal_than_total_price'));
                return back();
            }
        }

        $cart = $request->session()->get('cart', collect([]));
        $cart['extra_discount'] = $request->discount;
        $cart['extra_discount_type'] = $request->type;
        $request->session()->put('cart', $cart);

        Toastr::success(translate('Discount_applied'));
        return back();
    }

    /**
     * Update delivery fee in cart
     * @param Request $request
     * @return RedirectResponse|JsonResponse
     */
    public function updateDeliveryFee(Request $request)
    {
        $cart = $request->session()->get('cart', collect([]));

        // Check if free delivery is selected
        if ($request->has('is_free_delivery') && $request->is_free_delivery == 1) {
            $cart['is_free_delivery'] = true;
            $cart['delivery_fee'] = 0;
        } else {
            $cart['is_free_delivery'] = false;
            $cart['delivery_fee'] = $request->filled('fee_customer_payable') ? $request->fee_customer_payable : 0;
        }

        $request->session()->put('cart', $cart);

        if ($request->ajax()) {
            return response()->json(['message' => translate('تم تحديث رسوم التوصيل')], 200);
        }

        Toastr::success(translate('تم تحديث رسوم التوصيل'));
        return back();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function updateQuantity(Request $request): JsonResponse
    {
        $cart = $request->session()->get('cart', collect([]));
        $cart = $cart->map(function ($object, $key) use ($request) {
            if ($key == $request->key) {
                $object['quantity'] = $request->quantity;
            }
            return $object;
        });
        $request->session()->put('cart', $cart);
        return response()->json([], 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function updatePrice(Request $request): JsonResponse
    {
        $cart = $request->session()->get('cart', collect([]));
        $cart = $cart->map(function ($object, $key) use ($request) {
            if ($key == $request->key) {
                $object['price'] = $request->price;
            }
            return $object;
        });
        $request->session()->put('cart', $cart);
        return response()->json([], 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function addToCart(Request $request): JsonResponse
    {
        $product = $this->product->find($request->id);

        $data = array();
        $data['id'] = $product->id;
        $str = '';
        $variations = [];
        $price = 0;
        $stock = 0;

        //Gets all the choice values of customer choice option and generate a string like Black-S-Cotton
        foreach (json_decode($product->choice_options) as $key => $choice) {
            $data[$choice->name] = $request[$choice->name];
            $variations[$choice->title] = $request[$choice->name];
            if ($str != null) {
                $str .= '-' . str_replace(' ', '', $request[$choice->name]);
            } else {
                $str .= str_replace(' ', '', $request[$choice->name]);
            }
        }
        $data['variations'] = $variations;
        $data['variant'] = $str;
        if ($request->session()->has('cart')) {
            if (count($request->session()->get('cart')) > 0) {
                foreach ($request->session()->get('cart') as $key => $cartItem) {
                    if (is_array($cartItem) && $cartItem['id'] == $request['id'] && $cartItem['variant'] == $str) {
                        return response()->json([
                            'data' => 1
                        ]);
                    }
                }

            }
        }
        //Check the string and decreases quantity for the stock
        if ($str != null) {
            $count = count(json_decode($product->variations));
            for ($i = 0; $i < $count; $i++) {
                if (json_decode($product->variations)[$i]->type == $str) {
                    $price = json_decode($product->variations)[$i]->price;
                    $stock = json_decode($product->variations)[$i]->stock;
                }
            }
        } else {
            $price = $product->price;
            $stock = $product->total_stock;
        }

        $data['quantity'] = $request['quantity'];
        $data['price'] = $price;
        $data['name'] = $product->name;
        $data['discount'] = Helpers::discount_calculate($product, $price);
        $data['image'] = $product->image_fullpath;
        $data['total_stock'] = $stock;

        if ($request->session()->has('cart')) {
            $cart = $request->session()->get('cart', collect([]));
            $cart->push($data);
            $request->session()->put('cart', $cart);
        } else {
            $cart = collect([$data]);
            $request->session()->put('cart', $cart);
        }

        // Force save session immediately
        $request->session()->save();

        return response()->json([
            'data' => $data
        ]);
    }

    /**
     * @return Application|Factory|View
     */
    public function cartItems(): Factory|View|Application
    {
        // Get required data for cart view (same as index method)
        $sale_channels = OrderMetaOption::where('type', 'sale_channel')->get();
        $sale_agents = OrderMetaOption::where('type', 'sale_agent')->get();
        $dynamic_fields = \App\Models\OrderDynamicField::active()->get();

        return view('admin-views.pos._cart', compact('sale_channels', 'sale_agents', 'dynamic_fields'));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function emptyCart(Request $request): JsonResponse
    {
        session()->forget('cart');
        Session::forget('customer_id');
        Session::forget('branch_id');

        return response()->json([], 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function removeFromCart(Request $request): JsonResponse
    {
        if ($request->session()->has('cart')) {
            $cart = $request->session()->get('cart', collect([]));
            $cart->forget($request->key);
            $request->session()->put('cart', $cart);
            $request->session()->save(); // Force save session immediately
        }

        return response()->json([], 200);
    }

    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    public function orderList(Request $request): Factory|View|Application
    {
        $queryParam = [];
        $search = $request['search'];
        $branchId = $request['branch_id'];
        $startDate = $request['start_date'];
        $endDate = $request['end_date'];

        $this->order->where(['checked' => 0])->update(['checked' => 1]);

        $query = $this->order->whereIn('order_type', ['pos', 'delivery'])->with(['customer', 'branch'])
            ->when($request->filled('branch_id') && $branchId != 'all', function ($query) use ($branchId) {
                return $query->where('branch_id', $branchId);
            })
            ->when($request->filled('start_date') && $request->filled('end_date'), function ($query) use ($startDate, $endDate) {
                return $query->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate);
            });
        $queryParam = ['branch_id' => $branchId, 'start_date' => $startDate, 'end_date' => $endDate];

        if ($request->has('search')) {
            $key = explode(' ', $request['search']);
            $query = $query->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('id', 'like', "%{$value}%")
                        ->orWhere('order_status', 'like', "%{$value}%")
                        ->orWhere('transaction_reference', 'like', "%{$value}%");
                }
            });
            $queryParam = ['search' => $request['search']];
        }

        $orders = $query->orderBy('id', 'desc')->paginate(Helpers::getPagination())->appends($queryParam);

        return view('admin-views.pos.order.list', compact('orders', 'search', 'branchId', 'startDate', 'endDate'));
    }

    /**
     * @param $id
     * @return Application|Factory|View|RedirectResponse
     */
    public function orderDetails($id): View|Factory|RedirectResponse|Application
    {
        $order = $this->order->with('details')->where(['id' => $id])->first();
        if (isset($order)) {
            return view('admin-views.order.order-view', compact('order'));
        } else {
            Toastr::info(translate('No more orders!'));
            return back();
        }
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function placeOrder(Request $request)
    {
        Log::info('POS Place Order: Request received', [
            'customer_id' => session('customer_id'),
            'type' => $request->type,
            'cart_items_count' => session()->has('cart') ? count(session('cart')) : 0
        ]);

        // 1. Transaction Start
        DB::beginTransaction();
        try {
            // Check if the cart exists and is not empty
            if (!$request->session()->has('cart') || count($request->session()->get('cart')) < 1) {
                Toastr::error(translate('Please_select_a_customer_first'));
                DB::rollBack();
                return back();
            }

            $cart = $request->session()->get('cart');
            $delivery_charge = $request->filled('fee_customer_payable') ? $request->fee_customer_payable : ($cart['delivery_fee'] ?? 0);

            $totalTaxAmount = 0;
            $productPrice = 0;
            $totalProductMainPrice = 0;
            $order_details = [];

            // 2. Initial Order Creation (Relying on MySQL AUTO_INCREMENT)
            $order = $this->order->create([
                'user_id' => session()->has('customer_id') ? session('customer_id') : null,
                'coupon_discount_title' => $request->coupon_discount_title == 0 ? null : 'coupon_discount_title',
                'payment_status' => 'unpaid',
                'order_status' => 'new',
                'order_type' => 'pos', // Default to POS, might change to delivery below
                'paid_amount' => $request->paid_amount ?? 0,
                'coupon_code' => $request->coupon_code ?? null,
                'payment_method' => $request->type,
                'transaction_reference' => $request->transaction_reference ?? null,
                'delivery_charge' => $delivery_charge,
                'delivery_address_id' => $request->delivery_address_id ?? null,
                'checked' => 1,
                'branch_id' => session()->has('branch_id') ? session('branch_id') : 1,
                'delivery_date' => $request->delivery_date,
            ]);

            Log::info('POS Place Order: Order instance created', ['order_id' => $order->id]);

            // 3. Process Dynamic Fields & Adjust Order Type
            if ($request->has('dynamic_fields')) {
                foreach ($request->dynamic_fields as $field_id => $value) {
                    if ($value !== null) {
                        if (is_array($value)) {
                            $value = implode(', ', $value);
                        }
                        \App\Models\OrderDynamicFieldValue::create([
                            'order_id' => $order->id,
                            'field_id' => $field_id,
                            'field_value' => $value
                        ]);
                    }
                }
            }

            // Set order type based on the new delivery type toggle or fee
            if ($request->delivery_type == 'company' || $request->delivery_type == 'self' || $delivery_charge > 0) {
                $order->order_type = 'delivery';
            }

            // 4. Process Cart Items
            foreach ($cart as $key => $c) {
                if (is_array($c) && isset($c['id'])) {
                    $product = $this->product->find($c['id']);
                    if (!$product) continue;

                    $productSubtotal = ($c['price']) * $c['quantity'];
                    $discountOnProduct = ($c['discount'] * $c['quantity']);

                    $product_data = Helpers::product_data_formatting($product);
                    $order_details[] = [
                        'order_id' => $order->id,
                        'product_id' => $c['id'],
                        'product_details' => json_encode($product_data),
                        'quantity' => $c['quantity'],
                        'price' => $c['price'],
                        'tax_amount' => floor(Helpers::tax_calculate($product_data, $c['price'])),
                        'discount_on_product' => floor(Helpers::discount_calculate($product_data, $c['price'])),
                        'discount_type' => 'discount_on_product',
                        'variant' => json_encode($c['variant']),
                        'variation' => json_encode($c['variations']),
                        'unit' => $product_data['unit'] ?? 'pc',
                        'is_stock_decreased' => ($product['is_unlimited'] ?? 0) ? 0 : 1,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];

                    $totalTaxAmount += $order_details[count($order_details) - 1]['tax_amount'] * $c['quantity'];
                    $productPrice += $productSubtotal - $discountOnProduct;
                    $totalProductMainPrice += $productSubtotal;

                    // 5. Update Stock
                    if (!$product['is_unlimited']) {
                        $var_store = [];
                        $variations = gettype($product['variations']) != 'array' ? json_decode($product['variations'], true) : $product['variations'];
                        if (!empty($variations)) {
                            foreach ($variations as $var) {
                                if ($c['variant'] == $var['type']) {
                                    $var['stock'] -= $c['quantity'];
                                }
                                $var_store[] = $var;
                            }
                        }
                        $product->update([
                            'variations' => json_encode($var_store),
                            'total_stock' => $product['total_stock'] - $c['quantity'],
                        ]);
                    }
                }
            }

            // 6. Bulk Insert Order Details
            if (count($order_details) > 0) {
                try {
                    $this->orderDetail->insert($order_details);
                    Log::info('POS Place Order: Order details inserted', ['order_id' => $order->id, 'count' => count($order_details)]);
                } catch (\Exception $e) {
                    Log::error('POS Place Order: Order details insertion failed', ['order_id' => $order->id, 'error' => $e->getMessage()]);
                    throw new \Exception('Failed to insert order details: ' . $e->getMessage());
                }
            } else {
                throw new \Exception('No valid items in cart to create order');
            }

            // 7. Calculate Final Amounts
            $extra_discount = 0;
            if (isset($cart['extra_discount'])) {
                $extra_discount = $cart['extra_discount_type'] == 'percent' && $cart['extra_discount'] > 0
                    ? (($totalProductMainPrice * $cart['extra_discount']) / 100)
                    : $cart['extra_discount'];
            }

            $tax = $cart['tax'] ?? 0;
            $totalTaxAmount = ($tax > 0) ? (($productPrice * $tax) / 100) : $totalTaxAmount;

            $order->extra_discount = $extra_discount;
            $order->total_tax_amount = $totalTaxAmount;
            $order_amount = $productPrice + $totalTaxAmount + $order->delivery_charge - $extra_discount;
            $order->order_amount = $order_amount;

            // 8. Payment Status
            $paid_amount = $request->paid_amount ?? 0;
            if ($paid_amount >= $order_amount) {
                $order->payment_status = 'paid';
            } elseif ($paid_amount > 0) {
                $order->payment_status = 'partially_paid';
            } else {
                $order->payment_status = 'unpaid';
            }

            // 9. Boxy Delivery Integration
            $boxy_verified = false;
            // Removed check for business setting and user_id to allow automatic guest orders
            if ($order->order_type == 'delivery' && $request->delivery_type == 'company') {
                try {
                    $boxyService = new \App\Services\BoxyDeliveryService();
                    $boxyResponse = $boxyService->sendOrder($order);
                    if ($boxyResponse['success']) {
                        $responseData = $boxyResponse['data'];
                        $order->boxy_uid = $responseData['object']['order']['uid'] ?? null;
                        $order->boxy_platform_code = $responseData['object']['order']['platform_code'] ?? null;
                        $boxy_verified = true;
                    } else {
                        Toastr::warning(translate('Boxy Delivery Error: ') . $boxyResponse['message']);
                        Log::warning('Boxy API Warning: ' . ($boxyResponse['message'] ?? 'Unknown error'));
                    }
                } catch (\Exception $e) {
                    Log::error('Boxy Integration Error: ' . $e->getMessage());
                }
            }

            // 10. Final Save and Commit
            // Force save all changes to the order
            $order->timestamps = false; // Don't update timestamps again
            $order->save();

            // Double-check that order exists in database
            $verifyOrder = $this->order->find($order->id);
            if (!$verifyOrder) {
                throw new \Exception('Order failed to persist in database');
            }

            DB::commit();
            Log::info('POS Place Order: Completed successfully', [
                'order_id' => $order->id,
                'order_type' => $order->order_type,
                'boxy_verified' => $boxy_verified
            ]);

            // 11. Cleanup and Notif
            session()->forget(['cart', 'customer_id', 'branch_id']);
            session(['last_order' => $order->id]);

            if ($order->user_id) {
                try {
                    $user = User::find($order->user_id);
                    $emailServices = Helpers::get_business_settings('mail_config');
                    if (isset($emailServices['status']) && $emailServices['status'] == 1 && $user?->email) {
                        Mail::to($user->email)->send(new \App\Mail\OrderPlaced($order->id));
                    }
                } catch (\Exception $e) {
                    Log::error('Notification Error: ' . $e->getMessage());
                }
            }

            Toastr::success(translate('order_placed_successfully'));
            return back();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('POS Place Order: Critical failure', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            Toastr::error(translate('Order_placement_failed: ') . $e->getMessage());
            return back();
        }
    }


    /**
     * @param $id
     * @return JsonResponse
     */
    public function generateInvoice($id): JsonResponse
    {
        $order = $this->order->where('id', $id)->first();

        return response()->json([
            'success' => 1,
            'view' => view('admin-views.pos.order.invoice', compact('order'))->render(),
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function storeKeys(Request $request): JsonResponse
    {
        session()->put($request['key'], $request['value']);
        return response()->json('', 200);
    }

    public function customerStore(Request $request): RedirectResponse
    {
        $request->validate([
            'f_name' => 'required',
            'phone' => 'required|digits:11|regex:/^07\d{9}$/',
        ]);

        $userPhone = $this->user->where('phone', $request->phone)->first();
        if (isset($userPhone)) {
            Toastr::error(translate('The phone is already taken'));
            return back();
        }

        if ($request->email) {
            $userEmail = $this->user->where('email', $request->email)->first();
            if (isset($userEmail)) {
                Toastr::error(translate('The email is already taken'));
                return back();
            }
            $email = $request->email;
        } else {
            $email = $request->phone . '@example.com';
        }

        $user = $this->user->create([
            'f_name' => $request->f_name,
            'l_name' => $request->l_name ?? '',
            'email' => $email,
            'phone' => $request->phone,
            'secondary_phone' => $request->secondary_phone,
            'password' => bcrypt('password'),
        ]);

        // Fetch province code from delivery_location_city
        $provinceCode = null;
        if ($request->governate) {
            $location = DB::table('delivery_location_city')
                ->where('province', $request->governate)
                ->first();
            if ($location) {
                $provinceCode = $location->province_code;
            }
        }

        // Create Address
        $address_data = [
            'user_id' => $user->id,
            'contact_person_name' => $request->f_name . ' ' . $request->l_name,
            'contact_person_number' => $request->secondary_phone ?? $request->phone,
            'address_type' => 'Home',
            'address' => $request->address ? ', ' . $request->address : '',
            'governate' => $request->governate,
            'district' => $request->district,
            'province_code' => $provinceCode,
            'description' => $request->description,
            'longitude' => 0,
            'latitude' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        DB::table('customer_addresses')->insert($address_data);

        session()->put('customer_id', $user->id);

        Toastr::success(translate('customer added successfully'));
        return back();
    }

    /**
     * @param Request $request
     * @return string|StreamedResponse
     */
    public function exportOrders(Request $request): StreamedResponse|string
    {
        $search = $request['search'];
        $branchId = $request['branch_id'];
        $startDate = $request['start_date'];
        $endDate = $request['end_date'];

        $query = $this->order->whereIn('order_type', ['pos', 'delivery'])->with(['customer', 'branch'])
            ->when((!is_null($branchId) && $branchId != 'all'), function ($query) use ($branchId) {
                return $query->where('branch_id', $branchId);
            })
            ->when((!is_null($startDate) && !is_null($endDate)), function ($query) use ($startDate, $endDate) {
                return $query->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate);
            });

        if ($search) {
            $key = explode(' ', $search);
            $query->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('id', 'like', "%{$value}%")
                        ->orWhere('order_status', 'like', "%{$value}%")
                        ->orWhere('transaction_reference', 'like', "%{$value}%");
                }
            });
            $queryParam = ['search' => $request['search']];
        }

        $orders = $query->orderBy('id', 'desc')->get();
        $storage = [];
        foreach ($orders as $order) {
            $storage[] = [
                'Order Id' => $order['id'],
                'Order Date' => date('d M Y', strtotime($order['created_at'])),
                'Customer' => $order->customer ? $order->customer->f_name . ' ' . $order->customer->l_name : 'Walking Customer',
                'Branch' => $order->branch ? $order->branch->name : '',
                'Order Amount' => $order['order_amount'],
                'Order Status' => $order['order_status'],
                'Order Type' => $order['order_type'],
                'Payment Status' => $order['payment_status'],
                'Payment Method' => $order['payment_method'],
            ];
        }
        return (new FastExcel($storage))->download('pos-orders.xlsx');
    }
}
