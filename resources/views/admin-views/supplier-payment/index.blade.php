@extends('layouts.admin.app')

@section('title', translate('مدفوعات الموردين'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-3">
            <h2 class="text-capitalize mb-0 d-flex align-items-center gap-2">
                <i class="tio-money-vs"></i>
                {{translate('مدفوعات الموردين والتسوية')}}
            </h2>
        </div>

        <!-- Filter / Supplier Selection -->
        <div class="card mb-3">
            <div class="card-body">
                <form action="{{route('admin.supplier-payment.index')}}" method="GET">
                    <div class="row align-items-end">
                        <div class="col-md-4">
                            <div class="form-group mb-0">
                                <label class="input-label">{{translate('اختر المورد')}}</label>
                                <select name="supplier_id" class="form-control js-select2-custom"
                                    onchange="this.form.submit()">
                                    <option value="">{{translate('كل الموردين')}}</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{$supplier->id}}" {{$supplier_id == $supplier->id ? 'selected' : ''}}>
                                            {{$supplier->name}}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <!-- Potentially date range filter later -->
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if($supplier_id)
            <!-- Summary Widgets -->
            <div class="row mb-3">
                <div class="col-sm-6 col-lg-4 mb-3 mb-lg-0">
                    <div class="card card-sm">
                        <div class="card-body">
                            <div class="row">
                                <div class="col">
                                    <div class="media">
                                        <i class="tio-shopping-cart-outlined nav-icon text-primary font-size-lg mr-3"></i>
                                        <div class="media-body">
                                            <span class="d-block font-size-md">{{translate('إجمالي المشتريات')}}</span>
                                            <h3 class="mb-0">{{Helpers::set_symbol($total_purchase)}}</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-lg-4 mb-3 mb-lg-0">
                    <div class="card card-sm">
                        <div class="card-body">
                            <div class="row">
                                <div class="col">
                                    <div class="media">
                                        <i class="tio-money-vs nav-icon text-success font-size-lg mr-3"></i>
                                        <div class="media-body">
                                            <span class="d-block font-size-md">{{translate('إجمالي المدفوع')}}</span>
                                            <h3 class="mb-0">{{Helpers::set_symbol($total_paid_all)}}</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-lg-4 mb-3 mb-lg-0">
                    <div class="card card-sm">
                        <div class="card-body">
                            <div class="row">
                                <div class="col">
                                    <div class="media">
                                        <i class="tio-wallet-outlined nav-icon text-danger font-size-lg mr-3"></i>
                                        <div class="media-body">
                                            <span class="d-block font-size-md">{{translate('الرصيد المتبقي')}}</span>
                                            <h3 class="mb-0">{{Helpers::set_symbol($balance_due)}}</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="card">
            <div class="card-header border-0 py-2">
                <div class="search--button-wrapper">
                    <h5 class="card-title">{{translate('سجل الدفعات')}}</h5>
                    <div>
                        @if($supplier_id)
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addPaymentModal">
                                <i class="tio-add"></i> {{translate('إضافة دفعة')}}
                            </button>
                        @else
                            <button type="button" class="btn btn-secondary" disabled title="{{translate('اختر مورد أولاً')}}">
                                <i class="tio-add"></i> {{translate('إضافة دفعة')}}
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <div class="table-responsive datatable-custom">
                <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                    <thead class="thead-light">
                        <tr>
                            <th>{{translate('#')}}</th>
                            <th>{{translate('التاريخ')}}</th>
                            <th>{{translate('المورد')}}</th>
                            <th>{{translate('المبلغ')}}</th>
                            <th>{{translate('ملاحظات')}}</th>
                            <th>{{translate('الصورة')}}</th>
                            <th class="text-center">{{translate('إجراء')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payments as $key => $payment)
                            <tr>
                                <td>{{$payments->firstitem() + $key}}</td>
                                <td>{{$payment['payment_date']}}</td>
                                <td>{{$payment->supplier->name ?? translate('غير معروف')}}</td>
                                <td>{{Helpers::set_symbol($payment['amount'])}}</td>
                                <td>{{Str::limit($payment['notes'], 50)}}</td>
                                <td>
                                    @if($payment['image'])
                                        <a href="{{asset('storage/app/public/supplier_payment/' . $payment['image'])}}"
                                            target="_blank">
                                            <img src="{{asset('storage/app/public/supplier_payment/' . $payment['image'])}}"
                                                width="40" height="40" class="rounded" alt="">
                                        </a>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center gap-2">
                                        <a class="btn btn-outline-danger square-btn form-alert" href="javascript:"
                                            data-id="payment-{{$payment['id']}}"
                                            data-message="{{translate('هل أنت متأكد من حذف هذه الدفعة ؟')}}">
                                            <i class="tio-delete"></i>
                                        </a>
                                    </div>
                                    <form action="{{route('admin.supplier-payment.delete', [$payment['id']])}}" method="post"
                                        id="payment-{{$payment['id']}}">
                                        @csrf @method('delete')
                                        <input type="hidden" name="id" value="{{$payment['id']}}">
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="table-responsive mt-4 px-3">
                <div class="d-flex justify-content-end">
                    {!! $payments->appends(['supplier_id' => $supplier_id])->links() !!}
                </div>
            </div>

            @if(count($payments) == 0)
                <div class="text-center p-4">
                    <img class="mb-3 width-7rem" src="{{asset('public/assets/admin/svg/illustrations/sorry.svg')}}"
                        alt="{{ translate('Image Description') }}">
                    <p class="mb-0">{{ translate('لا توجد بيانات للعرض') }}</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Add Payment Modal -->
    <div class="modal fade" id="addPaymentModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{translate('إضافة دفعة')}}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{route('admin.supplier-payment.store')}}" method="post" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="supplier_id" value="{{$supplier_id}}">
                        <div class="form-group">
                            <label>{{translate('تاريخ الدفع')}}</label>
                            <input type="date" name="payment_date" class="form-control" value="{{date('Y-m-d')}}" required>
                        </div>
                        <div class="form-group">
                            <label>{{translate('المبلغ')}}</label>
                            <input type="number" step="0.01" min="0.01" name="amount" class="form-control" required
                                placeholder="0.00">
                        </div>
                        <div class="form-group">
                            <label>{{translate('صورة الإيصال / الدفع')}}</label>
                            <div class="custom-file">
                                <input type="file" name="image" class="custom-file-input"
                                    accept=".jpg, .png, .jpeg|image/*">
                                <label class="custom-file-label">{{translate('اختر ملف')}}</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>{{translate('ملاحظات')}}</label>
                            <textarea name="notes" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-secondary"
                                data-dismiss="modal">{{translate('إغلاق')}}</button>
                            <button type="submit" class="btn btn-primary">{{translate('حفظ الدفعة')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection