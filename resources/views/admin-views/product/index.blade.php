@extends('layouts.admin.app')

@section('title', translate('Add new product'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="{{asset('public/assets/admin/css/tags-input.min.css')}}" rel="stylesheet">
    <style>
        .compact-card {
            margin-bottom: 1rem !important;
        }
        .compact-card .card-header {
            padding: 0.5rem 1rem;
            background: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
        }
        .compact-card .card-header h6 {
            margin: 0;
            font-size: 0.9rem;
            font-weight: 600;
        }
        .compact-card .card-body {
            padding: 1rem;
        }
        .compact-form-group {
            margin-bottom: 0.75rem;
        }
        .compact-label {
            font-size: 0.85rem;
            margin-bottom: 0.25rem;
            font-weight: 500;
        }
        .compact-input {
            padding: 0.4rem 0.75rem;
            font-size: 0.875rem;
        }
        .compact-helper {
            font-size: 0.75rem;
            margin-top: 0.15rem;
        }
    </style>
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="mb-2">
            <h4 class="mb-0 d-flex align-items-center gap-2">
                <img width="18" src="{{asset('public/assets/admin/img/icons/product.png')}}" alt="{{ translate('product') }}">
                {{translate('add_new_product')}}
            </h4>
        </div>

        <div class="row">
            <div class="col-12">
                <form action="javascript:" method="post" id="product_form" enctype="multipart/form-data">
                    @csrf
                    @php($language=\App\Models\BusinessSetting::where('key','language')->first())
                    @php($language = $language->value ?? null)
                    @php($default_lang = 'bn')
                    @if($language)
                        @php($default_lang = json_decode($language)[0])
                        
                        @foreach(json_decode($language) as $lang)
                            <div class="card compact-card {{$lang != $default_lang ? 'd-none':''}} lang_form" id="{{$lang}}-form">
                                <div class="card-body">
                                    <div class="form-group compact-form-group">
                                        <label class="input-label compact-label" for="{{$lang}}_name">
                                            {{translate('name')}} ({{strtoupper($lang)}}) <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" {{$lang == $default_lang? 'required':''}} name="name[]"
                                               id="{{$lang}}_name" class="form-control compact-input"
                                               placeholder="{{ translate('New Product') }}"
                                               oninvalid="document.getElementById('en-link').click()">
                                    </div>
                                    <input type="hidden" name="lang[]" value="{{$lang}}">
                                    <div class="form-group compact-form-group">
                                        <label class="input-label compact-label" for="{{$lang}}_description">
                                            {{translate('short')}} {{translate('description')}} ({{strtoupper($lang)}})
                                        </label>
                                        <div id="{{$lang}}_editor"></div>
                                        <textarea name="description[]" style="display:none" id="{{$lang}}_hiddenArea"></textarea>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="card compact-card" id="{{$default_lang}}-form">
                            <div class="card-body">
                                <div class="form-group compact-form-group">
                                    <label class="input-label compact-label">{{translate('name')}} (EN) <span class="text-danger">*</span></label>
                                    <input type="text" name="name[]" class="form-control compact-input"
                                           placeholder="{{ translate('new_product') }}" required>
                                </div>
                                <input type="hidden" name="lang[]" value="en">
                                <div class="form-group compact-form-group">
                                    <label class="input-label compact-label">{{translate('short')}} {{translate('description')}} (EN)</label>
                                    <div id="editor"></div>
                                    <textarea name="description[]" style="display:none" id="hiddenArea"></textarea>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div id="from_part_2">
                        {{-- Pricing & Stock Section --}}
                        <div class="card compact-card">
                            <div class="card-header">
                                <h6><i class="tio-money"></i> {{translate('Pricing & Stock Information')}}</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-4 col-sm-6">
                                        <div class="form-group">
                                            <label class="input-label" for="price">
                                                {{translate('price')}} <span class="text-danger">*</span>
                                            </label>
                                            <input type="number" min="1" max="100000000" step="0.01" value="1"
                                                   name="price" id="price" class="form-control"
                                                   placeholder="{{ translate('Ex : 100') }}" required>
                                            <small class="form-text text-muted">{{translate('Product selling price')}}</small>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-sm-6">
                                        <div class="form-group">
                                            <label class="input-label" for="purchase_price">
                                                {{translate('purchase_price')}} <span class="text-danger">*</span>
                                            </label>
                                            <input type="number" min="0" max="100000000" step="0.01" value="0"
                                                   name="purchase_price" id="purchase_price" class="form-control"
                                                   placeholder="{{ translate('Ex : 100') }}" required>
                                            <small class="form-text text-muted">{{translate('Product purchase price')}}</small>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-sm-6">
                                        <div class="form-group compact-form-group">
                                            <label class="input-label compact-label" for="unit">
                                                {{translate('unit')}} <span class="text-danger">*</span>
                                            </label>
                                            <select name="unit" id="unit" class="form-control compact-input js-select2-custom">
                                                <option value="kg">{{translate('kg')}}</option>
                                                <option value="gm">{{translate('gm')}}</option>
                                                <option value="ltr">{{translate('ltr')}}</option>
                                                <option value="pc" selected>{{translate('pc')}}</option>
                                            </select>
                                            <small class="form-text text-muted compact-helper">{{translate('Unit of measurement')}}</small>
                                        </div>
                                    </div>

                                    {{-- Tax Fields Hidden --}}
                                    {{-- <div class="col-lg-4 col-sm-6">
                                        <div class="form-group">
                                            <label class="input-label"
                                                   for="exampleFormControlInput1">{{translate('tax')}}</label>
                                            <input type="number" min="0" value="0" step="0.01" max="100000" name="tax"
                                                   class="form-control"
                                                   placeholder="{{ translate('Ex : 7') }}" required>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-sm-6">
                                        <div class="form-group">
                                            <label class="input-label"
                                                   for="exampleFormControlInput1">{{translate('tax')}} {{translate('type')}}</label>
                                            <select name="tax_type" class="form-control js-select2-custom">
                                                <option value="percent">{{translate('percent')}}</option>
                                                <option value="amount">{{translate('amount')}}</option>
                                            </select>
                                        </div>
                                    </div> --}}

                                    {{-- Hidden inputs for tax with default values --}}
                                    <input type="hidden" name="tax" value="0">
                                    <input type="hidden" name="tax_type" value="percent">

                                    <div class="col-lg-3 col-sm-6">
                                        <div class="form-group compact-form-group">
                                            <label class="input-label compact-label" for="discount">
                                                {{translate('discount')}}
                                            </label>
                                            <input type="number" min="0" max="100000" value="0" step="0.01"
                                                   name="discount" id="discount" class="form-control compact-input"
                                                   placeholder="{{ translate('Ex : 100') }}" required>
                                            <small class="form-text text-muted compact-helper">{{translate('Discount amount or percentage')}}</small>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-sm-6">
                                        <div class="form-group compact-form-group">
                                            <label class="input-label compact-label" for="discount_type">
                                                {{translate('discount')}} {{translate('type')}}
                                            </label>
                                            <select name="discount_type" id="discount_type" class="form-control compact-input js-select2-custom">
                                                <option value="percent">{{translate('percent')}}</option>
                                                <option value="amount">{{translate('amount')}}</option>
                                            </select>
                                            <small class="form-text text-muted compact-helper">{{translate('Type of discount calculation')}}</small>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-sm-6">
                                        <div class="form-group compact-form-group">
                                            <label class="input-label compact-label" for="product_stock">
                                                {{translate('stock')}} <span class="text-danger">*</span>
                                            </label>
                                            <input type="number" min="0" max="100000000" value="0" name="total_stock"
                                                   class="form-control compact-input" id="product_stock"
                                                   placeholder="{{ translate('Ex : 100') }}" required>
                                            <small class="form-text text-muted compact-helper">{{translate('Available quantity in inventory')}}</small>
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-sm-6">
                                        <div class="form-group compact-form-group">
                                            <label class="input-label compact-label d-block">
                                                {{translate('Stock Management')}}
                                            </label>
                                            <div class="custom-control custom-checkbox" style="margin-top: 0.5rem;">
                                                <input type="checkbox" class="custom-control-input" id="is_unlimited" name="is_unlimited" value="1">
                                                <label class="custom-control-label" for="is_unlimited" style="font-size: 0.85rem;">
                                                    <strong>{{translate('Unlimited Stock')}}</strong>
                                                </label>
                                            </div>
                                            <small class="form-text text-muted compact-helper">{{translate('If checked, stock will not decrease when selling')}}</small>
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-sm-6">
                                        <div class="form-group compact-form-group">
                                            <label class="input-label compact-label d-block">
                                                {{translate('Featured Product')}}
                                            </label>
                                            <div class="custom-control custom-checkbox" style="margin-top: 0.5rem;">
                                                <input type="checkbox" class="custom-control-input" id="is_featured" name="is_featured" value="1">
                                                <label class="custom-control-label" for="is_featured" style="font-size: 0.85rem;">
                                                    <strong>{{translate('منتج مفضل')}}</strong>
                                                </label>
                                            </div>
                                            <small class="form-text text-muted compact-helper">{{translate('If checked, product will appear in featured section')}}</small>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        {{-- Category Section --}}
                        <div class="card compact-card">
                            <div class="card-header">
                                <h6><i class="tio-category"></i> {{translate('Category Selection')}}</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group compact-form-group">
                                            <label class="input-label compact-label" for="category-id">
                                                {{translate('category')}} <span class="text-danger">*</span>
                                            </label>
                                            <select name="category_id" id="category-id" class="form-control compact-input js-select2-custom"
                                                    onchange="getRequest('{{url('/')}}/admin/product/get-categories?parent_id='+this.value,'sub-categories')">
                                                <option value="">---{{translate('select category')}}---</option>
                                                @foreach($categories as $category)
                                                    <option value="{{$category['id']}}">{{$category['name']}}</option>
                                                @endforeach
                                            </select>
                                            <small class="form-text text-muted compact-helper">{{translate('Main product category')}}</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group compact-form-group">
                                            <label class="input-label compact-label" for="sub-categories">
                                                {{translate('sub_category')}}
                                            </label>
                                            <select name="sub_category_id" id="sub-categories"
                                                    class="form-control compact-input js-select2-custom"
                                                    onchange="getRequest('{{url('/')}}/admin/product/get-categories?parent_id='+this.value,'sub-sub-categories')">
                                                <option value="">---{{translate('select sub category')}}---</option>
                                            </select>
                                            <small class="form-text text-muted compact-helper">{{translate('Optional: Select after choosing main category')}}</small>
                                        </div>
                                    </div>
                                    {{-- Attributes Section Hidden --}}
                                    {{-- <div class="col-12">
                                        <div class="form-group">
                                            <label class="input-label">
                                                {{translate('select_attributes')}}
                                                <span class="input-label-secondary"></span>
                                            </label>
                                            <select name="attribute_id[]" id="choice_attributes"
                                                    class="form-control js-select2-custom"
                                                    multiple="multiple">
                                                @foreach(\App\Models\Attribute::orderBy('name')->get() as $attribute)
                                                    <option value="{{$attribute['id']}}">{{$attribute['name']}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="customer_choice_options mb-4" id="customer_choice_options"></div>
                                        <div class="variant_combination mb-4" id="variant_combination"></div> --}}
                                        <div class="col-12">
                                        <div>
                                            <div class="mb-2">
                                                <label class="text-capitalize">{{translate('product_image')}}</label>
                                                <small class="text-danger"> * ( {{translate('ratio')}} 1:1 )</small>
                                            </div>
                                            <div class="row" id="coba"></div>
                                        </div>
                                        <div class="d-flex justify-content-end gap-3">
                                            <button type="reset"
                                                    class="btn btn-secondary">{{translate('reset')}}</button>
                                            <button type="submit"
                                                    class="btn btn-primary">{{translate('submit')}}</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('script_2')
    <script src="{{asset('public/assets/admin/js/spartan-multi-image-picker.js')}}"></script>
    <script src="{{asset('public/assets/admin')}}/js/tags-input.min.js"></script>
    <script src="{{ asset('public/assets/admin/js/quill-editor.js') }}"></script>

    <script>
        "use strict";

        // Handle unlimited stock checkbox
        $(document).ready(function() {
            $('#is_unlimited').on('change', function() {
                if ($(this).is(':checked')) {
                    // When unlimited is checked: disable stock input and set to 0
                    $('#product_stock').prop('readonly', true);
                    $('#product_stock').val(0);
                    $('#product_stock').prop('required', false);
                } else {
                    // When unlimited is unchecked: enable stock input and make required
                    $('#product_stock').prop('readonly', false);
                    $('#product_stock').prop('required', true);
                    if ($('#product_stock').val() == 0) {
                        $('#product_stock').val('');
                    }
                }
            });
        });

        $(".lang_link").click(function (e) {
            e.preventDefault();
            $(".lang_link").removeClass('active');
            $(".lang_form").addClass('d-none');
            $(this).addClass('active');

            let form_id = this.id;
            let lang = form_id.split("-")[0];
            console.log(lang);
            $("#" + lang + "-form").removeClass('d-none');
            if (lang == '{{$default_lang}}') {
                $("#from_part_2").removeClass('d-none');
            } else {
                $("#from_part_2").addClass('d-none');
            }
        })

        $(function () {
            $("#coba").spartanMultiImagePicker({
                fieldName: 'images[]',
                maxCount: 4,
                rowHeight: '215px',
                groupClassName: 'col-auto',
                maxFileSize: '',
                placeholderImage: {
                    image: '{{asset('public/assets/admin/img/400x400/img2.jpg')}}',
                    width: '100%'
                },
                dropFileLabel: "Drop Here",
                onAddRow: function (index, file) {

                },
                onRenderedPreview: function (index) {

                },
                onRemoveRow: function (index) {

                },
                onExtensionErr: function (index, file) {
                    toastr.error('{{ translate("Please only input png or jpg type file") }}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                },
                onSizeErr: function (index, file) {
                    toastr.error('{{ translate("File size too big") }}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }
            });
        });

        function getRequest(route, id) {
            $.get({
                url: route,
                dataType: 'json',
                success: function (data) {
                    $('#' + id).empty().append(data.options);
                },
            });
        }

        $(document).on('ready', function () {
            $('.js-select2-custom').each(function () {
                var select2 = $.HSCore.components.HSSelect2.init($(this));
            });
        });

        $('#choice_attributes').on('change', function () {
            $('#customer_choice_options').html(null);
            $.each($("#choice_attributes option:selected"), function () {
                add_more_customer_choice_option($(this).val(), $(this).text());
            });
        });

        function add_more_customer_choice_option(i, name) {
            let n = name.split(' ').join('');
            $('#customer_choice_options').append('<div class="row"><div class="col-md-3"><input type="hidden" name="choice_no[]" value="' + i + '"><input type="text" class="form-control" name="choice[]" value="' + n + '" placeholder="Choice Title" readonly></div><div class="col-lg-9"><input type="text" class="form-control" name="choice_options_' + i + '[]" placeholder="Enter choice values" data-role="tagsinput" onchange="combination_update()"></div></div>');
            $("input[data-role=tagsinput], select[multiple][data-role=tagsinput]").tagsinput();
        }

        function combination_update() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                type: "POST",
                url: '{{route('admin.product.variant-combination')}}',
                data: $('#product_form').serialize(),
                success: function (data) {
                    $('#variant_combination').html(data.view);
                    if (data.length > 1) {
                        $('#quantity').hide();
                    } else {
                        $('#quantity').show();
                    }
                }
            });
        }

        @if($language)
        @foreach(json_decode($language) as $lang)
        var en_quill = new Quill('#{{$lang}}_editor', {
            theme: 'snow'
        });
        @endforeach
        @else
        var bn_quill = new Quill('#editor', {
            theme: 'snow'
        });
        @endif

        $('#product_form').on('submit', function () {
            @if($language)
            @foreach(json_decode($language) as $lang)
            var {{$lang}}_myEditor = document.querySelector('#{{$lang}}_editor')
            $("#{{$lang}}_hiddenArea").val({{$lang}}_myEditor.children[0].innerHTML);
            @endforeach
            @else
            var myEditor = document.querySelector('#editor')
            $("#hiddenArea").val(myEditor.children[0].innerHTML);
            @endif
            var formData = new FormData(this);
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: '{{route('admin.product.store')}}',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                success: function (data) {
                    if (data.errors) {
                        for (var i = 0; i < data.errors.length; i++) {
                            toastr.error(data.errors[i].message, {
                                CloseButton: true,
                                ProgressBar: true
                            });
                        }
                    } else {
                        toastr.success('{{ translate("product uploaded successfully!") }}', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                        setTimeout(function () {
                            location.href = '{{route('admin.product.list')}}';
                        }, 2000);
                    }
                }
            });
        });


        // Stock field is always editable now - no readonly
        function update_qty() {
            var total_qty = 0;
            var qty_elements = $('input[name^="stock_"]');
            for (var i = 0; i < qty_elements.length; i++) {
                total_qty += parseInt(qty_elements.eq(i).val());
            }
            // Removed readonly logic - stock is always editable
            // if (qty_elements.length > 0) {
            //     $('input[name="total_stock"]').attr("readonly", true);
            //     $('input[name="total_stock"]').val(total_qty);
            // } else {
            //     $('input[name="total_stock"]').attr("readonly", false);
            // }
        }
    </script>
@endpush
