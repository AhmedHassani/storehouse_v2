@extends('layouts.admin.app')

@section('title', translate('إضافة مورد جديد'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-3">
            <h2 class="text-capitalize mb-0 d-flex align-items-center gap-2">
                <i class="tio-user-big"></i>
                {{translate('إعدادات الموردين')}}
            </h2>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{route('admin.supplier.store')}}" method="post">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="input-label" for="name">{{translate('الاسم')}} <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control"
                                    placeholder="{{ translate('اسم المورد') }}" required maxlength="255">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="input-label" for="phone">{{translate('الهاتف')}} <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="phone" class="form-control"
                                    placeholder="{{ translate('رقم الهاتف') }}" required maxlength="20">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="input-label" for="address">{{translate('العنوان')}}</label>
                                <input type="text" name="address" class="form-control"
                                    placeholder="{{ translate('العنوان') }}" maxlength="255">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="input-label" for="notes">{{translate('ملاحظات')}}</label>
                                <textarea name="notes" class="form-control" placeholder="{{ translate('ملاحظات') }}"
                                    rows="1"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-3">
                        <button type="reset" class="btn btn-secondary">{{translate('إعادة تعيين')}}</button>
                        <button type="submit" class="btn btn-primary">{{translate('إرسال')}}</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mt-3">
            <div class="px-20 py-3">
                <div class="row gy-2 align-items-center">
                    <div class="col-lg-8 col-sm-4 col-md-6">
                        <h5 class="text-capitalize d-flex align-items-center gap-2 mb-0">
                            {{translate('قائمة الموردين')}}
                            <span class="badge badge-soft-dark rounded-50 fz-12">{{ $suppliers->total() }}</span>
                        </h5>
                    </div>
                    <div class="col-lg-4 col-sm-8 col-md-6">
                        <form action="{{url()->current()}}" method="GET">
                            <div class="input-group">
                                <input id="datatableSearch_" type="search" name="search" class="form-control"
                                    placeholder="{{translate('بحث بالاسم')}}" aria-label="Search"
                                    value="{{request('search')}}" autocomplete="off">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-primary">{{translate('بحث')}}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="table-responsive datatable-custom">
                <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                    <thead class="thead-light">
                        <tr>
                            <th>{{translate('#')}}</th>
                            <th>{{translate('الاسم')}}</th>
                            <th>{{translate('الهاتف')}}</th>
                            <th>{{translate('العنوان')}}</th>
                            <th class="text-center">{{translate('إجراء')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($suppliers as $key => $supplier)
                            <tr>
                                <td>{{$suppliers->firstitem() + $key}}</td>
                                <td>{{$supplier['name']}}</td>
                                <td>{{$supplier['phone']}}</td>
                                <td>{{$supplier['address']}}</td>
                                <td>
                                    <div class="d-flex justify-content-center gap-2">
                                        <a class="btn btn-outline-info square-btn"
                                            href="{{route('admin.supplier.edit', [$supplier['id']])}}"><i
                                                class="tio tio-edit"></i></a>
                                        <a class="btn btn-outline-danger square-btn form-alert" href="javascript:"
                                            data-id="supplier-{{$supplier['id']}}"
                                            data-message="{{translate('هل أنت متأكد من حذف هذا المورد ؟')}}">
                                            <i class="tio tio-delete"></i>
                                        </a>
                                    </div>
                                    <form action="{{route('admin.supplier.delete', [$supplier['id']])}}" method="post"
                                        id="supplier-{{$supplier['id']}}">
                                        @csrf @method('delete')
                                        <input type="hidden" name="id" value="{{$supplier['id']}}">
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="table-responsive mt-4 px-3">
                <div class="d-flex justify-content-end">
                    {!! $suppliers->links() !!}
                </div>
            </div>
        </div>
    </div>
@endsection