@extends('layouts.admin.app')

@section('title', translate('Add new product'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="{{asset('assets/admin/css/tags-input.min.css')}}" rel="stylesheet">
    <style>
        /* ── Reset & base ───────────────────────────── */
        .pf-wrap {
            display: flex;
            gap: 16px;
            align-items: flex-start;
        }

        .pf-col-main {
            flex: 0 0 42%;
            max-width: 42%;
        }

        .pf-col-aside {
            flex: 0 0 calc(58% - 16px);
            max-width: calc(58% - 16px);
        }

        /* ── Page header ────────────────────────────── */
        .pf-header {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 14px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eef0f5;
        }

        .pf-header-icon {
            width: 34px;
            height: 34px;
            background: rgba(103, 58, 183, .1);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .pf-header h5 {
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
            color: #1a1a2e;
        }

        /* ── Outer shell (one clean card per column) ── */
        .pf-shell {
            background: #fff;
            border: 1px solid #eef0f5;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 12px;
        }

        /* ── Section inside the shell ── */
        .pf-section {
            padding: 14px 18px;
            border-bottom: 1px dashed #f0f0f8;
        }

        .pf-section:last-child {
            border-bottom: none;
        }

        /* ── Section title ── */
        .pf-section-title {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 12px;
        }

        .pf-section-title::before {
            content: '';
            width: 3px;
            height: 16px;
            background: #673ab7;
            border-radius: 2px;
            flex-shrink: 0;
        }

        .pf-section-title span {
            font-size: 0.8rem;
            font-weight: 700;
            color: #2d2d4e;
            letter-spacing: .01em;
        }

        /* keep .pf-card-body as alias */
        .pf-card-body {
            padding: 0;
        }


        /* ── Form controls ──────────────────────────── */
        .pf-label {
            font-size: 0.775rem;
            font-weight: 600;
            color: #5a5a7a;
            margin-bottom: 3px;
            display: block;
        }

        .pf-label .req {
            color: #e53e3e;
            margin-inline-start: 2px;
        }

        .form-control {
            border-radius: 7px !important;
            border-color: #dde2ef !important;
            font-size: 0.825rem !important;
            padding: 0.38rem 0.7rem !important;
            color: #2d2d4e !important;
            transition: border-color .15s, box-shadow .15s;
        }

        .form-control:focus {
            border-color: #673ab7 !important;
            box-shadow: 0 0 0 3px rgba(103, 58, 183, .1) !important;
        }

        .fg {
            margin-bottom: 8px;
        }

        /* ── Inline grid for 2/3/4 inputs side by side ─ */
        .pf-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .pf-grid-3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 8px;
        }

        .pf-grid-4 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr;
            gap: 8px;
        }

        /* ── Toggle checkboxes ──────────────────────── */
        .pf-checks {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 4px;
        }

        .pf-check-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            background: #f8f7ff;
            border: 1.5px solid #e5deff;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.825rem;
            font-weight: 700;
            color: #2d2d4e;
            transition: border-color .15s, background .15s, box-shadow .15s;
            white-space: nowrap;
            user-select: none;
        }

        .pf-check-item:hover {
            border-color: #673ab7;
            background: #ede8ff;
            box-shadow: 0 2px 8px rgba(103, 58, 183, .12);
        }

        .pf-check-item input[type=checkbox] {
            width: 16px;
            height: 16px;
            accent-color: #673ab7;
            margin: 0;
            cursor: pointer;
        }

        .pf-check-item .pf-check-icon {
            font-size: 1rem;
            line-height: 1;
        }

        /* ── Image picker compact ───────────────────── */
        #coba .spartan_image_placeholder {
            height: 90px !important;
            width: 90px !important;
        }

        /* ── Quill compact ──────────────────────────── */
        .ql-toolbar {
            border-radius: 7px 7px 0 0 !important;
            border-color: #dde2ef !important;
            padding: 4px 8px !important;
        }

        .ql-container {
            border-radius: 0 0 7px 7px !important;
            border-color: #dde2ef !important;
            min-height: 115px;
            max-height: 115px;
            overflow-y: auto;
            font-size: 0.825rem !important;
        }

        .ql-toolbar .ql-formats {
            margin-left: 0 !important;
        }

        /* ── Lang tabs ──────────────────────────────── */
        .pf-lang-tabs {
            display: flex;
            gap: 5px;
            margin-bottom: 10px;
            flex-wrap: wrap;
        }

        .pf-lang-tab {
            padding: 3px 12px;
            border-radius: 20px;
            font-size: 0.775rem;
            font-weight: 600;
            border: 1px solid #dde2ef;
            background: #fff;
            color: #4a4a6a;
            cursor: pointer;
            text-decoration: none !important;
            transition: all .15s;
        }

        .pf-lang-tab.active,
        .pf-lang-tab:hover {
            background: #673ab7;
            border-color: #673ab7;
            color: #fff !important;
        }

        /* ── Action bar ─────────────────────────────── */
        .pf-action-bar {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            background: #fafbff;
            border-top: 1px solid #eef0f5;
        }

        .btn-pf-reset {
            padding: 5px 18px;
            border-radius: 7px;
            font-size: 0.825rem;
            font-weight: 600;
            background: #fff;
            border: 1px solid #dde2ef;
            color: #4a4a6a;
            transition: all .15s;
            cursor: pointer;
        }

        .btn-pf-reset:hover {
            background: #f4f5f7;
        }

        .btn-pf-submit {
            padding: 5px 22px;
            border-radius: 7px;
            font-size: 0.825rem;
            font-weight: 700;
            background: #673ab7;
            border: none;
            color: #fff;
            transition: background .15s, box-shadow .15s;
            cursor: pointer;
        }

        .btn-pf-submit:hover {
            background: #5a2fa0;
            box-shadow: 0 4px 12px rgba(103, 58, 183, .3);
        }

        /* ── Responsive ─────────────────────────────── */
        @media(max-width:900px) {
            .pf-wrap {
                flex-direction: column;
            }

            .pf-col-main,
            .pf-col-aside {
                max-width: 100%;
                flex: none;
                width: 100%;
            }

            .pf-grid-4 {
                grid-template-columns: 1fr 1fr;
            }
        }
    </style>
