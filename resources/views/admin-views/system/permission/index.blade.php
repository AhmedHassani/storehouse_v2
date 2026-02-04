@extends('layouts.admin.app')

@section('title', translate('إدارة الصلاحيات'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-3">
            <h2 class="text-capitalize mb-0 d-flex align-items-center gap-2">
                <i class="tio-security"></i>
                {{translate('إدارة الصلاحيات')}}
            </h2>
        </div>

        <!-- Add Permission Form -->
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="card-title">{{translate('إضافة صلاحية جديدة')}}</h5>
            </div>
            <div class="card-body">
                <form action="{{route('admin.permission.store')}}" method="post">
                    @csrf
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="input-label">{{translate('اسم الصلاحية')}}</label>
                                <input type="text" name="name" class="form-control"
                                    placeholder="{{translate('مثال: إضافة مشتريات')}}" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="input-label">{{translate('كود الصلاحية')}} (Key)</label>
                                <input type="text" name="key" class="form-control"
                                    placeholder="{{translate('مثال: purchase.create')}}" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="input-label">{{translate('القسم (Module)')}}</label>
                                <select name="module" class="form-control" required>
                                    <option value="Dashboard">{{translate('Dashboard')}}</option>
                                    <option value="POS">{{translate('POS')}}</option>
                                    <option value="Order">{{translate('Order')}}</option>
                                    <option value="Product">{{translate('Product')}}</option>
                                    <option value="Category">{{translate('Category')}}</option>
                                    <option value="SubCategory">{{translate('SubCategory')}}</option>
                                    <option value="Brand">{{translate('Brand')}}</option>
                                    <option value="Attribute">{{translate('Attribute')}}</option>
                                    <option value="Customer">{{translate('Customer')}}</option>
                                    <option value="Supplier">{{translate('Supplier')}}</option>
                                    <option value="Purchase">{{translate('Purchase')}}</option>
                                    <option value="SupplierPayment">{{translate('SupplierPayment')}}</option>
                                    <option value="Employee">{{translate('Employee')}}</option>
                                    <option value="Permission">{{translate('Permission')}}</option>
                                    <option value="Report">{{translate('Report')}}</option>
                                    <option value="Settings">{{translate('Settings')}}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="input-label">{{translate('وصف')}}</label>
                                <textarea name="description" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">{{translate('إضافة')}}</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Permissions Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">{{translate('قائمة الصلاحيات')}} <span
                        class="badge badge-soft-dark ml-2">{{count($permissions)}}</span></h5>
            </div>
            <div class="table-responsive datatable-custom">
                <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                    <thead class="thead-light">
                        <tr>
                            <th>{{translate('#')}}</th>
                            <th>{{translate('الاسم')}}</th>
                            <th>{{translate('Key')}}</th>
                            <th>{{translate('Module')}}</th>
                            <th>{{translate('الحالة')}}</th>
                            <th class="text-center">{{translate('إجراء')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($permissions as $key => $permission)
                            <tr>
                                <td>{{$key + 1}}</td>
                                <td>{{$permission->name}} <br> <small>{{$permission->description}}</small></td>
                                <td><code>{{$permission->key}}</code></td>
                                <td><span class="badge badge-soft-info">{{$permission->module}}</span></td>
                                <td>
                                    <label class="toggle-switch toggle-switch-sm">
                                        <input type="checkbox" class="toggle-switch-input"
                                            onclick="status_change_alert('{{route('admin.permission.status', [$permission->id, $permission->status ? 0 : 1])}}', '{{translate('هل تريد تغيير الحالة؟')}}', event)"
                                            {{$permission->status ? 'checked' : ''}}>
                                        <span class="toggle-switch-label">
                                            <span class="toggle-switch-indicator"></span>
                                        </span>
                                    </label>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center gap-2">
                                        <a class="btn btn-outline-danger square-btn form-alert" href="javascript:"
                                            data-id="permission-{{$permission['id']}}"
                                            data-message="{{translate('هل تريد حذف هذه الصلاحية؟')}}">
                                            <i class="tio-delete"></i>
                                        </a>
                                        <form action="{{route('admin.permission.delete', [$permission['id']])}}" method="post"
                                            id="permission-{{$permission['id']}}">
                                            @csrf @method('delete')
                                        </form>
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