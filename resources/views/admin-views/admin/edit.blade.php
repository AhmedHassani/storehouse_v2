@extends('layouts.admin.app')

@section('title', translate('تعديل بيانات الأدمن'))

@section('content')
<div class="content container-fluid">
    <div class="mb-3">
        <h2 class="text-capitalize mb-0 d-flex align-items-center gap-2">
            <i class="tio-user-edit"></i>
            {{translate('تعديل بيانات الأدمن')}}
        </h2>
    </div>

    <form action="{{route('admin.management.update', [$admin['id']])}}" method="post" enctype="multipart/form-data">
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
                            <input type="text" name="name" class="form-control" value="{{$admin->name}}" required>
                        </div>
                        <div class="form-group">
                            <label class="input-label">{{translate('اسم المستخدم (Username)')}}</label>
                            <input type="text" name="username" class="form-control" value="{{$admin->username}}"
                                required>
                        </div>
                        <div class="form-group">
                            <label class="input-label">{{translate('البريد الإلكتروني')}}</label>
                            <input type="email" name="email" class="form-control" value="{{$admin->email}}" required>
                        </div>
                        <div class="form-group">
                            <label class="input-label">{{translate('رقم الهاتف')}}</label>
                            <input type="text" name="phone" class="form-control" value="{{$admin->phone}}" required>
                        </div>
                        <div class="form-group">
                            <label class="input-label">{{translate('كلمة المرور')}} ({{translate('اختياري')}})</label>
                            <input type="password" name="password" class="form-control"
                                placeholder="{{translate('اتركه فارغاً إذا كنت لا تريد التغيير')}}">
                        </div>
                        <div class="form-group">
                            <label class="input-label">{{translate('الصورة الشخصية')}}</label>
                            <div class="custom-file mb-3">
                                <input type="file" name="image" id="customFileEg1" class="custom-file-input"
                                    accept=".jpg, .png, .jpeg|image/*">
                                <label class="custom-file-label" for="customFileEg1">{{translate('اختر ملف')}}</label>
                            </div>
                            <div class="text-center">
                                <img style="height: 150px; border: 1px solid; border-radius: 10px;" id="viewer"
                                    src="{{$admin->image_fullpath}}" alt="admin image" />
                            </div>
                        </div>
                        @if($admin->id != 1)
                            <div class="form-group">
                                <label
                                    class="toggle-switch toggle-switch-sm d-flex justify-content-between border border-secondary rounded px-4 form-control"
                                    for="statusSwitch">
                                    <span class="pr-2">{{translate('Status (Active)')}}</span>
                                    <input type="checkbox" class="toggle-switch-input" name="status" id="statusSwitch"
                                        value="1" {{$admin->status ? 'checked' : ''}}>
                                    <span class="toggle-switch-label">
                                        <span class="toggle-switch-indicator"></span>
                                    </span>
                                </label>
                            </div>
                        @else
                            <input type="hidden" name="status" value="1">
                        @endif
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
                        @php($admin_permissions = $admin->permissions->pluck('id')->toArray())
                        @foreach($permissions as $module => $modulePermissions)
                            <div class="mb-3 border-bottom pb-2">
                                <h5 class="mb-2 text-primary">{{$module}}</h5>
                                <div class="row">
                                    @foreach($modulePermissions as $permission)
                                        <div class="col-md-6 mb-2">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input permission-checkbox"
                                                    id="perm_{{$permission->id}}" name="permissions[]"
                                                    value="{{$permission->id}}" {{in_array($permission->id, $admin_permissions) ? 'checked' : ''}}>
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
            <button type="submit" class="btn btn-primary">{{translate('تحديث')}}</button>
        </div>
    </form>
</div>
@endsection

@push('script_2')
    <script>
        function readURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    $('#viewer').attr('src', e.target.result);
                }

                reader.readAsDataURL(input.files[0]);
            }
        }

        $("#customFileEg1").change(function () {
            readURL(this);
        });

        $("#select_all_permissions").click(function () {
            $(".permission-checkbox").prop('checked', $(this).prop('checked'));
        });
    </script>
@endpush