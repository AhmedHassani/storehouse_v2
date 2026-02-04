@extends('layouts.admin.app')

@section('title', translate('حقول الطلب المتغيرة'))

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
            <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                <img width="20" src="{{asset('public/assets/admin/img/attribute.png')}}" alt="">
                <span class="page-header-title">
                    {{translate('حقول الطلب المتغيرة')}}
                </span>
            </h2>
        </div>
        <!-- End Page Header -->

        <div class="row g-3">
            <!-- Add New Field Card -->
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="tio-add-circle"></i>
                            {{translate('إضافة حقل متغير جديد')}}
                        </h5>
                    </div>
                    <div class="card-body">
                        <form action="{{route('admin.order-dynamic-fields.store')}}" method="post">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="input-label">{{translate('اسم الحقل')}} <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="field_name" class="form-control"
                                        placeholder="{{translate('مثال: ملاحظات العميل')}}" required>
                                    <small class="text-muted">{{translate('الاسم المعروض للحقل')}}</small>
                                </div>

                                <input type="hidden" name="field_key" value="{{Str::uuid()}}">

                                <div class="col-md-4">
                                    <label class="input-label">{{translate('نوع الحقل')}} <span
                                            class="text-danger">*</span></label>
                                    <select name="field_type" id="field_type" class="form-control" required>
                                        <option value="text">{{translate('نص')}}</option>
                                        <option value="textarea">{{translate('منطقة نصية')}}</option>
                                        <option value="number">{{translate('رقم')}}</option>
                                        <option value="date">{{translate('تاريخ')}}</option>
                                        <option value="select">{{translate('قائمة منسدلة')}}</option>
                                        <option value="checkbox">{{translate('خانات اختيار')}}</option>
                                        <option value="radio">{{translate('أزرار اختيار')}}</option>
                                    </select>
                                </div>

                                <!-- Options for Select/Radio (Hidden by default) -->
                                <div class="col-12" id="options_container" style="display: none;">
                                    <label class="input-label">{{translate('الخيارات')}} <span
                                            class="text-muted">({{translate('لحقول القائمة/الاختيار')}})</span></label>
                                    <div id="options_list">
                                        <div class="input-group mb-2">
                                            <input type="text" name="field_options[]" class="form-control"
                                                placeholder="{{translate('قيمة الخيار')}}">
                                            <button type="button" class="btn btn-outline-success" onclick="addOption()">
                                                <i class="tio-add"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <label class="input-label">{{translate('القيمة الافتراضية')}}</label>
                                    <input type="text" name="default_value" class="form-control"
                                        placeholder="{{translate('اختياري')}}">
                                </div>

                                <div class="col-md-2">
                                    <label class="input-label">{{translate('الترتيب')}}</label>
                                    <input type="number" name="sort_order" class="form-control" value="0" min="0">
                                </div>

                                <div class="col-md-3">
                                    <label class="input-label d-block">{{translate('خيارات')}}</label>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="is_required"
                                            name="is_required" value="1">
                                        <label class="custom-control-label" for="is_required">
                                            {{translate('حقل مطلوب')}}
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <label class="input-label d-block">&nbsp;</label>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active"
                                            value="1" checked>
                                        <label class="custom-control-label" for="is_active">
                                            {{translate('نشط')}}
                                        </label>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="d-flex justify-content-end gap-3">
                                        <button type="reset" class="btn btn-secondary">{{translate('إعادة تعيين')}}</button>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="tio-add"></i> {{translate('إضافة الحقل')}}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Fields List -->
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="tio-filter-list"></i>
                            {{translate('الحقول المتغيرة الموجودة')}}
                        </h5>
                        <span class="badge badge-soft-info">{{count($fields)}} {{translate('حقل')}}</span>
                    </div>
                    <div class="table-responsive">
                        <table
                            class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                            <thead class="thead-light">
                                <tr>
                                    <th>{{translate('#')}}</th>
                                    <th>{{translate('اسم الحقل')}}</th>
                                    <th>{{translate('مفتاح الحقل')}}</th>
                                    <th>{{translate('النوع')}}</th>
                                    <th>{{translate('مطلوب')}}</th>
                                    <th>{{translate('الحالة')}}</th>
                                    <th>{{translate('الترتيب')}}</th>
                                    <th class="text-center">{{translate('إجراء')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($fields as $key => $field)
                                    <tr>
                                        <td>{{$key + 1}}</td>
                                        <td>
                                            <strong>{{$field->field_name}}</strong>
                                            @if($field->default_value)
                                                <br><small class="text-muted">{{translate('الافتراضي')}}:
                                                    {{$field->default_value}}</small>
                                            @endif
                                        </td>
                                        <td><code>{{$field->field_key}}</code></td>
                                        <td>
                                            <span class="badge badge-soft-info">
                                                {{ucfirst($field->field_type)}}
                                            </span>
                                            @if(in_array($field->field_type, ['select', 'radio']) && $field->options_array)
                                                <br><small class="text-muted">{{count($field->options_array)}}
                                                    {{translate('خيار')}}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($field->is_required)
                                                <span class="badge badge-danger">{{translate('نعم')}}</span>
                                            @else
                                                <span class="badge badge-secondary">{{translate('لا')}}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($field->is_active)
                                                <span class="badge badge-success">{{translate('نشط')}}</span>
                                            @else
                                                <span class="badge badge-warning">{{translate('غير نشط')}}</span>
                                            @endif
                                        </td>
                                        <td>{{$field->sort_order}}</td>
                                        <td>
                                            <div class="d-flex justify-content-center gap-2">
                                                <a class="btn btn-outline-info btn-sm square-btn" title="{{translate('تعديل')}}"
                                                    href="{{route('admin.order-dynamic-fields.edit', [$field->id])}}">
                                                    <i class="tio-edit"></i>
                                                </a>
                                                <button class="btn btn-outline-danger btn-sm square-btn delete"
                                                    title="{{translate('حذف')}}"
                                                    onclick="form_alert('field-{{$field->id}}','{{translate('هل تريد حذف هذا الحقل؟')}}')">
                                                    <i class="tio-delete-outlined"></i>
                                                </button>
                                                <form action="{{route('admin.order-dynamic-fields.delete', [$field->id])}}"
                                                    method="post" id="field-{{$field->id}}">
                                                    @csrf @method('delete')
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5">
                                            <img src="{{asset('public/assets/admin/svg/illustrations/sorry.svg')}}"
                                                alt="{{translate('لا توجد بيانات')}}" class="mb-3" width="100">
                                            <p class="text-muted">{{translate('لم يتم إضافة حقول متغيرة بعد')}}</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
    <script>
        "use strict";

        // Show/hide options based on field type
        $('#field_type').on('change', function () {
            let fieldType = $(this).val();
            if (fieldType === 'select' || fieldType === 'radio' || fieldType === 'checkbox') {
                $('#options_container').show();
            } else {
                $('#options_container').hide();
            }
        });

        // Add more option inputs
        function addOption() {
            let optionHtml = `
                        <div class="input-group mb-2">
                            <input type="text" name="field_options[]" class="form-control" placeholder="{{translate('Option value')}}">
                            <button type="button" class="btn btn-outline-danger" onclick="$(this).closest('.input-group').remove()">
                                <i class="tio-delete"></i>
                            </button>
                        </div>
                    `;
            $('#options_list').append(optionHtml);
        }
    </script>
@endpush