@endpush

@section('content')
<div class="content container-fluid">

    {{-- Header --}}
    <div class="pf-header">
        <div class="pf-header-icon">
            <img width="16" src="{{asset('assets/admin/img/icons/product.png')}}" alt="">
        </div>
        <h5>{{ translate('add_new_product') }}</h5>
    </div>

    <form action="javascript:" method="post" id="product_form" enctype="multipart/form-data">
        @csrf
        @php($language = \App\Models\BusinessSetting::where('key', 'language')->first())
        @php($language = $language->value ?? null)
        @php($default_lang = 'bn')
        @if($language) @php($default_lang = json_decode($language)[0]) @endif

        <div class="pf-wrap">

            {{-- ══ COLUMN LEFT ══ --}}
            <div class="pf-col-main">
                <div class="pf-shell">

                    {{-- معلومات المنتج --}}
                    @if($language)
                        @foreach(json_decode($language) as $lang)
                            <div class="pf-section {{ $lang != $default_lang ? 'd-none' : '' }} lang_form" id="{{$lang}}-form">
                                <div class="pf-section-title"><span>{{ translate('معلومات المنتج') }}</span></div>
                                <div class="fg">
                                    <label class="pf-label">{{ translate('name') }} <span class="req">*</span></label>
                                    <input type="text" {{ $lang == $default_lang ? 'required' : '' }} name="name[]"
                                        id="{{$lang}}_name" class="form-control" placeholder="{{ translate('اسم المنتج') }}"
                                        oninvalid="document.getElementById('en-link').click()">
                                </div>
                                <input type="hidden" name="lang[]" value="{{$lang}}">
                                <div class="fg mb-0">
                                    <label class="pf-label">{{ translate('الوصف المختصر') }}</label>
                                    <div id="{{$lang}}_editor"></div>
                                    <textarea name="description[]" style="display:none" id="{{$lang}}_hiddenArea"></textarea>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="pf-section" id="{{$default_lang}}-form">
                            <div class="pf-section-title"><span>{{ translate('معلومات المنتج') }}</span></div>
                            <div class="fg">
                                <label class="pf-label">{{ translate('name') }} <span class="req">*</span></label>
                                <input type="text" name="name[]" class="form-control"
                                    placeholder="{{ translate('اسم المنتج') }}" required>
                            </div>
                            <input type="hidden" name="lang[]" value="en">
                            <div class="fg mb-0">
                                <label class="pf-label">{{ translate('الوصف المختصر') }}</label>
                                <div id="editor"></div>
                                <textarea name="description[]" style="display:none" id="hiddenArea"></textarea>
                            </div>
                        </div>
                    @endif

                    {{-- صورة المنتج --}}
                    <div class="pf-section">
                        <div class="pf-section-title"><span>{{ translate('product_image') }}</span></div>
                        <p style="font-size:0.75rem;color:#9ba3b8;margin-bottom:8px;">اضغط على الصورة لرفعها — بنسبة 1:1
                            — حتى 4 صور</p>
                        <div class="d-flex flex-wrap gap-2" id="coba"></div>
                    </div>

                </div>{{-- /pf-shell --}}
            </div>{{-- /col-main --}}

            {{-- ══ COLUMN RIGHT ══ --}}
            <div class="pf-col-aside" id="from_part_2">
                <div class="pf-shell">

                    {{-- الأسعار والمخزون --}}
                    <div class="pf-section">
                        <div class="pf-section-title"><span>{{ translate('الأسعار والمخزون') }}</span></div>

                        <div class="pf-grid-3">
                            <div class="fg">
                                <label class="pf-label">{{ translate('price') }} <span class="req">*</span></label>
                                <input type="number" min="1" max="100000000" step="0.01" value="1" name="price"
                                    id="price" class="form-control" placeholder="0.00" required>
                            </div>
                            <div class="fg">
                                <label class="pf-label">{{ translate('سعر الشراء') }} <span class="req">*</span></label>
                                <input type="number" min="0" max="100000000" step="0.01" value="0" name="purchase_price"
                                    class="form-control" placeholder="0.00" required>
                            </div>
                            <div class="fg">
                                <label class="pf-label">{{ translate('unit') }} <span class="req">*</span></label>
                                <select name="unit" class="form-control js-select2-custom">
                                    <option value="kg">{{ translate('kg') }}</option>
                                    <option value="gm">{{ translate('gm') }}</option>
                                    <option value="ltr">{{ translate('ltr') }}</option>
                                    <option value="pc" selected>{{ translate('pc') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="pf-grid-3">
                            <div class="fg mb-0">
                                <label class="pf-label">{{ translate('discount') }}</label>
                                <input type="number" min="0" max="100000" value="0" step="0.01" name="discount"
                                    class="form-control" placeholder="0" required>
                            </div>
                            <div class="fg mb-0">
                                <label class="pf-label">{{ translate('discount') }} {{ translate('type') }}</label>
                                <select name="discount_type" class="form-control js-select2-custom">
                                    <option value="percent">{{ translate('percent') }}</option>
                                    <option value="amount">{{ translate('amount') }}</option>
                                </select>
                            </div>
                            <div class="fg mb-0">
                                <label class="pf-label">{{ translate('stock') }} <span class="req">*</span></label>
                                <input type="number" min="0" max="100000000" value="0" name="total_stock"
                                    class="form-control" id="product_stock" placeholder="0" required>
                            </div>
                        </div>
                    </div>

                    {{-- الإعدادات --}}
                    <div class="pf-section">
                        <div class="pf-checks">
                            <label class="pf-check-item">
                                <input type="checkbox" id="is_unlimited" name="is_unlimited" value="1">

                                {{ translate('مخزون غير محدود') }}
                            </label>
                            <label class="pf-check-item">
                                <input type="checkbox" id="is_featured" name="is_featured" value="1">

                                {{ translate('منتج مفضل') }}
                            </label>
                        </div>
                        <input type="hidden" name="tax" value="0">
                        <input type="hidden" name="tax_type" value="percent">
                    </div>

                    {{-- الفئة --}}
                    <div class="pf-section">
                        <div class="pf-section-title"><span>{{ translate('category') }}</span></div>
                        <div class="pf-grid-2">
                            <div class="fg mb-0">
                                <label class="pf-label">{{ translate('category') }} <span class="req">*</span></label>
                                <select name="category_id" id="category-id" class="form-control js-select2-custom"
                                    onchange="onCategoryChange(this.value)">
                                    <option value="">— {{ translate('select category') }} —</option>
                                    @foreach($categories as $category)
                                        <option value="{{$category['id']}}">{{$category['name']}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="fg mb-0">
                                <label class="pf-label">{{ translate('sub_category') }}
                                    <span id="sub-cat-hint" style="font-size:0.7rem;color:#aaa;font-weight:400;"> — اختر
                                        الفئة أولاً</span>
                                </label>
                                <select name="sub_category_id" id="sub-categories"
                                    class="form-control js-select2-custom" disabled
                                    onchange="getRequest('{{url('/')}}/admin/product/get-categories?parent_id='+this.value,'sub-sub-categories')">
                                    <option value="">— {{ translate('select') }} —</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- زر الحفظ --}}
                    <div class="pf-section">
                        <button type="submit" class="btn-pf-submit"
                            style="width:100%;justify-content:center;display:flex;align-items:center;gap:6px;padding:10px 22px;font-size:0.9rem;">
                            <i class="tio-save"></i> {{ translate('حفظ المنتج') }}
                        </button>
                    </div>

                </div>{{-- /pf-shell --}}
            </div>{{-- /col-aside --}}
        </div>{{-- /wrap --}}
    </form>
</div>
@endsection

@push('script_2')
    <script src="{{asset('assets/admin/js/spartan-multi-image-picker.js')}}"></script>
    <script src="{{asset('assets/admin')}}/js/tags-input.min.js"></script>
    <script src="{{ asset('assets/admin/js/quill-editor.js') }}"></script>
    <script>
        "use strict";

        // Unlimited stock toggle
        $(document).ready(function () {
            $('#is_unlimited').on('change', function () {
                $('#product_stock').prop('required', !$(this).is(':checked'));
            });
        });

        // Language tabs
        $(".lang_link").click(function (e) {
            e.preventDefault();
            $(".lang_link").removeClass('active');
            $(".lang_form").addClass('d-none');
            $(this).addClass('active');
            let lang = this.id.split("-")[0];
            $("#" + lang + "-form").removeClass('d-none');
            if (lang === '{{$default_lang}}') {
                $("#from_part_2").removeClass('d-none');
            } else {
                $("#from_part_2").addClass('d-none');
            }
        });

        // Image picker
        $(function () {
            $("#coba").spartanMultiImagePicker({
                fieldName: 'images[]',
                maxCount: 4,
                rowHeight: '90px',
                groupClassName: 'col-auto',
                maxFileSize: '',
                placeholderImage: {
                    image: '{{asset('assets/admin/img/400x400/img2.jpg')}}',
                    width: '90px'
                },
                dropFileLabel: "Drop",
                onAddRow: function () { },
                onRenderedPreview: function () { },
                onRemoveRow: function () { },
                onExtensionErr: function () {
                    toastr.error('{{ translate("Please only input png or jpg type file") }}', { CloseButton: true, ProgressBar: true });
                },
                onSizeErr: function () {
                    toastr.error('{{ translate("File size too big") }}', { CloseButton: true, ProgressBar: true });
                }
            });
        });

        function getRequest(route, id) {
            $.get({
                url: route, dataType: 'json', success: function (data) {
                    $('#' + id).empty().append(data.options);
                }
            });
        }

        $(document).on('ready', function () {
            $('.js-select2-custom').each(function () {
                $.HSCore.components.HSSelect2.init($(this));
            });
        });

        // Quill editors — simplified toolbar (Bold, Italic, Ordered list, Bullet list only)
        var quillOptions = {
            theme: 'snow',
            modules: {
                toolbar: [
                    ['bold', 'italic'],
                    [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                    ['clean']
                ]
            }
        };
        @if($language)
            @foreach(json_decode($language) as $lang)
                var {{$lang}}_quill = new Quill('#{{$lang}}_editor', quillOptions);
            @endforeach
        @else
                var bn_quill = new Quill('#editor', quillOptions);
            @endif

        // Category → sub-category dependency
        function onCategoryChange(val) {
            var $sub = $('#sub-categories');
            var $hint = $('#sub-cat-hint');
            if (val) {
                getRequest('{{url('/')}}/admin/product/get-categories?parent_id=' + val, 'sub-categories');
                $sub.prop('disabled', false).css('opacity', '1');
                $hint.hide();
            } else {
                $sub.prop('disabled', true).css('opacity', '0.5');
                $hint.show();
            }
        }

        // Submit
        $('#product_form').on('submit', function () {
            @if($language)
                @foreach(json_decode($language) as $lang)
                    var {{$lang}}_ed = document.querySelector('#{{$lang}}_editor');
                    if ({{$lang}}_ed) $("#{{$lang}}_hiddenArea").val({{$lang}}_ed.children[0].innerHTML);
                @endforeach
            @else
                                            var ed = document.querySelector('#editor');
                    if (ed) $("#hiddenArea").val(ed.children[0].innerHTML);
                @endif

                var formData = new FormData(this);
            $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
            $.post({
                url: '{{route('admin.product.store')}}',
                data: formData,
                cache: false, contentType: false, processData: false,
                success: function (data) {
                    if (data.errors) {
                        data.errors.forEach(e => toastr.error(e.message, { CloseButton: true, ProgressBar: true }));
                    } else {
                        toastr.success('{{ translate("product uploaded successfully!") }}', { CloseButton: true, ProgressBar: true });
                        setTimeout(() => { location.href = '{{route('admin.product.list')}}'; }, 2000);
                    }
                }
            });
        });
    </script>
@endpush