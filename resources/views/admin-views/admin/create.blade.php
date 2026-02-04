@extends('layouts.admin.app')

@section('title', translate('إضافة أدمن جديد'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-3">
            <h2 class="text-capitalize mb-0 d-flex align-items-center gap-2">
                <i class="tio-user-add"></i>
                {{translate('إضافة أدمن جديد')}}
            </h2>
        </div>

        <form action="{{route('admin.management.store')}}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title">{{translate('معلومات الأدمن')}}</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label class="input-label">{{translate('الاسم الكامل')}}</label>
                                <input type="text" name="name" class="form-control" placeholder="{{translate('الاسم')}}"
                                    required>
                            </div>
                            <div class="form-group">
                                <label class="input-label">{{translate('اسم المستخدم (Username)')}}</label>
                                <input type="text" name="username" class="form-control"
                                    placeholder="{{translate('اسم المستخدم')}}" required>
                            </div>
                            <div class="form-group">
                                <label class="input-label">{{translate('البريد الإلكتروني')}}</label>
                                <input type="email" name="email" class="form-control"
                                    placeholder="{{translate('email@example.com')}}" required>
                            </div>
                            <div class="form-group">
                                <label class="input-label">{{translate('رقم الهاتف')}}</label>
                                <input type="text" name="phone" class="form-control"
                                    placeholder="{{translate('رقم الهاتف')}}" required>
                            </div>
                            <div class="form-group">
                                <label class="input-label">{{translate('كلمة المرور')}}</label>
                                <input type="password" name="password" class="form-control"
                                    placeholder="{{translate('كلمة المرور')}}" required>
                            </div>
                            <div class="form-group">
                                <label class="input-label">{{translate('الصورة الشخصية')}}</label>
                                <div class="custom-file">
                                    <input type="file" name="image" id="customFileEg1" class="custom-file-input"
                                        accept=".jpg, .png, .jpeg|image/*">
                                    <label class="custom-file-label" for="customFileEg1">{{translate('اختر ملف')}}</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <label
                                    class="toggle-switch toggle-switch-sm d-flex justify-content-between border border-secondary rounded px-4 form-control"
                                    for="statusSwitch">
                                    <span class="pr-2">{{translate('Status (Active)')}}</span>
                                    <input type="checkbox" class="toggle-switch-input" name="status" id="statusSwitch"
                                        value="1" checked>
                                    <span class="toggle-switch-label">
                                        <span class="toggle-switch-indicator"></span>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <div class="card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title">{{translate('الصلاحيات')}}</h5>
                            <div>
                                <input type="checkbox" id="select_all_permissions"> <label
                                    for="select_all_permissions">{{translate('تحديد الكل')}}</label>
                            </div>
                        </div>
                        <div class="card-body">
                            @foreach($permissions as $module => $modulePermissions)
                                <div class="mb-3 border-bottom pb-2">
                                    <h5 class="mb-2 text-primary">{{$module}}</h5>
                                    <div class="row">
                                        @foreach($modulePermissions as $permission)
                                            <div class="col-md-6 mb-2">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input permission-checkbox"
                                                        id="perm_{{$permission->id}}" name="permissions[]"
                                                        value="{{$permission->id}}">
                                                    <label class="custom-control-label" for="perm_{{$permission->id}}">
                                                        {{$permission->name}}
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-right">
                <button type="submit" class="btn btn-primary">{{translate('حفظ')}}</button>
            </div>
        </form>
    </div>
@endsection

@push('script_2')
    <script>
        $("#select_all_permissions").click(function () {
            $(".permission-checkbox").prop('checked', $(this).prop('checked'));
        });
    </script>
@endpush