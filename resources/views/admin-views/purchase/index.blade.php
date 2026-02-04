@extends('layouts.admin.app')

@section('title', translate('قائمة المشتريات'))

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('public/assets/admin/img/bill.png')}}" class="w--26" alt="">
                </span>
                <span>
                    {{translate('قائمة المشتريات')}}
                    <span class="badge badge-soft-dark radius-50 fz-12">{{ $purchases->total() }}</span>
                </span>
            </h1>
        </div>
        <!-- End Page Header -->

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header border-0 py-2">
                        <div class="d-flex flex-wrap gap-2 justify-content-between w-100">
                            <h5 class="card-title d-flex align-items-center gap-2">
                                {{translate('كل المشتريات')}}
                            </h5>

                            <div class="d-flex flex-wrap gap-2">
                                <form action="{{url()->current()}}" method="GET">
                                    <div class="input-group input-group-merge input-group-custom">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text">
                                                <i class="tio-search"></i>
                                            </div>
                                        </div>
                                        <input id="datatableSearch_" type="search" name="search" class="form-control"
                                            placeholder="{{translate('بحث برقم الفاتورة أو المورد')}}" aria-label="Search"
                                            value="{{ $search }}" autocomplete="off">
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-primary">{{translate('بحث')}}</button>
                                        </div>
                                    </div>
                                </form>

                                <a href="{{route('admin.purchase.create')}}" class="btn btn-primary align-items-center">
                                    <i class="tio-add"></i>
                                    {{translate('إضافة شراء جديد')}}
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive datatable-custom">
                        <table
                            class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>{{translate('#')}}</th>
                                    <th>{{translate('مرجع')}}</th>
                                    <th>{{translate('المورد')}}</th>
                                    <th>{{translate('المبلغ الإجمالي')}}</th>
                                    <th>{{translate('المدفوع')}}</th>
                                    <th>{{translate('المتبقي')}}</th>
                                    <th>{{translate('الحالة')}}</th>
                                    <th>{{translate('التاريخ')}}</th>
                                    <th class="text-center">{{translate('إجراء')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchases as $key => $purchase)
                                    <tr>
                                        <td>{{$purchases->firstitem() + $key}}</td>
                                        <td>
                                            <a class="text-body text-hover-primary"
                                                href="{{route('admin.purchase.invoice', [$purchase['id']])}}">
                                                <span class="d-block font-size-sm text-body">
                                                    #{{$purchase['id']}}
                                                </span>
                                            </a>
                                        </td>
                                        <td>
                                            @if($purchase->supplier)
                                                <a href="javascript:"
                                                    class="text-body text-hover-primary">{{$purchase->supplier->name}}</a>
                                            @else
                                                <span class="text-danger">{{translate('تم حذف المورد')}}</span>
                                            @endif
                                        </td>
                                        <td>{{Helpers::set_symbol($purchase['total_amount'])}}</td>
                                        <td>{{Helpers::set_symbol($purchase['paid_amount'])}}</td>
                                        <td>{{Helpers::set_symbol($purchase['due_amount'])}}</td>
                                        <td>
                                            @if($purchase['status'] == 'entered')
                                                <span class="badge badge-soft-success">{{translate('تم الإدخال للمخزون')}}</span>
                                            @else
                                                <span class="badge badge-soft-warning">{{translate('لم يتم الإدخال')}}</span>
                                            @endif
                                        </td>
                                        <td>{{$purchase['created_at']->format('d M, Y')}}</td>
                                        <td>
                                            <div class="d-flex justify-content-center gap-2">
                                                <a class="btn btn-outline-info square-btn btn-sm"
                                                    href="{{route('admin.purchase.invoice', [$purchase['id']])}}"
                                                    title="{{translate('عرض الفاتورة')}}">
                                                    <i class="tio-invisible"></i>
                                                </a>
                                                <a class="btn btn-outline-primary square-btn btn-sm"
                                                    href="{{route('admin.purchase.edit', [$purchase['id']])}}"
                                                    title="{{translate('تعديل')}}">
                                                    <i class="tio-edit"></i>
                                                </a>
                                                <a class="btn btn-outline-danger square-btn btn-sm form-alert"
                                                    href="javascript:" data-id="purchase-{{$purchase['id']}}"
                                                    data-message="{{translate('هل تريد حذف هذا الشراء ؟')}}">
                                                    <i class="tio-delete"></i>
                                                </a>
                                            </div>
                                            <form action="{{route('admin.purchase.delete', [$purchase['id']])}}" method="post"
                                                id="purchase-{{$purchase['id']}}">
                                                @csrf @method('delete')
                                                <input type="hidden" name="id" value="{{$purchase['id']}}">
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="table-responsive mt-4 px-3">
                        <div class="d-flex justify-content-end">
                            {!! $purchases->links() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection