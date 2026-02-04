<div class="table-responsive border-primary-light pos-cart-table rounded">
    <table class="table table-align-middle mb-0">
        <thead class="bg-primary-light text-dark">
            <tr>
                <th class="border-bottom-0">{{translate('item')}}</th>
                <th class="border-bottom-0">{{translate('qty')}}</th>
                <th class="border-bottom-0">{{translate('price')}}</th>
                <th class="border-bottom-0 text-center">{{translate('delete')}}</th>
            </tr>
        </thead>
        <tbody>
            <?php
$subtotal = 0;
$discount = 0;
$discount_type = 'amount';
$discount_on_product = 0;
$total_tax = 0;
        ?>
            @if(session()->has('cart') && count(session()->get('cart')) > 0)
                        <?php
                $cart = session()->get('cart');
                if (isset($cart['discount'])) {
                    $discount = $cart['discount'];
                    $discount_type = $cart['discount_type'];
                }
                                                                                                                        ?>
                        @foreach(session()->get('cart') as $key => $cartItem)
                            @if(is_array($cartItem))
                                <?php
                                $product_subtotal = ($cartItem['price']) * $cartItem['quantity'];
                                $discount_on_product += ($cartItem['discount'] * $cartItem['quantity']);
                                $subtotal += $product_subtotal;

                                $product = \App\Models\Product::find($cartItem['id']);
                                $total_tax += Helpers::tax_calculate($product, $cartItem['price']) * $cartItem['quantity'];
                                                                                                                                                                                                        ?>
                                <tr>
                                    <td class="media gap-2 align-items-center">
                                        <div class="avatar-sm rounded border">
                                            <img class="img-fit rounded" src="{{$cartItem['image'][0]}}" alt="{{$cartItem['name']}} image">
                                        </div>
                                        <div class="media-body">
                                            <h5 class="mb-0">{{Str::limit($cartItem['name'], 10)}}</h5>
                                            <small>{{Str::limit($cartItem['variant'], 20)}}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="number" data-key="{{$key}}" class="form-control qty" value="{{$cartItem['quantity']}}"
                                            min="1"
                                            onfocus="storeOldValue(this)" onchange="updateQuantity(event)">
                                    </td>
                                    <td>
                                        <div class="fs-12">
                                            {{ Helpers::set_symbol($product_subtotal) }}
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <a href="javascript:removeFromCart({{$key}})" class="btn btn-sm btn-outline-danger"> <i
                                                class="tio-delete-outlined"></i></a>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
            @endif
        </tbody>
    </table>
</div>

<?php
$total = $subtotal;

$session_subtotal = $subtotal;
$session_total = $subtotal + $total_tax - $discount_on_product;
\Session::put('subtotal', $session_subtotal);
\Session::put('total', $session_total);

$discount_amount = ($discount_type == 'percent' && $discount > 0) ? (($total * $discount) / 100) : $discount;
$discount_amount += $discount_on_product;
$total -= $discount_amount;

$extra_discount = session()->get('cart')['extra_discount'] ?? 0;
$extra_discount_type = session()->get('cart')['extra_discount_type'] ?? 'amount';
if ($extra_discount_type == 'percent' && $extra_discount > 0) {
    $extra_discount = ($subtotal * $extra_discount) / 100;
}
if ($extra_discount) {
    $total -= $extra_discount;
}
?>

<?php
$delivery_fee = session()->get('cart')['delivery_fee'] ?? 0;
$is_free_delivery = session()->get('cart')['is_free_delivery'] ?? false;
if ($is_free_delivery) {
    $delivery_fee = 0;
}
?>

