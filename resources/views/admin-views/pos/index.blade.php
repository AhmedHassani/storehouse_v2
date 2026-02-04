@extends('layouts.admin.app')
@section('title', translate('كاشير'))
@section('content')
<div class="content container-fluid">
    <div class="row gy-3 gx-2">
        <div class="col-lg-7">
            <div class="card overflow-hidden card-h-100vh">
                <div class="pos-title d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">{{translate('Product_Section')}}</h4>
                    <button onclick="toggleFeatured()"
                        class="btn btn-sm {{request('featured') == 1 ? 'btn-danger' : 'btn-outline-secondary'}}">
                        <i class="tio-star"></i> {{translate('Favorites')}}
                    </button>
                </div>

                <div class="d-flex flex-wrap flex-md-nowrap justify-content-between gap-3 gap-xl-4 px-4 py-4">
                    <div class="w-100 mr-xl-2">
                        <select name="category" id="category" class="form-control js-select2-custom mx-1"
                            title="{{translate('select category')}}" onchange="set_category_filter(this.value)">
                            <option value="">{{translate('All Categories')}}</option>
                            @foreach ($categories as $item)
                                <option value="{{$item->id}}" {{$category == $item->id ? 'selected' : ''}}>{{$item->name}}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="w-100 mr-xl-2">
                        <form id="search-form" class="header-item">
                            <div class="input-group input-group-merge input-group-flush border rounded">
                                <div class="input-group-prepend pl-2">
                                    <div class="input-group-text">
                                        <i class="tio-search"></i>
                                    </div>
                                </div>
                                <input id="datatableSearch" type="search" value="{{$keyword ? $keyword : ''}}"
                                    name="search" class="form-control border-0 pr-2"
                                    placeholder="{{translate('Search here')}}" aria-label="Search here">
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card-body pt-0" id="items">
                    <div class="pos-item-wrap justify-content-center">
                        @foreach($products as $product)
                            @include('admin-views.pos._single_product', ['product' => $product])
                        @endforeach
                    </div>
                </div>
                <div class="px-3 d-flex justify-content-end">
                    {!!$products->withQueryString()->links()!!}
                </div>
                @if(count($products) == 0)
                    <div class="text-center p-4">
                        <img class="mb-3 width-7rem" src="{{asset('public/assets/admin/svg/illustrations/sorry.svg')}}"
                            alt="{{ translate('image') }}">
                        <p class="mb-0">{{ translate('No data to show') }}</p>
                    </div>
                @endif
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card card-h-100vh">
                <div class="pos-title">
                    <h4 class="mb-0">{{translate('Billing_Section')}}</h4>
                </div>
                <div class="p-2 p-sm-4 max-h-100 overflow-y-auto">
                    <div class="form-group d-flex gap-2">
                        <select id='customer' name="customer_id" onchange="store_key('customer_id',this.value)"
                            data-placeholder="{{translate('Walk In Customer')}}"
                            class="js-data-example-ajax-2 form-control js-select2-custom m-1">
                            <option value="" selected disabled>{{translate('Walking Customer')}}</option>
                            @foreach($users as $user)
                                <option value="{{$user['id']}}" {{ session()->get('customer_id') == $user['id'] ? 'selected' : '' }}>{{$user['f_name'] . ' ' . $user['l_name'] }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-success rounded text-nowrap" id="add_new_customer" type="button"
                            data-toggle="modal" data-target="#add-customer" title="Add Customer">
                            <i class="tio-add"></i>
                            {{translate('Customer')}}
                        </button>
                    </div>



                    <div id="cart">
                        @include('admin-views.pos._cart')
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="quick-view" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" id="quick-view-modal">

        </div>
    </div>
</div>

<div class="modal fade" id="add-customer" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header pb-0 border-0">
                <h4 class="modal-title" id="exampleModalLabel">{{translate('إضافة عميل جديد')}}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body pt-0">
                <form action="{{route('admin.pos.customer-store')}}" method="post" id="product_form"
                    enqueue="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-12 col-lg-6">
                            <div class="form-group">
                                <label class="input-label" for="">{{translate('الاسم الكامل')}} <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="f_name" class="form-control"
                                    placeholder="{{translate('الاسم الكامل')}}" required>
                            </div>
                        </div>
                        <div class="col-12 col-lg-6">
                            <div class="form-group">
                                <label class="input-label" for="">{{translate('رقم الهاتف')}} <span
                                        class="text-danger">*</span></label>
                                <input type="text" maxlength="11" name="phone" class="form-control"
                                    placeholder="{{translate('رقم الهاتف')}}" required>
                            </div>
                        </div>
                    </div>




                    <div class="row pl-2">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label class="input-label">{{translate('المحافظة')}}
                                    <span class="text-danger">*</span>
                                </label>
                                <select name="governate" required id="governate" class="form-control js-select2-custom"
                                    onchange="getAreas(this.value)">
                                    <option value="" selected disabled>{{ translate('Select Governate') }}</option>
                                    @foreach($provinces as $province)
                                        <option value="{{ $province->province }}">{{ $province->province }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label class="input-label">
                                    {{translate('المنطقة / القضاء')}}
                                    <span class="text-danger">*</span>
                                </label>
                                <select name="district" required id="district" class="form-control js-select2-custom">
                                    <option value="" selected disabled>{{ translate('Select Area') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>


                    <div class="row pl-2">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label class="input-label">{{translate('رقم الهاتف الثانوي')}}</label>
                                <input type="text" name="secondary_phone" class="form-control" pattern="07\d{9}"
                                    value="" maxlength="11" placeholder="{{ translate('07xxxxxxxxx') }}">
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label class="input-label">{{translate('العنوان التفصيلي')}}</label>
                                <input type="text" name="address" class="form-control" value=""
                                    placeholder="{{ translate('العنوان التفصيلي') }}">
                            </div>
                        </div>
                    </div>



                    <div class="row pl-2">
                        <div class="col-12">
                            <div class="form-group">
                                <label class="input-label">{{translate('ملاحظة')}}</label>
                                <input type="text" name="description" class="form-control" value=""
                                    placeholder="ملاحظة">
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="submit" id="" class="btn btn-primary">{{translate('حفظ')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@php($order = \App\Models\Order::find(session('last_order')))
@if($order)
@php(session(['last_order' => false]))
<div class="modal fade" id="print-invoice" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{translate('طباعة الفاتورة')}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body row front-emoji">
                <div class="col-md-12 text-center">
                    <div>
                        <input type="button" class="btn btn-primary non-printable" id="print-invoice-div"
                            data-name="printableArea"
                            value="{{translate('متابعة ، إذا كانت الطابعة الحرارية جاهزة.')}}" />
                        <a href="{{url()->previous()}}" class="btn btn-danger non-printable">{{translate('رجوع')}}</a>
                    </div>
                    <hr class="non-printable">
                </div>
                <div class="row m-auto" id="printableArea">
                    @include('admin-views.pos.order.invoice')
                </div>

            </div>
        </div>
    </div>
    @endif

    @include('admin-views.pos._delivery_fee_modal')

    @endsection

    @push('script_2')
        <script>
            "use strict";

            $(document).on('ready', function () {
                @if($order)
                    $('#print-invoice').modal('show');
                @endif
                                                                                                    });

            $("#print-invoice-div").on('click', function () {
                let name = $(this).data('name');
                printDiv(name);
            });

            function printDiv(divName) {
                let printContents = document.getElementById(divName).innerHTML;
                let originalContents = document.body.innerHTML;
                document.body.innerHTML = printContents;
                window.print();
                document.body.innerHTML = originalContents;
                location.reload();
            }

            function set_category_filter(id) {
                let nurl = new URL('{!!url()->full()!!}');
                nurl.searchParams.set('category_id', id);
                location.href = nurl;
            }

            function toggleFeatured() {
                let nurl = new URL('{!!url()->full()!!}');
                let current = nurl.searchParams.get('featured');
                if (current == '1') {
                    nurl.searchParams.delete('featured');
                } else {
                    nurl.searchParams.set('featured', '1');
                }
                location.href = nurl;
            }

            $('#search-form').on('submit', function (e) {
                e.preventDefault();
                let keyword = $('#datatableSearch').val();
                let nurl = new URL('{!!url()->full()!!}');
                nurl.searchParams.set('keyword', keyword);
                location.href = nurl;
            });

            $('.pos-single-product-card').on('click', function () {
                let productId = $(this).data('id');
                quickView(productId);
            });

            function quickView(product_id) {
                $.ajax({
                    url: '{{route('admin.pos.quick-view')}}',
                    type: 'GET',
                    data: {
                        product_id: product_id
                    },
                    dataType: 'json',
                    beforeSend: function () {
                        $('#loading').show();
                    },
                    success: function (data) {
                        $('#quick-view').modal('show');
                        $('#quick-view-modal').empty().html(data.view);
                    },
                    complete: function () {
                        $('#loading').hide();
                    },
                });
            }

            function checkAddToCartValidity() {
                return true;
            }

            function cartQuantityInitialize() {
                $('.btn-number').click(function (e) {
                    e.preventDefault();
                    let $btn = $(this);
                    let fieldName = $(this).attr('data-field');
                    let type = $(this).attr('data-type');
                    let input = $("input[name='" + fieldName + "']");
                    let currentVal = parseInt(input.val());
                    let $tooltip = $('.custom-tooltip');

                    if (!isNaN(currentVal)) {
                        if (type == 'minus') {

                            if (currentVal > input.attr('min')) {
                                input.val(currentVal - 1).change();
                                $tooltip.css('display', 'none');
                            }
                            if (parseInt(input.val()) == input.attr('min')) {
                                $(this).attr('disabled', true);
                            }

                        } else if (type == 'plus') {
                            let maxVal = parseInt(input.attr('max'));
                            if (isNaN(maxVal) || currentVal < maxVal) {
                                input.val(currentVal + 1).change();
                            }
                            if (!isNaN(maxVal) && currentVal >= maxVal) {
                                input.val(currentVal).change();
                                $tooltip.css('display', 'flex');
                                $btn.prop('disabled', true);
                            }
                        }
                    } else {
                        input.val(0);
                    }
                });

                $('.input-number').focusin(function () {
                    $(this).data('oldValue', $(this).val());
                });

                $('.input-number').change(function () {

                    let minValue = parseInt($(this).attr('min'));
                    let maxValue = parseInt($(this).attr('max'));
                    let valueCurrent = parseInt($(this).val());
                    let $tooltip = $('.custom-tooltip');
                    let name = $(this).attr('name');
                    if (valueCurrent >= minValue) {
                        $(".btn-number[data-type='minus'][data-field='" + name + "']").removeAttr('disabled')
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: '{{translate('Cart')}}',
                            confirmButtonText: '{{translate("Ok")}}',
                            text: '{{translate('Sorry, the minimum value was reached')}}'
                        });
                        $(this).val($(this).data('oldValue'));
                    }
                    if (isNaN(maxValue) || valueCurrent <= maxValue) {
                        $(".btn-number[data-type='plus'][data-field='" + name + "']").removeAttr('disabled');
                        $tooltip.css('display', 'none');
                    } else {
                        $(".btn-number[data-type='plus'][data-field='" + name + "']").attr('disabled', true)
                        $tooltip.css('display', 'flex');
                        $(this).val(maxValue);
                    }
                });
                $(".input-number").keydown(function (e) {
                    // Allow: backspace, delete, tab, escape, enter and .
                    if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 190]) !== -1 ||
                        // Allow: Ctrl+A
                        (e.keyCode == 65 && e.ctrlKey === true) ||
                        // Allow: home, end, left, right
                        (e.keyCode >= 35 && e.keyCode <= 39)) {
                        // let it happen, don't do anything
                        return;
                    }
                    // Ensure that it is a number and stop the keypress
                    if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                        e.preventDefault();
                    }
                });
            }

            function getVariantPrice() {
                let quantity = $('#quantity').val();
                if (quantity > 0 && checkAddToCartValidity()) {
                    let formData = {
                        _token: $('meta[name="csrf-token"]').attr('content'), // CSRF token
                        id: $('input[name="id"]').val(),
                        quantity: quantity
                    };

                    // Collect selected choices (radio inputs)
                    $('[type=radio]:checked').each(function () {
                        formData[$(this).attr('name')] = $(this).val();
                    });

                    $.ajax({
                        type: "POST",
                        url: '{{ route('admin.pos.variant_price') }}',
                        data: formData,
                        success: function (data) {
                            console.log(data)
                            $('#chosen_price_div').removeClass('d-none');
                            $('#chosen_price').html(round(data.price, 2).toFixed(2));
                            $(".total-stock").html(data.stock);
                            if (data.stock > 0) {
                                if ($('#quantity').val() > data.stock) {
                                    let $tooltip = $('.custom-tooltip');
                                    $(".btn-number[data-type='plus'][data-field='quantity']").removeAttr('disabled');
                                    $tooltip.css('display', 'none');
                                    $('#quantity').val(1);
                                }
                                $('#quantity').attr({ "max": data.stock });
                            } else {
                                $('#quantity').removeAttr("max");
                            }
                        },
                        error: function (xhr) {
                            console.error(xhr.responseJSON || xhr.responseText);
                        }
                    });
                }
            }


            $('.add-to-shopping-cart').on('click', function () {
                addToCart();
            });

            function addToCart(form_id = 'add-to-cart-form') {
                if (checkAddToCartValidity()) {
                    let formData = {
                        _token: $('meta[name="csrf-token"]').attr('content'), // CSRF token
                        id: $('input[name="id"]').val(),
                        quantity: $('input[name="quantity"]').val()
                    };

                    // Collect selected choices (radio inputs)
                    $('[type=radio]:checked').each(function () {
                        formData[$(this).attr('name')] = $(this).val();
                    });
                    $.post({
                        url: '{{ route('admin.pos.add-to-cart') }}',
                        data: formData,
                        beforeSend: function () {
                            $('#loading').show();
                        },
                        success: function (data) {
                            console.log('Add to cart response:', data);
                            if (data.data == 1) {
                                Swal.fire({
                                    icon: 'info',
                                    title: '{{translate('Cart')}}',
                                    confirmButtonText: '{{translate("Ok")}}',
                                    text: "{{translate('Product already added in cart')}}"
                                });
                                $('#loading').hide();
                                return false;
                            } else if (data.data == 0) {
                                Swal.fire({
                                    icon: 'error',
                                    title: '{{translate('Cart')}}',
                                    confirmButtonText: '{{translate("Ok")}}',
                                    text: '{{translate('Sorry, product out of stock')}}.'
                                });
                                $('#loading').hide();
                                return false;
                            }
                            $('.call-when-done').click();
                            toastr.success('{{translate('Item has been added in your cart')}}!', {
                                CloseButton: true,
                                ProgressBar: true
                            });

                            updateCart();
                        },
                        error: function (xhr, status, error) {
                            console.error('Error adding to cart:', error);
                            console.error('Response:', xhr.responseText);
                            toastr.error('Error adding product to cart', {
                                CloseButton: true,
                                ProgressBar: true
                            });
                        },
                        complete: function () {
                            $('#loading').hide();
                        }
                    });
                } else {
                    Swal.fire({
                        type: 'info',
                        title: '{{translate('Cart')}}',
                        confirmButtonText: '{{translate("Ok")}}',
                        text: '{{translate('Please choose all the options')}}'
                    });
                }
            }

            function removeFromCart(key) {
                $.post('{{ route('admin.pos.remove-from-cart') }}', {
                    _token: '{{ csrf_token() }}',
                    key: key
                }, function (data) {
                    if (data.errors) {
                        for (let i = 0; i < data.errors.length; i++) {
                            toastr.error(data.errors[i].message, {
                                CloseButton: true,
                                ProgressBar: true
                            });
                        }
                    } else {
                        updateCart();
                        toastr.info('{{translate('Item has been removed from cart')}}', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    }

                });
            }

            $('.pos-empty-cart').on('click', function () {
                emptyCart();
            });

            function emptyCart() {
                $.post('{{ route('admin.pos.emptyCart') }}', { _token: '{{ csrf_token() }}' }, function (data) {
                    updateCart();
                    toastr.info('{{translate('Item has been removed from cart')}}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                    location.reload();
                });
            }


            function updateCart() {
                console.log('Updating cart...');
                $.post('<?php echo e(route('admin.pos.cart_items')); ?>', { _token: '<?php echo e(csrf_token()); ?>' }, function (data) {
                    console.log('Cart updated successfully');
                    $('#cart').empty().html(data);
                    calculateAmountDifference();
                }).fail(function (xhr, status, error) {
                    console.error('Error updating cart:', error);
                    console.error('Response:', xhr.responseText);
                });
            }

            function store_key(key, value) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': "{{csrf_token()}}"
                    }
                });
                $.post({
                    url: '{{route('admin.pos.store-keys')}}',
                    data: {
                        key: key,
                        value: value,
                    },
                    success: function (data) {
                        let selected_field_text = key;
                        var selected_field = selected_field_text.replace("_", " ");
                        var selected_field = selected_field.replace("id", " ");
                        var message = selected_field + ' ' + 'selected!';
                        var new_message = message.charAt(0).toUpperCase() + message.slice(1);
                        toastr.success((new_message), {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    },
                });
            }

            $(function () {
                $(document).on('click', 'input[type=number]', function () {
                    this.select();
                });
            });

            function storeOldValue(input) {
                $(input).data('old', $(input).val());
            }

            function updateQuantity(e) {
                let element = $(e.target);
                var minValue = parseInt(element.attr('min'));
                var valueCurrent = parseInt(element.val());

                let key = element.data('key');

                // If input is empty/NaN (e.g. while clearing), do nothing yet.
                if (isNaN(valueCurrent)) {
                    return;
                }

                // Allow any quantity >= minValue (defaults to 1)
                if (valueCurrent >= minValue) {
                    $.post('{{ route('admin.pos.updateQuantity') }}', {
                        _token: '{{ csrf_token() }}',
                        key: key,
                        quantity: valueCurrent
                    }, function (data) {
                        updateCart();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: '{{translate("Cart")}}',
                        text: '{{translate('Sorry, the minimum value was reached')}}',
                        confirmButtonText: '{{translate("Yes")}}',
                    });
                    element.val(element.data('old'));
                }


                if (e.type == 'keydown') {
                    if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 190]) !== -1 ||
                        (e.keyCode == 65 && e.ctrlKey === true) ||
                        (e.keyCode >= 35 && e.keyCode <= 39)) {
                        return;
                    }
                    if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                        e.preventDefault();
                    }
                }

            }

            $('.js-select2-custom').each(function () {
                let select2 = $.HSCore.components.HSSelect2.init($(this));
            });

            $('.js-data-example-ajax').select2({
                ajax: {
                    url: '{{route('admin.pos.customers')}}',
                    data: function (params) {
                        return {
                            q: params.term,
                            page: params.page
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: data
                        };
                    },
                    __port: function (params, success, failure) {
                        let $request = $.ajax(params);

                        $request.then(success);
                        $request.fail(failure);

                        return $request;
                    }
                }
            });


            $('.js-data-example-ajax-2').select2()

            $('#order_place').submit(function (eventObj) {
                if ($('#customer').val()) {
                    $(this).append('<input type="hidden" name="user_id" value="' + $('#customer').val() + '" /> ');
                }
                return true;
            });

            $(document).on('input', '#showPaidAmount', function () {
                calculateAmountDifference();
            });

            $(document).on('change', 'input[name="type"]', function () {
                toggleInput();
            });

            $(document).on('input', '#yourInputId', function () {
                // Remove leading zeros (e.g. "0005" becomes "5")
                let value = this.value.replace(/^0+(?=\d)/, '');

                // If the value starts with a decimal point (e.g. ".5"), add a leading "0" (becomes "0.5")
                if (value.startsWith('.')) {
                    value = '0' + value;
                }

                // Ensure only one decimal point is allowed
                if ((value.match(/\./g) || []).length > 1) {
                    value = value.replace(/\.+$/, ''); // Remove any extra decimal points
                }

                // Update the input value with the cleaned value
                this.value = value;
            });



            function calculateAmountDifference() {
                const showPaidAmountInput = document.getElementById('showPaidAmount');
                const amountDiffInput = document.getElementById('amount-difference');
                const paidAmountInput = document.getElementById('paidAmount');
                const totalAmountInput = document.getElementById('totalAmount');
                const placeOrderWrapper = document.getElementById('placeOrder');
                const disablePlaceOrderWrapper = document.getElementById('disablePlaceOrder');
                const diffLabel = document.querySelector('.due-or-change-amount');

                const totalAmount = parseFloat(totalAmountInput.value) || 0;
                const paidAmount = parseFloat(showPaidAmountInput.value) || 0;

                const difference = paidAmount - totalAmount;
                paidAmountInput.value = paidAmount.toFixed(2);
                amountDiffInput.value = Math.abs(difference).toFixed(2);

                if (difference >= 0) {
                    diffLabel.innerHTML = '{{translate("Change Amount")}} :';
                } else {
                    diffLabel.innerHTML = '{{translate("Due Amount")}} :';
                }

                $(placeOrderWrapper).removeClass('d-none');
                $(disablePlaceOrderWrapper).addClass('d-none');
            }


            function toggleInput() {
                let cashRadio = document.getElementById('cash');
                let showPaidAmountInput = document.getElementById('showPaidAmount');
                if (cashRadio && cashRadio.checked) {
                    showPaidAmountInput.removeAttribute('disabled');
                    calculateAmountDifference(); // Immediately calculate
                } else {
                    calculateAmountDifference();
                    showPaidAmountInput.setAttribute('disabled', true);
                }
            }

            function getAreas(province) {
                $.ajax({
                    url: '{{route('admin.pos.get-areas')}}',
                    type: 'GET',
                    data: {
                        province: province
                    },
                    dataType: 'json',
                    success: function (data) {
                        $('#district').empty();
                        $('#district').append('<option value="" selected disabled>{{ translate('Select Area') }}</option>');
                        $.each(data, function (key, value) {
                            $('#district').append('<option value="' + value.area_name + '">' + value.area_name + '</option>');
                        });
                    },
                });
            }

        </script>
        <script>
            if (/MSIE \d|Trident.*rv:/.test(navigator.userAgent)) document.write('<script src="{{asset('public/assets/admin')}}/vendor/babel-polyfill/polyfill.min.js"><\/script>');
        </script>
    @endpush