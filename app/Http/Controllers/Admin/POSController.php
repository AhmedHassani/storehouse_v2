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
use Box\Spout\Common\Exception\InvalidArgumentException;
use Box\Spout\Common\Exception\IOException;
use Box\Spout\Common\Exception\UnsupportedTypeException;
use Box\Spout\Writer\Exception\WriterNotOpenedException;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\DB;
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

    public function getCustomers(Request $request): \Illuminate\Http\JsonResponse
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
            Toastr::error(translate('Tax_can_not_be_less_than_0_percent'));
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
            Toastr::error(translate('Extra_discount_can_not_be_less_than_0_percent'));
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
            Toastr::error(translate('cart_is_empty'));
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
     * @return RedirectResponse
     */
    public function updateDeliveryFee(Request $request): RedirectResponse
    {
        $cart = $request->session()->get('cart', collect([]));

        // Check if free delivery is selected
        if ($request->has('is_free_delivery') && $request->is_free_delivery == 1) {
            $cart['is_free_delivery'] = true;
            $cart['delivery_fee'] = 0;
        } else {
            $cart['is_free_delivery'] = false;
            $cart['delivery_fee'] = $request->delivery_fee ?? 0;
        }

        $request->session()->put('cart', $cart);

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

        $query = $this->order->pos()->with(['customer', 'branch'])
            ->when((!is_null($branchId) && $branchId != 'all'), function ($query) use ($branchId) {
                return $query->where('branch_id', $branchId);
            })
            ->when((!is_null($startDate) && !is_null($endDate)), function ($query) use ($startDate, $endDate) {
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
        // Check if the cart exists and is not empty
        if (!$request->session()->has('cart') || count($request->session()->get('cart')) < 1) {
            Toastr::error(translate('cart_empty_warning'));
            return back();
        }

        // Retrieve cart data from session
        $cart = $request->session()->get('cart');
        $totalTaxAmount = 0;
        $productPrice = 0;
        $order_details = [];

        // Get delivery fee from cart session
        $delivery_fee = $cart['delivery_fee'] ?? 0;
        $is_free_delivery = $cart['is_free_delivery'] ?? false;
        if ($is_free_delivery) {
            $delivery_fee = 0;
        }

        // Create a new order instance
        $order = $this->order->create([
            'user_id' => session()->has('customer_id') ? session('customer_id') : null,
            'coupon_discount_title' => $request->coupon_discount_title == 0 ? null : 'coupon_discount_title',
            'payment_status' => 'unpaid',
            'order_status' => 'confirmed',
            'order_type' => 'delivery',
            'paid_amount' => $request->paid_amount,
            'coupon_code' => $request->coupon_code ?? null,
            'payment_method' => $request->type,
            'transaction_reference' => $request->transaction_reference ?? null,
            'delivery_charge' => $delivery_fee, // Get from session
            'delivery_address_id' => $request->delivery_address_id ?? null,
            'order_note' => null,
            'checked' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'branch_id' => session()->has('branch_id') ? session('branch_id') : 1,
            'sale_channel' => null,
            'sale_agent' => null,
            'is_organic' => 0,
            'video_link' => null,
            'delivery_date' => $request->delivery_date,
            'agent_username' => null,
        ]);

        // Save dynamic field values
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

        // Process cart items
        $totalProductMainPrice = 0;
        foreach ($cart as $c) {
            if (is_array($c)) {
                $product = $this->product->find($c['id']);
                $p['variations'] = gettype($product['variations']) != 'array' ? json_decode($product['variations'], true) : $product['variations'];

                // Stock validation removed - allow negative stock in POS
                // Products can be sold even if stock is 0 or negative

                $discountOnProduct = 0;
                $productSubtotal = ($c['price']) * $c['quantity'];
                $discountOnProduct += ($c['discount'] * $c['quantity']);

                if ($product) {
                    $price = $c['price'];
                    $product = Helpers::product_data_formatting($product);
                    $order_details[] = [
                        'product_id' => $c['id'],
                        'product_details' => $product,
                        'quantity' => $c['quantity'],
                        'price' => $price,
                        'tax_amount' => floor(Helpers::tax_calculate($product, $price)),
                        'discount_on_product' => floor(Helpers::discount_calculate($product, $price)),
                        'discount_type' => 'discount_on_product',
                        'variant' => json_encode($c['variant']),
                        'variation' => json_encode($c['variations']),
                        'created_at' => now(),
                        'updated_at' => now()
                    ];

                    $totalTaxAmount += $order_details[count($order_details) - 1]['tax_amount'] * $c['quantity'];
                    $productPrice += $productSubtotal - $discountOnProduct;
                    $totalProductMainPrice += $productSubtotal;
                }

                // Update product stock ONLY if product is NOT unlimited
                if (!$product['is_unlimited']) {
                    $var_store = [];
                    if (!empty($product['variations'])) {
                        $type = $c['variant'];
                        foreach ($product['variations'] as $var) {
                            if ($type == $var['type']) {
                                $var['stock'] -= $c['quantity'];
                            }
                            $var_store[] = $var;
                        }
                    }

                    $this->product->where(['id' => $product['id']])->update([
                        'variations' => json_encode($var_store),
                        'total_stock' => $product['total_stock'] - $c['quantity'],
                    ]);
                }
            }
        }

        // Calculate total price including discounts, taxes, and extras
        $totalPrice = $productPrice;

        if (isset($cart['extra_discount'])) {
            $extra_discount = $cart['extra_discount_type'] == 'percent' && $cart['extra_discount'] > 0
                ? (($totalProductMainPrice * $cart['extra_discount']) / 100)
                : $cart['extra_discount'];
            $totalPrice -= $extra_discount;
        }

        $tax = isset($cart['tax']) ? $cart['tax'] : 0;
        $totalTaxAmount = ($tax > 0) ? (($totalPrice * $tax) / 100) : $totalTaxAmount;

        try {
            // Save order details
            $order->extra_discount = $extra_discount ?? 0;
            $order->total_tax_amount = $totalTaxAmount;
            $order_amount = $totalPrice + $totalTaxAmount + $order->delivery_charge;
            $order->order_amount = $order_amount;
            $order->coupon_discount_amount = 0.00;

            // Payment Status logic
            $paid_amount = $request->paid_amount ?? 0;
            if ($paid_amount >= $order_amount) {
                $order->payment_status = 'paid';
            } elseif ($paid_amount > 0) {
                $order->payment_status = 'partially_paid';
            } else {
                $order->payment_status = 'unpaid';
            }

            $order->save();

            foreach ($order_details as $key => $item) {
                $order_details[$key]['order_id'] = $order->id;
            }

            $this->orderDetail->insert($order_details);

            // Send notifications to the user
            if ($order->user_id) {
                $user = User::find($order->user_id);
                $userFcmToken = $user?->cm_firebase_token;
                $value = Helpers::order_status_update_message('confirmed');
                try {
                    if ($value && $userFcmToken) {
                        $data = [
                            'title' => 'Order',
                            'description' => $value,
                            'order_id' => $order->id,
                            'image' => '',
                            'type' => 'order',
                        ];
                        Helpers::send_push_notif_to_device($userFcmToken, $data);
                    }

                    $emailServices = Helpers::get_business_settings('mail_config');
                    if (isset($emailServices['status']) && $emailServices['status'] == 1 && isset($user)) {
                        Mail::to($user->email)->send(new \App\Mail\OrderPlaced($order->id));
                    }
                } catch (\Exception $e) {
                }
            }

            // Send to Boxy Delivery
            try {
                $boxyService = new \App\Services\BoxyDeliveryService();
                $boxyResponse = $boxyService->sendOrder($order);
                if ($boxyResponse['success']) {
                    // Save Boxy UID and Platform Code
                    $responseData = $boxyResponse['data'];
                    if (isset($responseData['object']['order']['uid'])) {
                        $order->boxy_uid = $responseData['object']['order']['uid'];
                    }
                    if (isset($responseData['object']['order']['platform_code'])) {
                        $order->boxy_platform_code = $responseData['object']['order']['platform_code'];
                    }
                    $order->save();
                } else {
                    \Illuminate\Support\Facades\Log::warning('Boxy API Warning: ' . ($boxyResponse['message'] ?? 'Unknown error'));
                    $errorMsg = 'Order placed but failed to send to Boxy Delivery';
                    if (isset($boxyResponse['message'])) {
                        // Try to parse JSON error message if exists
                        $decoded = json_decode($boxyResponse['message'], true);
                        if ($decoded && isset($decoded['message'])) {
                            $errorMsg .= ': ' . $decoded['message'];
                        } elseif (is_string($boxyResponse['message'])) {
                            $errorMsg .= ': ' . $boxyResponse['message'];
                        }
                    }
                    Toastr::warning(translate($errorMsg));
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Boxy Integration Error: ' . $e->getMessage());
            }

            // Clear the cart and customer session data
            session()->forget(['cart', 'customer_id', 'branch_id']);
            session(['last_order' => $order->id]);

            Toastr::success(translate('order_placed_successfully'));
            return back();
        } catch (\Exception $e) {
            Toastr::warning(translate('failed_to_place_order'));
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
     * @throws IOException
     * @throws InvalidArgumentException
     * @throws UnsupportedTypeException
     * @throws WriterNotOpenedException
     */
    public function exportOrders(Request $request): StreamedResponse|string
    {
        $queryParam = [];
        $search = $request['search'];
        $branchId = $request['branch_id'];
        $startDate = $request['start_date'];
        $endDate = $request['end_date'];

        $query = $this->order->pos()->with(['customer', 'branch'])
            ->when((!is_null($branchId) && $branchId != 'all'), function ($query) use ($branchId) {
                return $query->where('branch_id', $branchId);
            })
            ->when((!is_null($startDate) && !is_null($endDate)), function ($query) use ($startDate, $endDate) {
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