<div class="box p-3">
    <dl class="row">
        <dt class="col-6">{{translate('المجموع الفرعي')}} :</dt>
        <dd class="col-6 text-right">{{ Helpers::set_symbol($subtotal) }}</dd>

        <dt class="col-6">{{translate('خصم إضافي')}}:
        </dt>
        <dd class="col-6 text-right">
            <button class="btn btn-sm" type="button" data-toggle="modal" data-target="#add-discount"><i
                    class="tio-edit"></i>
            </button> - {{ Helpers::set_symbol($extra_discount) }}

        </dd>


        <dt class="col-6">{{translate('رسوم التوصيل')}} :

        </dt>
        <dd class="col-6 text-right">
            <button class="btn btn-sm" type="button" data-toggle="modal" data-target="#delivery-fee-modal">
                <i class="tio-edit"></i>
            </button>
            {{ $is_free_delivery ? translate('مجاني') : Helpers::set_symbol($delivery_fee) }}
        </dd>

        <dt class="col-6 font-weight-bold fs-16 border-top pt-2">{{translate('الإجمالي')}} :</dt>
        <dd class="col-6 text-right font-weight-bold fs-16 border-top pt-2">
            {{ Helpers::set_symbol(round($total + $total_tax + $delivery_fee, 2)) }}
        </dd>
    </dl>

    <form action="{{route('admin.pos.order')}}" id='order_place' method="post">
        @csrf
        {{-- Hidden inputs for delivery --}}
        <input type="hidden" name="delivery_fee" id="delivery_fee_input" value="{{ $delivery_fee }}">
        <input type="hidden" name="is_free_delivery" id="is_free_delivery_input"
            value="{{ $is_free_delivery ? '1' : '0' }}">

        <div class="my-4 p-3 border rounded bg-light">
            <h5 class="mb-3 text-primary"><i class="tio-layers-outlined"></i> {{translate('Dynamic Order Fields')}}</h5>
            <div class="row g-2">
                {{--
                <div class="col-sm-6">
                    <div class="form-group mb-2">
                        <label class="input-label small text-muted">{{translate('قناة البيع')}}</label>
                        <select name="sale_channel" class="form-control form-control-sm">
                            <option value="">{{translate('اختر القناة')}}</option>
                            @foreach($sale_channels as $channel)
                                <option value="{{$channel->name}}">{{$channel->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group mb-2">
                        <label class="input-label small text-muted">{{translate('مندوب المبيعات')}}</label>
                        <select name="sale_agent" class="form-control form-control-sm">
                            <option value="">{{translate('اختر المندوب')}}</option>
                            @foreach($sale_agents as $agent)
                                <option value="{{$agent->name}}">{{$agent->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                --}}

                @if(isset($dynamic_fields) && count($dynamic_fields) > 0)
                
                    @foreach($dynamic_fields as $field)
                        <div class="col-sm-6">
                            <div class="form-group mb-2">
                                <label class="input-label small text-muted">
                                    {{$field->field_name}}
                                    @if($field->is_required) <span class="text-danger">*</span> @endif
                                </label>
                                
                                @if($field->field_type == 'text')
                                    <input type="text" name="dynamic_fields[{{$field->id}}]" class="form-control form-control-sm" 
                                           value="{{$field->default_value}}" {{$field->is_required ? 'required' : ''}}>
                                
                                @elseif($field->field_type == 'number')
                                     <input type="number" name="dynamic_fields[{{$field->id}}]" class="form-control form-control-sm" 
                                           value="{{$field->default_value}}" {{$field->is_required ? 'required' : ''}}>

                                @elseif($field->field_type == 'date')
                                     <input type="date" name="dynamic_fields[{{$field->id}}]" class="form-control form-control-sm" 
                                           value="{{$field->default_value}}" {{$field->is_required ? 'required' : ''}}>

                                @elseif($field->field_type == 'textarea')
                                    <textarea name="dynamic_fields[{{$field->id}}]" class="form-control form-control-sm" rows="1"
                                              {{$field->is_required ? 'required' : ''}}>{{$field->default_value}}</textarea>

                                @elseif($field->field_type == 'select')
                                    <select name="dynamic_fields[{{$field->id}}]" class="form-control form-control-sm" {{$field->is_required ? 'required' : ''}}>
                                        <option value="">{{translate('Select')}}</option>
                                        @foreach($field->options_array as $option)
                                            <option value="{{$option}}" {{$field->default_value == $option ? 'selected' : ''}}>{{$option}}</option>
                                        @endforeach
                                    </select>

                                @elseif($field->field_type == 'checkbox')
                                    @if(count($field->options_array) > 0)
                                        <div class="d-flex gap-2 flex-wrap">
                                            @foreach($field->options_array as $key => $option)
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input" id="field-{{$field->id}}-{{$key}}" 
                                                           name="dynamic_fields[{{$field->id}}][]" value="{{$option}}">
                                                    <label class="custom-control-label" for="field-{{$field->id}}-{{$key}}">{{$option}}</label>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="field-{{$field->id}}" 
                                                   name="dynamic_fields[{{$field->id}}]" value="1" {{$field->default_value == '1' ? 'checked' : ''}}>
                                            <label class="custom-control-label" for="field-{{$field->id}}">{{$field->field_name}}</label>
                                        </div>
                                    @endif

                                @elseif($field->field_type == 'radio')
                                    <div class="d-flex gap-2 flex-wrap">
                                         @foreach($field->options_array as $key => $option)
                                            <div class="custom-control custom-radio">
                                                <input type="radio" id="field-{{$field->id}}-{{$key}}" name="dynamic_fields[{{$field->id}}]" 
                                                       class="custom-control-input" value="{{$option}}" 
                                                       {{$field->default_value == $option ? 'checked' : ''}} 
                                                       {{$field->is_required ? 'required' : ''}}>
                                                <label class="custom-control-label" for="field-{{$field->id}}-{{$key}}">{{$option}}</label>
                                            </div>
                                         @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @endif
                <div class="col-sm-6">
                    <div class="form-group mb-2">
                        <label class="input-label small text-muted">{{translate('تاريخ التوصيل')}}</label>
                        <input type="date" name="delivery_date" class="form-control form-control-sm">
                    </div>
                </div>
                {{--
                <div class="col-sm-6">
                    <div class="form-group mb-2">
                        <label class="input-label small text-muted">{{translate('اسم المستخدم')}}</label>
                        <input type="text" name="agent_username" class="form-control form-control-sm"
                            placeholder="{{translate('اسم المستخدم')}}">
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group mb-2">
                        <label class="input-label small text-muted">{{translate('رابط الفيديو')}}</label>
                        <input type="url" name="video_link" class="form-control form-control-sm"
                            placeholder="https://...">
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-check">
                        <input type="checkbox" name="is_organic" value="1" id="is_organic" class="form-check-input">
                        <label class="form-check-label small" for="is_organic">{{translate('أورجانيك (طبيعي)')}}</label>
                    </div>
                </div>
                --}}
            </div>
        </div>

        <div class="my-4">
            <div class="text-dark d-flex mb-2">{{ translate('طريقة الدفع') }}:</div>
            <ul class="list-unstyled option-buttons">
                <li>
                    <input type="radio" id="cash" value="cash" name="type" hidden="" checked>
                    <label for="cash" class="btn border px-4 mb-0">{{ translate('نقداً') }}</label>
                </li>
                <li>
                    <input type="radio" value="card" id="card" hidden="" name="type">
                    <label for="card" class="btn border px-4 mb-0">{{ translate('بطاقة') }}</label>
                </li>
            </ul>
        </div>

        <div class="collect-cash-section pb-80 pb-sm-3" style="display: block">
            <div class="form-group mb-2 d-flex align-items-center justify-content-between gap-2">
                <label class="w-50 mb-0">{{translate('المبلغ المدفوع')}} :</label>
                <input type="number" class="form-control w-50 text-right" name="show_paid_amount" step="0.01"
                    id="showPaidAmount" value="0" required="">
                <input type="hidden" class="hidden-paid-amount" name="paid_amount" id="paidAmount" value="0">
                <input type="hidden" class="hidden-paid-amount" id="totalAmount"
                    value="{{ round($total + $total_tax + $delivery_fee, 2) }}">
            </div>
            <div class="form-group d-flex align-items-center justify-content-between gap-2">
                <label class="due-or-change-amount w-50 mb-0">{{translate('المبلغ المتبقي للعميل')}} :</label>
                <input type="number" class="form-control text-right w-50 border-0 shadow-none" id="amount-difference"
                    value="0" step="0.01" readonly="" required="">
            </div>
        </div>

        <div class="pos-cart-bottom-btns bg-white shadow">
            <div class="row g-2">
                <div class="col-sm-6">
                    <button type="button" class="btn btn-outline-danger btn-block" onclick="emptyCart()"><i
                            class="tio-delete-outlined"></i> {{translate('تفريغ السلة')}} </button>
                </div>
                <div class="col-sm-6" id="placeOrder">
                    <button type="submit" class="btn  btn-primary btn-block"><i class="fa fa-shopping-bag"></i>
                        {{translate('إتمام الطلب')}} </button>
                </div>
                <div class="col-sm-6 d-none" id="disablePlaceOrder">
                    <button type="button" class="btn  btn-primary btn-block" disabled data-toggle="tooltip"
                        title="Paid amount must be equal or greater than total amount."><i
                            class="fa fa-shopping-bag"></i>
                        {{translate('إتمام الطلب')}} </button>
                </div>
            </div>
        </div>
    </form>


</div>

<div class="modal fade" id="add-discount" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{translate('update_discount')}}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{route('admin.pos.discount')}}" method="post" class="row">
                    @csrf
                    <div class="form-group col-sm-6">
                        <label for="">{{translate('discount')}}</label>
                        <input type="number" value="{{session()->get('cart')['extra_discount'] ?? 0}}"
                            class="form-control" name="discount">
                    </div>
                    <div class="form-group col-sm-6">
                        <label for="">{{translate('type')}}</label>
                        <select name="type" class="form-control">
                            <option value="amount" {{$extra_discount_type == 'amount' ? 'selected' : ''}}>
                                {{translate('amount')}}({{Helpers::currency_symbol()}})
                            </option>
                            <option value="percent" {{$extra_discount_type == 'percent' ? 'selected' : ''}}>
                                {{translate('percent')}}(%)
                            </option>
                        </select>
                    </div>
                    <div class="d-flex justify-content-end col-sm-12">
                        <button class="btn btn-primary" type="submit">{{translate('submit')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="add-coupon-discount" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{translate('Coupon_discount')}}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{route('admin.pos.discount')}}" method="post" class="row">
                    @csrf
                    <div class="form-group col-12">
                        <label for="">{{translate('Coupon_code')}}</label>
                        <input type="number" placeholder="{{translate('COUPON200')}}" class="form-control">
                    </div>
                    <div class="d-flex justify-content-end col-12">
                        <button class="btn btn-primary" type="submit">{{translate('submit')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="add-tax" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{translate('update_tax')}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{route('admin.pos.tax')}}" method="POST" class="row">
                    @csrf
                    <div class="form-group col-12">
                        <label for="">{{translate('tax')}} (%)</label>
                        <input type="number" class="form-control" name="tax" min="0">
                    </div>

                    <div class="form-group col-sm-12">
                        <button class="btn btn-sm btn-primary" type="submit">{{translate('submit')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>