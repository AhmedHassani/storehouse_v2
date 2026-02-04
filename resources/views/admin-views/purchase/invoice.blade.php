@extends('layouts.admin.app')

@section('title', translate('فاتورة الشراء'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-3">
            <h2 class="text-capitalize mb-0 d-flex align-items-center gap-2">
                <i class="tio-invoice"></i>
                {{translate('تفاصيل الشراء')}} #{{$purchase->id}}
            </h2>
        </div>

        <div class="row">
            <div class="col-lg-8 mb-3">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title">{{translate('المنتجات')}}</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table
                                class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                                <thead class="thead-light">
                                    <tr>
                                        <th>{{translate('#')}}</th>
                                        <th>{{translate('اسم المنتج')}}</th>
                                        <th>{{translate('الكمية')}}</th>
                                        <th>{{translate('سعر الشراء')}}</th>
                                        <th>{{translate('المجموع')}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($purchase->details as $key => $detail)
                                        <tr>
                                            <td>{{$key + 1}}</td>
                                            <td>
                                                @if($detail->product)
                                                    {{$detail->product->name}}
                                                @else
                                                    <span class="text-danger">{{translate('تم حذف المنتج')}}</span>
                                                @endif
                                            </td>
                                            <td>{{$detail->quantity}}</td>
                                            <td>{{Helpers::set_symbol($detail->purchase_price)}}</td>
                                            <td>{{Helpers::set_symbol($detail->total_price)}}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="row justify-content-md-end mb-3 mt-4">
                            <div class="col-md-9 col-lg-8">
                                <dl class="row text-sm-right">
                                    <dt class="col-sm-6">{{translate('المبلغ الإجمالي')}}:</dt>
                                    <dd class="col-sm-6">{{Helpers::set_symbol($purchase->total_amount)}}</dd>

                                    <dt class="col-sm-6">{{translate('المدفوع')}}:</dt>
                                    <dd class="col-sm-6">{{Helpers::set_symbol($purchase->paid_amount)}}</dd>

                                    <dt class="col-sm-6">{{translate('المتبقي')}}:</dt>
                                    <dd class="col-sm-6">{{Helpers::set_symbol($purchase->due_amount)}}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 mb-3">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">{{translate('معلومات عامة')}}</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-dark font-weight-bold">{{translate('التاريخ')}}:</span>
                            <span class="text-dark">{{$purchase->created_at->format('d M, Y h:i A')}}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-dark font-weight-bold">{{translate('المورد')}}:</span>
                            <span class="text-dark">
                                @if($purchase->supplier)
                                    {{$purchase->supplier->name}}
                                @else
                                    <span class="text-danger">{{translate('تم الحذف')}}</span>
                                @endif
                            </span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-dark font-weight-bold">{{translate('الحالة')}}:</span>
                            @if($purchase->status == 'entered')
                                <span class="badge badge-soft-success">{{translate('تم الإدخال للمخزون')}}</span>
                            @else
                                <span class="badge badge-soft-warning">{{translate('لم يتم الإدخال')}}</span>
                            @endif
                        </div>
                        @if($purchase->image)
                            <hr>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="text-dark font-weight-bold">{{translate('الإيصال')}}:</span>
                            </div>
                            <div class="text-center">
                                <a href="{{asset('storage/app/public/purchase/' . $purchase->image)}}" target="_blank">
                                    <img src="{{asset('storage/app/public/purchase/' . $purchase->image)}}"
                                        class="img-fluid rounded" style="max-height: 200px" alt="receipt">
                                </a>
                            </div>
                        @endif
                        @if($purchase->notes)
                            <hr>
                            <div class="mb-3">
                                <span class="text-dark font-weight-bold">{{translate('ملاحظات')}}:</span>
                                <p class="text-dark">{{$purchase->notes}}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection