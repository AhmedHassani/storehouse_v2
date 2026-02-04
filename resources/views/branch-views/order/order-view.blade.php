@extends('layouts.branch.app')

@section('title', translate('Order Details'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-3">
            <h2 class="text-capitalize mb-0 d-flex align-items-center gap-2">
                <img src="{{asset('public/assets/admin/img/icons/all_orders.png')}}" alt="{{ translate('order') }}">
                {{translate('order_details')}}
                <span class="badge badge-soft-dark rounded-50 fz-14">{{$order->details->count()}}</span>
            </h2>
        </div>

        @php($googleMapStatus = \App\CentralLogics\Helpers::get_business_settings('google_map_status'))

        <div class="row">
            <div class="col-lg-{{$order->user_id == null ? 12 : 8}} mb-3 mb-lg-0">
                <div class="card mb-3 mb-lg-5">
                    <div class="card-body">
                        <div class="mb-3 text-dark d-print-none">
                            <div class="row gy-3">
                                <div class="col-sm-6">
                                    <div class="d-flex flex-column justify-content-between h-100">
                                        <div class="d-flex flex-column gap-2">
                                            <h2 class="page-header-title">{{translate('order')}} #{{$order['id']}}</h2>
                                            <div>
                                                <i class="tio-date-range"></i> {{date('d M Y h:i a',strtotime($order['created_at']))}}
                                            </div>
                                        </div>

                                        @if($order['order_type'] != 'pos' && $order['order_note'])
                                            <div><strong>{{translate('order_Note')}}:</strong> {{$order['order_note']}}
                                            </div>
                                        @endif
                                        @if($order['bring_change_amount'] >0)
                                            <div
                                                class="badge badge-soft-info p-2 d-flex align-items-center gap-1 text-wrap text-left lh-1.3 font-size-sm">
                                                <i class="tio-info"></i>
                                                <span class="text-dark opacity-lg">
                                                    {{translate('Please_bring').' '. \App\CentralLogics\Helpers::set_symbol($order['bring_change_amount']) . ' '.  translate('in_change_for_the_customer_when_making_the_delivery')}}
                                                </span>
                                            </div>
                                        @endif

                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="d-flex flex-column gap-2 align-items-sm-end">
                                        <div class="d-flex gap-2">

                                            @if($googleMapStatus ==1)
                                                <div class="hs-unfold">
                                                    @if($order['order_status']=='out_for_delivery')
                                                        @php($origin=\App\Models\DeliveryHistory::where(['deliveryman_id'=>$order['delivery_man_id'],'order_id'=>$order['id']])->first())
                                                        @php($current=\App\Models\DeliveryHistory::where(['deliveryman_id'=>$order['delivery_man_id'],'order_id'=>$order['id']])->latest()->first())
                                                        @if(isset($origin))
                                                            <a class="btn btn-outline-primary" target="_blank"
                                                               title="Delivery Boy Last Location" data-toggle="tooltip"
                                                               data-placement="top"
                                                               href="https://www.google.com/maps/dir/?api=1&origin={{$origin['latitude']}},{{$origin['longitude']}}&destination={{$current['latitude']}},{{$current['longitude']}}">
                                                                <i class="tio-map"></i>
                                                            </a>
                                                        @else
                                                            <a class="btn btn-outline-primary" href="javascript:"
                                                               data-toggle="tooltip"
                                                               data-placement="top" title="Waiting for location...">
                                                                <i class="tio-map"></i>
                                                            </a>
                                                        @endif
                                                    @else
                                                        <a class="btn btn-outline-dark last_location_view"
                                                           href="javascript:"
                                                           data-toggle="tooltip" data-placement="top"
                                                           title="{{translate('Only available when order is out for delivery!')}}">
                                                            <i class="tio-map"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            @endif

                                            @if($order->order_type == 'pos')
                                                <button class="btn btn-primary invoice-printing"
                                                        target="_blank" type="button"
                                                        data-id="{{$order->id}}">
                                                    <i class="tio-print"></i> {{translate('print_invoice')}}
                                                </button>
                                            @else
                                                <a class="btn btn-primary" target="_blank"
                                                   href={{route('branch.orders.generate-invoice',[$order['id']])}}>
                                                    <i class="tio-print"></i> {{translate('print_invoice')}}
                                                </a>
                                            @endif
                                        </div>

                                        <div class="d-flex justify-content-sm-end gap-2">
                                            <div>{{translate('حالة الطلب')}}:</div>
                                            @if($order['order_status']=='pending')
                                                <span class="text-info text-capitalize">{{translate('قيد الانتظار')}}</span>
                                            @elseif($order['order_status']=='confirmed')
                                                <span
                                                    class="text-info text-capitalize">{{translate('مؤكد')}}</span>
                                            @elseif($order['order_status']=='processing')
                                                <span
                                                    class="text-warning text-capitalize">{{translate('قيد التنفيذ')}}</span>
                                            @elseif($order['order_status']=='out_for_delivery')
                                                <span
                                                    class="text-warning text-capitalize">{{translate('خارج للتوصيل')}}</span>
                                            @elseif($order['order_status']=='delivered')
                                                <span
                                                    class="text-success text-capitalize">{{translate('تم التوصيل')}}</span>
                                            @elseif($order['order_status']=='returned')
                                                <span
                                                    class="text-danger text-capitalize">{{translate('مرتجع')}}</span>
                                            @elseif($order['order_status']=='failed')
                                                <span
                                                    class="text-danger text-capitalize">{{translate('فشل')}}</span>
                                            @else
                                                <span
                                                    class="text-danger text-capitalize">{{translate('ملغي')}}</span>
                                            @endif
                                        </div>

                                        <div class="d-flex justify-content-sm-end gap-2">
                                            <div>{{translate('طريقة الدفع')}}:</div>
                                            <div>{{translate(str_replace('_', ' ', $order['payment_method']))}}</div>
                                        </div>

                                        @if($order['payment_method'] != 'cash_on_delivery' && $order['payment_method'] != 'wallet')
                                            <div class="d-flex justify-content-sm-end align-items-center gap-2">
                                                @if($order['transaction_reference']==null && $order['order_type']!='pos')
                                                    <div>{{translate('رمز المرجع')}}:</div>
                                                    <button class="btn btn-outline-primary btn-sm py-1"
                                                            data-toggle="modal"
                                                            data-target=".bd-example-modal-sm">
                                                        {{translate('إضافة')}}
                                                    </button>
                                                @elseif($order['order_type']!='pos')
                                                    <div>{{translate('رمز المرجع')}}:</div>
                                                    <div>{{$order['transaction_reference']}}</div>
                                                @endif
                                            </div>
                                        @endif

                                        <div class="d-flex justify-content-sm-end gap-2">
                                            <div>{{translate('حالة الدفع')}}:</div>
                                            @if($order['payment_status']=='paid')
                                                <span class="text-success">{{translate('مدفوع')}}</span>
                                            @elseif($order['payment_status']=='partially_paid')
                                                <span class="text-warning">{{translate('مدفوع جزئياً')}}</span>
                                            @else
                                                <span class="text-danger">{{translate('غير مدفوع')}}</span>
                                            @endif
                                        </div>

                                        <div class="d-flex justify-content-sm-end gap-2">
                                            <div>{{translate('نوع الطلب')}}:</div>
                                            <label
                                                class="text-primary">{{translate(str_replace('_',' ',$order['order_type']))}}</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @php($item_amount=0)
                        @php($sub_total=0)
                        @php($total_tax=0)
                        @php($total_dis_on_pro=0)
                        @php($total_item_discount=0)

                        <div class="table-responsive">
                            <table class="table table-bordered table-align-middle text-dark">
                                <thead class="thead-light">
                                <tr>
                                    <th>{{ translate('SL') }}</th>
                                    <th>{{ translate('Item Description') }}</th>
                                    <th>{{ translate('Unit Price') }}</th>
                                    <th>{{ translate('Discount') }}</th>
                                    <th>{{ translate('Qty') }}</th>
                                    <th class="text-right">{{ translate('Total') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($order->details as $detail)
                                    @if($detail->product_details != null)
                                        @php($product = json_decode($detail->product_details, true))
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                <div class="media gap-3 max-content">
                                                    <div class="avatar-xl">
                                                        @if($detail->product && $detail->product['image'] != null )
                                                            <img class="img-fit"
                                                                 src="{{$detail->product['image_fullpath'][0]}}"
                                                                 alt="{{ translate('image') }}">
                                                        @else
                                                            <img
                                                                src="{{asset('public/assets/admin/img/160x160/img2.jpg')}}"
                                                                class="img-fit img-fluid rounded aspect-ratio-1"
                                                                alt="{{ translate('image') }}">
                                                        @endif
                                                    </div>
                                                    <div class="media-body">
                                                        <h6 class="mb-1 w-24ch">{{$product['name']}}</h6>
                                                        @if(count(json_decode($detail['variation'],true))>0)
                                                            @foreach(json_decode($detail['variation'],true)[0] ?? json_decode($detail['variation'],true) as $key1 =>$variation)
                                                                <div class="font-size-sm text-body text-capitalize">
                                                                    @if($variation != null)
                                                                        <span>{{$key1}} :  </span>
                                                                    @endif
                                                                    <span class="font-weight-bold">{{$variation}}</span>
                                                                </div>
                                                            @endforeach
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                {{ Helpers::set_symbol($detail['price']) }}
                                            </td>
                                            <td>{{Helpers::set_symbol($detail['discount_on_product'])}}</td>
                                            <td>{{$detail['quantity']}}</td>
                                            <td class="text-right">
                                                @php($amount=($detail['price']-$detail['discount_on_product'])*$detail['quantity'])
                                                {{ Helpers::set_symbol($amount) }}
                                            </td>
                                        </tr>
                                        @php($item_amount+=$detail['price']*$detail['quantity'])
                                        @php($sub_total+=$amount)
                                        @php($total_tax+=$detail['tax_amount']*$detail['quantity'])
                                        @php($total_item_discount += $detail['discount_on_product'] * $detail['quantity'])
                                    @endif
                                @endforeach
                                </tbody>
                            </table>
                        </div>


                        {{-- Dynamic Fields Display --}}
                        <div class="card mb-3 border">
                            <div class="card-header pb-0 border-0">
                                <h5 class="card-title">{{translate('Dynamic Order Fields')}}</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    {{-- Static Meta Fields --}}
                                    @if($order->sale_channel)
                                        <div class="col-md-4 mb-3">
                                            <h6 class="text-muted small mb-1">{{translate('قناة البيع')}}</h6>
                                            <p class="font-weight-bold text-dark">{{$order->sale_channel}}</p>
                                        </div>
                                    @endif
                                    
                                    @if($order->sale_agent)
                                        <div class="col-md-4 mb-3">
                                            <h6 class="text-muted small mb-1">{{translate('مندوب المبيعات')}}</h6>
                                            <p class="font-weight-bold text-dark">{{$order->sale_agent}}</p>
                                        </div>
                                    @endif

                                    @if($order->is_organic)
                                        <div class="col-md-4 mb-3">
                                            <h6 class="text-muted small mb-1">{{translate('اورجانيك')}}</h6>
                                            <span class="badge badge-soft-success">{{translate('نعم')}}</span>
                                        </div>
                                    @endif

                                    @if($order->video_link)
                                        <div class="col-md-4 mb-3">
                                            <h6 class="text-muted small mb-1">{{translate('رابط الفيديو')}}</h6>
                                            <a href="{{$order->video_link}}" target="_blank" class="text-primary">{{translate('عرض الفيديو')}}</a>
                                        </div>
                                    @endif

                                    @if($order->delivery_date)
                                      <div class="col-md-4 mb-3">
                                          <h6 class="text-muted small mb-1">{{translate('تاريخ التوصيل')}}</h6>
                                          <p class="font-weight-bold text-dark">{{$order->delivery_date->format('Y-m-d')}}</p>
                                      </div>
                                    @endif

                                    @if($order->agent_username)
                                        <div class="col-md-4 mb-3">
                                            <h6 class="text-muted small mb-1">{{translate('يوزر نيم المندوب')}}</h6>
                                            <p class="font-weight-bold text-dark">{{$order->agent_username}}</p>
                                        </div>
                                    @endif

                                    @if($order->dynamicValues && $order->dynamicValues->count() > 0)
                                        @foreach($order->dynamicValues as $value)
                                            <div class="col-md-4 mb-3">
                                                <h6 class="text-muted small mb-1">{{$value->field->field_name}}</h6>
                                                @if(str_contains($value->field_value, ','))
                                                    @foreach(explode(',', $value->field_value) as $v)
                                                        <span class="badge badge-soft-dark">{{ trim($v) }}</span>
                                                    @endforeach
                                                @else
                                                    <p class="font-weight-bold text-dark">{{$value->field_value}}</p>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="row justify-content-md-end mb-3">
                            <div class="col-md-9 col-lg-8">
                                <dl class="row">
                                    <dt class="col-6">{{translate('سعر المنتجات')}}:</dt>
                                    <dd class="col-6 text-end">{{ Helpers::set_symbol($item_amount) }}</dd>

                                    <dt class="col-6">{{translate('خصم المنتجات')}}:</dt>
                                    <dd class="col-6 text-end">{{ Helpers::set_symbol($total_item_discount) }}</dd>

                                    <dt class="col-6">{{translate('الضريبة')}}:</dt>
                                    <dd class="col-6 text-end">{{ Helpers::set_symbol($total_tax) }}</dd>

                                    <dt class="col-6">{{translate('المجموع الفرعي')}}:</dt>
                                    <dd class="col-6 text-end">{{ Helpers::set_symbol($sub_total+$total_tax) }}</dd>

                                    <dt class="col-6">{{translate('خصم الكوبون')}}:</dt>
                                    <dd class="col-6 text-end">
                                        - {{ Helpers::set_symbol($order['coupon_discount_amount']) }}</dd>

                                    @if($order['order_type'] == 'pos')
                                        <dt class="col-6">{{translate('خصم إضافي')}}:</dt>
                                        <dd class="col-6 text-end">
                                            - {{ Helpers::set_symbol($order['extra_discount']) }}</dd>
                                    @endif

                                    <dt class="col-6">{{translate('رسوم التوصيل')}}:</dt>
                                    <dd class="col-6 text-end">
                                        @if($order['order_type']=='self_pickup')
                                            @php($del_c=0)
                                        @else
                                            @php($del_c=$order['delivery_charge'])
                                        @endif
                                        {{ Helpers::set_symbol($del_c) }}
                                    </dd>

                                    <dt class="col-6 border-top pt-2 font-weight-bold">{{translate('الإجمالي')}}:</dt>
                                    <dd class="col-6 text-end border-top pt-2 font-weight-bold">{{ Helpers::set_symbol($sub_total+$del_c+$total_tax-$order['coupon_discount_amount']-$order['extra_discount']) }}</dd>
                                    @php($total_due = ($sub_total+$del_c+$total_tax-$order['coupon_discount_amount']-$order['extra_discount']))
                                    @if($order['paid_amount'] > 0 || $order['order_type'] == 'pos')
                                        <dt class="col-6">{{translate('المبلغ المدفوع')}}:</dt>
                                        <dd class="col-6 text-end"> {{ Helpers::set_symbol($order['paid_amount']) }}</dd>
                                        @if($order['paid_amount'] >= $total_due)
                                            <dt class="col-6">{{translate('المبلغ المتبقي للعميل')}}:</dt>
                                            <dd class="col-6 text-end"> {{ Helpers::set_symbol($order['paid_amount'] - $total_due) }}</dd>
                                        @else
                                            <dt class="col-6">{{translate('المبلغ المتبقي')}}:</dt>
                                            <dd class="col-6 text-end">
                                                <div class="d-flex justify-content-end align-items-center gap-2">
                                                    {{ Helpers::set_symbol($total_due - $order['paid_amount']) }}
                                                    <button class="btn btn-outline-primary btn-sm py-1" data-toggle="modal" data-target="#pay-remaining-modal">
                                                        {{translate('دفع المتبقي')}}
                                                    </button>
                                                </div>
                                            </dd>
                                        @endif
                                    @endif
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if($order->user_id != null)
                <div class="col-lg-4">
                    @if($order['order_type'] != 'pos')
                        <div class="card mb-3">
                            <div class="card-body text-capitalize d-flex flex-column">
                                <h4 class="mb-0 text-center">{{ $order['order_type'] != 'pos' ? translate('Order & Shipping Info ') : translate('Order Info') }}</h4>

                                <div class="mt-2">
                                    @if($order['order_type'] != 'pos')
                                        <h6>{{translate('Order Status')}}</h6>
                                        <select name="order_status"
                                                onchange="route_alert('{{route('branch.orders.status',['id'=>$order['id']])}}'+'&order_status='+ this.value,'{{translate("Change the order status to ") }}'+  this.value.replace(/_/g, ' '))"
                                                class="form-control">
                                            <option
                                                value="pending" {{$order['order_status'] == 'pending'? 'selected' : ''}}>{{translate('pending')}}</option>
                                            <option
                                                value="confirmed" {{$order['order_status'] == 'confirmed'? 'selected' : ''}}> {{translate('confirmed')}}</option>
                                            <option
                                                value="processing" {{$order['order_status'] == 'processing'? 'selected' : ''}}> {{translate('processing')}}</option>
                                            <option
                                                value="out_for_delivery" {{$order['order_status'] == 'out_for_delivery'? 'selected' : ''}}>{{translate('Out_For_Delivery')}} </option>
                                            <option
                                                value="delivered" {{$order['order_status'] == 'delivered'? 'selected' : ''}}>{{translate('Delivered')}} </option>
                                            <option
                                                value="returned" {{$order['order_status'] == 'returned'? 'selected' : ''}}> {{translate('Returned')}}</option>
                                            <option
                                                value="failed" {{$order['order_status'] == 'failed'? 'selected' : ''}}>{{translate('Failed')}} </option>
                                            <option
                                                value="canceled" {{$order['order_status'] == 'canceled'? 'selected' : ''}}>{{translate('canceled')}} </option>
                                        </select>
                                    @endif
                                </div>

                                <div class="mt-3">
                                    @if($order['order_type'] != 'pos')
                                        <h6>{{translate('Payment Status')}}</h6>
                                        <select name="order_status"
                                                onchange="route_alert('{{route('branch.orders.payment-status',['id'=>$order['id']])}}'+'&payment_status='+ this.value,'{{translate("Change status to ")}}'+ this.value)"
                                                class="status custom-select" data-id="100147">
                                            <option
                                                value="paid" {{$order['payment_status'] == 'paid'? 'selected' : ''}}> {{translate('paid')}}</option>
                                            <option
                                                value="partially_paid" {{$order['payment_status'] == 'partially_paid'? 'selected' : ''}}> {{translate('partially_paid')}}</option>
                                            <option
                                                value="unpaid" {{$order['payment_status'] == 'unpaid'? 'selected' : ''}}>{{translate('unpaid')}} </option>
                                        </select>
                                    @endif
                                </div>

                                @if($order['order_type']!='self_pickup' && $order['order_type'] != 'pos')
                                    <div class="hs-unfold mt-3">
                                        <select class="form-control" name="delivery_man_id" id="select-delivery-man">
                                            <option value="0">{{ translate('Select Delivery Man') }}</option>
                                            @foreach(\App\Models\DeliveryMan::where(['branch_id'=>auth('branch')->id()])->orWhere(['branch_id'=>0])->where(['application_status' => 'approved'])->get() as $deliveryMan)
                                                <option
                                                    value="{{$deliveryMan['id']}}" {{$order['delivery_man_id']==$deliveryMan['id']?'selected':''}}>
                                                    {{$deliveryMan['f_name'].' '.$deliveryMan['l_name']}}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif


                    @if($order['order_type'] != 'pos')
                        <div class="card mb-3">
                            <div class="card-header">
                                <h4 class="card-header-title">
                                    <i class="tio-info-outlined"></i> {{translate('Additional Order Data')}}
                                </h4>
                            </div>
                            <div class="card-body">
                                <dl class="row mb-0">
                                    @if($order['sale_channel'])
                                        <dt class="col-sm-4">{{translate('Sale Channel')}}</dt>
                                        <dd class="col-sm-8">{{$order['sale_channel']}}</dd>
                                    @endif

                                    @if($order['sale_agent'])
                                        <dt class="col-sm-4">{{translate('Sale Agent')}}</dt>
                                        <dd class="col-sm-8">
                                            {{$order['sale_agent']}}
                                            @if($order['agent_username'])
                                                <span>({{$order['agent_username']}})</span>
                                            @endif
                                        </dd>
                                    @endif

                                    @if($order['delivery_date'])
                                        <dt class="col-sm-4">{{translate('Delivery Date')}}</dt>
                                        <dd class="col-sm-8">{{date('d M Y', strtotime($order['delivery_date']))}}</dd>
                                    @endif

                                    @if($order['video_link'])
                                        <dt class="col-sm-4">{{translate('Video Link')}}</dt>
                                        <dd class="col-sm-8">
                                            <a href="{{$order['video_link']}}" target="_blank">{{translate('View Video')}}</a>
                                        </dd>
                                    @endif

                                    <dt class="col-sm-4">{{translate('Is Organic')}}</dt>
                                    <dd class="col-sm-8">
                                        @if($order['is_organic'])
                                            <span class="badge badge-soft-success">{{translate('Yes')}}</span>
                                        @else
                                            <span class="badge badge-soft-secondary">{{translate('No')}}</span>
                                        @endif
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    @endif

                    <div class="card mb-3">
                        <div class="card-header">
                            <h4 class="d-flex mb-0 gap-2 align-items-center"><i
                                    class="tio tio-user"></i> {{translate('Customer_Information')}}</h4>
                        </div>

                        <div class="card-body">
                            <div class="media gap-3">
                                @if($order->is_guest == 1)
                                    <div class="media-body d-flex flex-column gap-1 text-dark">
                                        <span class="fz--14px text--title font-semibold text-hover-primary d-block">
                                                {{translate('Guest Customer')}}
                                            </span>
                                    </div>
                                @else
                                    @if($order->customer)
                                        <div class="avatar-lg rounded-circle">
                                            <img class="img-fit rounded-circle"
                                                 src="{{$order->customer->image_fullpath}}"
                                                 alt="{{ translate('image') }}">
                                        </div>
                                        <div class="media-body d-flex flex-column gap-1 text-dark">
                                            <div>{{$order->customer['f_name'].' '.$order->customer['l_name']}}</div>
                                            <div>{{\App\Models\Order::where('user_id',$order['user_id'])->count()}} {{translate('orders')}}</div>
                                            <a class="text-dark"
                                               href="tel:{{$order->customer['phone']}}">{{$order->customer['phone']}}</a>
                                            <a class="text-dark"
                                               href="mailto:{{$order->customer['email']}}">{{$order->customer['email']}}</a>
                                        </div>
                                    @else
                                        <div class="media-body d-flex flex-column gap-1 text-dark">
                                        <span class="fz--14px text--title font-semibold text-hover-primary d-block">
                                            {{translate('Customer_deleted')}}
                                        </span>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($order['order_type']!='self_pickup' && $order['order_type'] != 'pos')
                        <div class="card">
                            <div class="card-header">
                                <h4 class="d-flex mb-0 gap-2 align-items-center"><i
                                        class="tio tio-user"></i> {{translate('Delivery_Address')}}</h4>
                            </div>

                            <div class="card-body">
                                @php($address=\App\Models\CustomerAddress::find($order['delivery_address_id']))
                                <div class="d-flex justify-content-between gap-3">
                                    @if(isset($address))
                                        <div class="delivery--information-single flex-column flex-grow-1">
                                            <div class="d-flex">
                                                <div class="name">{{translate('name')}}</div>
                                                <div class="info">{{$address['contact_person_name']}}</div>
                                            </div>
                                            <div class="d-flex">
                                                <div class="name">{{translate('contact')}}</div>
                                                <a href="tel:{{$address['contact_person_number']}}"
                                                   class="info">{{$address['contact_person_number']}}</a>
                                            </div>
                                            @if($address['floor'])
                                                <div class="d-flex">
                                                    <div class="name">{{translate('floor')}}</div>
                                                    <div class="info">#{{$address['floor']}}</div>
                                                </div>
                                            @endif
                                            @if($address['house'])
                                                <div class="d-flex">
                                                    <div class="name">{{translate('house')}}</div>
                                                    <div class="info">#{{$address['house']}}</div>
                                                </div>
                                            @endif
                                            @if($address['road'])
                                                <div class="d-flex">
                                                    <div class="name">{{translate('road')}}</div>
                                                    <div class="info">#{{$address['road'] }}</div>
                                                </div>
                                            @endif
                                            <div class="d-flex">
                                                <div class="name">{{translate('address')}}</div>
                                                <div class="info">#{{$address['address'] }}</div>
                                            </div>
                                            @if($googleMapStatus ==1 && isset($address['latitude']) && isset($address['longitude']))
                                                <hr class="w-100">
                                                <div>
                                                    <a target="_blank" class="text-dark d-flex align-items-center gap-3"
                                                       href="http://maps.google.com/maps?z=12&t=m&q=loc:{{$address['latitude']}}+{{$address['longitude']}}">
                                                        <i class="tio-map"></i> {{$address['address']}}<br>
                                                    </a>
                                                </div>
                                            @else
                                                <div class="d-flex">
                                                    <div class="name">{{translate('address')}}</div>
                                                    <div class="info">#{{$address['address'] }}</div>
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                    @if(isset($address))
                                        <a class="link" data-toggle="modal" data-target="#shipping-address-modal"
                                           href="javascript:"><i class="tio tio-edit"></i></a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <div class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title h4"
                        id="mySmallModalLabel">{{translate('reference')}} {{translate('code')}} {{translate('add')}}</h5>
                    <button type="button" class="btn btn-xs btn-icon btn-ghost-secondary" data-dismiss="modal"
                            aria-label="Close">
                        <i class="tio-clear tio-lg"></i>
                    </button>
                </div>

                <form action="{{route('branch.orders.add-payment-ref-code',[$order['id']])}}" method="post">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <input type="text" name="transaction_reference" class="form-control"
                                   placeholder="{{translate('Ex : Code123')}}" required>
                        </div>
                        <button class="btn btn-primary">{{translate('submit')}}</button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <div id="shipping-address-modal" class="modal fade" tabindex="-1" role="dialog"
         aria-labelledby="exampleModalTopCoverTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-top-cover bg-dark text-center">
                    <figure class="position-absolute right-0 bottom-0 left-0 mb-minus-1px">
                        <svg preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px"
                             viewBox="0 0 1920 100.1">
                            <path fill="#fff" d="M0,0c0,0,934.4,93.4,1920,0v100.1H0L0,0z"/>
                        </svg>
                    </figure>

                    <div class="modal-close">
                        <button type="button" class="btn btn-icon btn-sm btn-ghost-light" data-dismiss="modal"
                                aria-label="Close">
                            <svg width="16" height="16" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg">
                                <path fill="currentColor"
                                      d="M11.5,9.5l5-5c0.2-0.2,0.2-0.6-0.1-0.9l-1-1c-0.3-0.3-0.7-0.3-0.9-0.1l-5,5l-5-5C4.3,2.3,3.9,2.4,3.6,2.6l-1,1 C2.4,3.9,2.3,4.3,2.5,4.5l5,5l-5,5c-0.2,0.2-0.2,0.6,0.1,0.9l1,1c0.3,0.3,0.7,0.3,0.9,0.1l5-5l5,5c0.2,0.2,0.6,0.2,0.9-0.1l1-1 c0.3-0.3,0.3-0.7,0.1-0.9L11.5,9.5z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="modal-top-cover-icon">
                    <span class="icon icon-lg icon-light icon-circle icon-centered shadow-soft">
                      <i class="tio-location-search"></i>
                    </span>
                </div>

                @php($address=\App\Models\CustomerAddress::find($order['delivery_address_id']))
                @if(isset($address))
                    <form action="{{route('branch.order.update-shipping',[$order['delivery_address_id']])}}"
                          method="post">
                        @csrf
                        <div class="modal-body">
                            <div class="row mb-3">
                                <label for="requiredLabel"
                                       class="col-md-2 col-form-label input-label text-md-right">{{translate('type')}}</label>
                                <div class="col-md-10 js-form-message">
                                    <input type="text" class="form-control" name="address_type"
                                           value="{{$address['address_type']}}" required>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="requiredLabel"
                                       class="col-md-2 col-form-label input-label text-md-right">{{translate('contact')}}</label>
                                <div class="col-md-10 js-form-message">
                                    <input type="text" class="form-control" name="contact_person_number"
                                           value="{{$address['contact_person_number']}}" required>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="requiredLabel"
                                       class="col-md-2 col-form-label input-label text-md-right">{{translate('name')}}</label>
                                <div class="col-md-10 js-form-message">
                                    <input type="text" class="form-control" name="contact_person_name"
                                           value="{{$address['contact_person_name']}}" required>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="requiredLabel"
                                       class="col-md-2 col-form-label input-label text-md-right">{{translate('address')}}</label>
                                <div class="col-md-10 js-form-message">
                                    <input type="text" class="form-control" name="address"
                                           value="{{$address['address']}}" required>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="requiredLabel" class="col-md-2 col-form-label input-label text-md-right">
                                    {{translate('road')}}
                                </label>
                                <div class="col-md-10 js-form-message">
                                    <input type="text" class="form-control" name="road" value="{{$address['road']}}">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="requiredLabel" class="col-md-2 col-form-label input-label text-md-right">
                                    {{translate('house')}}
                                </label>
                                <div class="col-md-4 js-form-message">
                                    <input type="text" class="form-control" name="house" value="{{$address['house']}}">
                                </div>
                                <label for="requiredLabel" class="col-md-2 col-form-label input-label text-md-right">
                                    {{translate('floor')}}
                                </label>
                                <div class="col-md-4 js-form-message">
                                    <input type="text" class="form-control" name="floor" value="{{$address['floor']}}">
                                </div>
                            </div>

                            @if($googleMapStatus ==1)
                                <div class="row mb-3">
                                    <label for="requiredLabel"
                                           class="col-md-2 col-form-label input-label text-md-right">
                                        {{translate('latitude')}}
                                    </label>
                                    <div class="col-md-4 js-form-message">
                                        <input type="text" class="form-control" name="latitude"
                                               value="{{$address['latitude']}}" required>
                                    </div>
                                    <label for="requiredLabel"
                                           class="col-md-2 col-form-label input-label text-md-right">
                                        {{translate('longitude')}}
                                    </label>
                                    <div class="col-md-4 js-form-message">
                                        <input type="text" class="form-control" name="longitude"
                                               value="{{$address['longitude']}}" required>
                                    </div>
                                </div>
                            @endif

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-white"
                                    data-dismiss="modal">{{translate('close')}}</button>
                            <button type="submit"
                                    class="btn btn-primary">{{translate('save')}} {{translate('changes')}}</button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>

    @include('branch-views.modal.print-invoice-modal')

    <div class="modal fade" id="pay-remaining-modal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{translate('دفع المبلغ المتبقي')}}</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{route('branch.orders.update-payment', [$order['id']])}}" method="post">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="input-label">{{translate('المبلغ المتبقي الحالي')}}</label>
                            <input type="text" class="form-control" value="{{ Helpers::set_symbol($total_due - $order['paid_amount']) }}" readonly>
                        </div>
                        <div class="form-group">
                            <label class="input-label">{{translate('المبلغ المراد دفعه')}}</label>
                            <input type="number" step="0.01" name="amount" class="form-control" placeholder="{{translate('أدخل المبلغ')}}" required max="{{ $total_due - $order['paid_amount'] }}" min="0.01">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{translate('إلغاء')}}</button>
                        <button type="submit" class="btn btn-primary">{{translate('دفع')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
    <script>
        "use strict"

        $('#select-delivery-man').on('change', function () {
            let id = $(this).val();
            addDeliveryMan(id);
        })

        function addDeliveryMan(id) {
            $.ajax({
                type: "GET",
                url: '{{url('/')}}/branch/orders/add-delivery-man/{{$order['id']}}/' + id,
                data: $('#product_form').serialize(),
                success: function (data) {
                    if (data.status == false) {
                        toastr.error(data.message, {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    } else {
                        toastr.success('{{ translate("Successfully added") }}', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    }
                },
                error: function () {
                    toastr.error('{{ translate("Add valid data") }}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }
            });
        }

        $('.last_location_view').on('click', function () {
            toastr.warning('{{translate("Only available when order is out for delivery!")}}', {
                CloseButton: true,
                ProgressBar: true
            });
        })
    </script>
@endpush
