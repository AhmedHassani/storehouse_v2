@extends('layouts.admin.app')

@section('title', translate('قائمة الأدمن'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-3">
            <h2 class="text-capitalize mb-0 d-flex align-items-center gap-2">
                <i class="tio-user-big"></i>
                {{translate('إدارة الأدمن')}}
            </h2>
        </div>

        <!-- Search/Header -->
        <div class="card mb-3">
            <div class="card-header border-0 py-2">
                <div class="search--button-wrapper">
                    <h5 class="card-title">{{translate('قائمة الأدمن')}} <span
                            class="badge badge-soft-dark ml-2">{{count($admins)}}</span></h5>
                    <div>
                        <a href="{{route('admin.management.create')}}" class="btn btn-primary">
                            <i class="tio-add"></i> {{translate('إضافة أدمن جديد')}}
                        </a>
                    </div>
                </div>
            </div>
            <div class="table-responsive datatable-custom">
                <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                    <thead class="thead-light">
                        <tr>
                            <th>{{translate('#')}}</th>
                            <th>{{translate('الاسم')}}</th>
                            <th>{{translate('Username')}}</th>
                            <th>{{translate('معلومات الاتصال')}}</th>
                            <th>{{translate('الصلاحيات')}}</th>
                            <th>{{translate('الحالة')}}</th>
                            <th class="text-center">{{translate('إجراء')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($admins as $key => $admin)
                            <tr>
                                <td>{{$key + 1}}</td>
                                <td>
                                    <div class="media align-items-center gap-3">
                                        <div class="avatar avatar-circle">
                                            <img class="avatar-img"
                                                onerror="this.src='{{asset('public/assets/admin/img/160x160/img1.jpg')}}'"
                                                src="{{$admin->image_fullpath}}" alt="Image Description">
                                        </div>
                                        <div class="media-body">
                                            <h5 class="text-hover-primary mb-0">{{$admin->name}}</h5>
                                            <span class="text-body font-size-sm">{{$admin->email}}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>{{$admin->username}}</td>
                                <td>{{$admin->phone}}</td>
                                <td>
                                    <span class="badge badge-soft-info">{{$admin->permissions->count()}}
                                        {{translate('صلاحيات')}}</span>
                                </td>
                                <td>
                                    @if($admin->id != 1)
                                        <label class="toggle-switch toggle-switch-sm">
                                            <input type="checkbox" class="toggle-switch-input"
                                                onclick="status_change_alert('{{route('admin.management.status', [$admin->id, $admin->status ? 0 : 1])}}', '{{translate('هل تريد تغيير الحالة؟')}}', event)"
                                                {{$admin->status ? 'checked' : ''}}>
                                            <span class="toggle-switch-label">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                    @else
                                        <span class="badge badge-soft-success">{{translate('نشط دائماً')}}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center gap-2">
                                        <a class="btn btn-outline-primary square-btn"
                                            href="{{route('admin.management.edit', [$admin['id']])}}">
                                            <i class="tio-edit"></i>
                                        </a>
                                        @if($admin->id != 1)
                                            <a class="btn btn-outline-danger square-btn form-alert" href="javascript:"
                                                data-id="admin-{{$admin['id']}}"
                                                data-message="{{translate('هل تريد حذف هذا الأدمن؟')}}">
                                                <i class="tio-delete"></i>
                                            </a>
                                            <form action="{{route('admin.management.delete', [$admin['id']])}}" method="post"
                                                id="admin-{{$admin['id']}}">
                                                @csrf @method('delete')
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection