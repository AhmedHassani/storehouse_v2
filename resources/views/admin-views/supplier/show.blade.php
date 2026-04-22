@extends('layouts.admin.app')

@section('title', translate('تفاصيل المورد'))

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="mb-3">
            <div class="d-flex align-items-center justify-content-between">
                <h2 class="text-capitalize mb-0 d-flex align-items-center gap-2">
                    <i class="tio-user-big"></i>
                    {{translate('تفاصيل المورد:')}} {{ $supplier->name }}
                </h2>
                <a href="{{ route('admin.supplier.add-new') }}" class="btn btn-secondary">
                    <i class="tio-chevron-right"></i>
                    {{translate('العودة للقائمة')}}
                </a>
            </div>
        </div>

        <!-- Supplier Info Card -->
        <div class="card mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <p class="mb-2"><strong>{{translate('الاسم')}}:</strong> {{ $supplier->name }}</p>
                    </div>
                    <div class="col-md-3">
                        <p class="mb-2"><strong>{{translate('الهاتف')}}:</strong> {{ $supplier->phone }}</p>
                    </div>
                    <div class="col-md-3">
                        <p class="mb-2"><strong>{{translate('العنوان')}}:</strong> {{ $supplier->address ?? '-' }}</p>
                    </div>
                    <div class="col-md-3">
                        <p class="mb-2"><strong>{{translate('ملاحظات')}}:</strong> {{ $supplier->notes ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Date Filter Card -->
        <div class="card mb-3">
            <div class="card-body">
                <form action="{{ route('admin.supplier.show', $supplier->id) }}" method="GET">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <div class="form-group mb-0">
                                <label class="input-label">{{translate('من تاريخ')}}</label>
                                <input type="date" name="from_date" class="form-control" value="{{ $from_date ?? '' }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-0">
                                <label class="input-label">{{translate('إلى تاريخ')}}</label>
                                <input type="date" name="to_date" class="form-control" value="{{ $to_date ?? '' }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="tio-filter-outlined"></i> {{translate('تصفية')}}
                            </button>
                            <a href="{{ route('admin.supplier.show', $supplier->id) }}" class="btn btn-secondary">
                                <i class="tio-clear"></i> {{translate('إعادة تعيين')}}
                            </a>
                        </div>
                        <div class="col-md-3 text-right">
                            <a href="{{ route('admin.supplier.export-timeline', $supplier->id) }}?from_date={{ $from_date ?? '' }}&to_date={{ $to_date ?? '' }}"
                                class="btn btn-success">
                                <i class="tio-download"></i> {{translate('تصدير Excel')}}
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-3">
            <div class="col-sm-6 col-lg-3 mb-3 mb-lg-0">
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

            <div class="col-sm-6 col-lg-3 mb-3 mb-lg-0">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <div class="media">
                                    <i class="tio-money nav-icon text-info font-size-lg mr-3"></i>
                                    <div class="media-body">
                                        <span class="d-block font-size-md">{{translate('مدفوع عند الشراء')}}</span>
                                        <h3 class="mb-0">{{Helpers::set_symbol($paid_on_purchase)}}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3 mb-3 mb-lg-0">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <div class="media">
                                    <i class="tio-money-vs nav-icon text-success font-size-lg mr-3"></i>
                                    <div class="media-body">
                                        <span class="d-block font-size-md">{{translate('دفعات إضافية')}}</span>
                                        <h3 class="mb-0">{{Helpers::set_symbol($paid_via_portal)}}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3 mb-3 mb-lg-0">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <div class="media">
                                    <i class="tio-wallet-outlined nav-icon text-danger font-size-lg mr-3"></i>
                                    <div class="media-body">
                                        <span class="d-block font-size-md">{{translate('المتبقي')}}</span>
                                        <h3 class="mb-0">{{Helpers::set_symbol($balance_due)}}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Timeline Card -->
        <div class="card">
            <div class="card-header border-0 py-2">
                <div class="search--button-wrapper">
                    <h5 class="card-title">{{translate('سجل الدفعات والمشتريات')}}</h5>
                    <div>
                        <span class="badge badge-soft-dark">{{ $timelinePaginated->total() }} {{translate('عملية')}}</span>
                    </div>
                </div>
            </div>

            <div class="table-responsive datatable-custom">
                <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                    <thead class="thead-light">
                        <tr>
                            <th>{{translate('#')}}</th>
                            <th>{{translate('النوع')}}</th>
                            <th>{{translate('التاريخ')}}</th>
                            <th>{{translate('المبلغ الإجمالي')}}</th>
                            <th>{{translate('المبلغ المدفوع')}}</th>
                            <th>{{translate('الملاحظات')}}</th>
                            <th class="text-center">{{translate('إجراء')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($timelinePaginated as $key => $item)
                            @if($item['type'] == 'purchase')
                                <tr class="bg-light-green">
                                    <td>{{ $timelinePaginated->firstItem() + $key }}</td>
                                    <td>
                                        <span class="badge badge-soft-primary">
                                            <i class="tio-shopping-cart"></i> {{translate('مشتريات')}}
                                        </span>
                                    </td>
                                    <td>{{ $item['data']->created_at->format('Y-m-d H:i') }}</td>
                                    <td><strong class="text-primary">{{ Helpers::set_symbol($item['data']->total_amount) }}</strong>
                                    </td>
                                    <td><span class="text-success">{{ Helpers::set_symbol($item['data']->paid_amount) }}</span></td>
                                    <td>{{ Str::limit($item['data']->notes ?? '-', 40) }}</td>
                                    <td class="text-center">
                                        @if($item['data']->image)
                                            <a href="{{asset('storage/purchase/' . $item['data']->image)}}" target="_blank"
                                                class="btn btn-sm btn-outline-info">
                                                <i class="tio-image"></i>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @else
                                <tr class="bg-light-blue">
                                    <td>{{ $timelinePaginated->firstItem() + $key }}</td>
                                    <td>
                                        <span class="badge badge-soft-success">
                                            <i class="tio-money"></i> {{translate('دفعة')}}
                                        </span>
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($item['data']->payment_date)->format('Y-m-d') }}</td>
                                    <td>-</td>
                                    <td><strong class="text-success">{{ Helpers::set_symbol($item['data']->amount) }}</strong></td>
                                    <td>{{ Str::limit($item['data']->notes ?? '-', 40) }}</td>
                                    <td class="text-center">
                                        @if($item['data']->image)
                                            <a href="{{asset('storage/supplier_payment/' . $item['data']->image)}}"
                                                target="_blank" class="btn btn-sm btn-outline-info">
                                                <i class="tio-image"></i>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="7" class="text-center p-4">
                                    <img class="mb-3 width-7rem"
                                        src="{{asset('assets/admin/svg/illustrations/sorry.svg')}}"
                                        alt="{{ translate('Image Description') }}">
                                    <p class="mb-0">{{ translate('لا توجد بيانات للعرض') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="table-responsive mt-4 px-3">
                <div class="d-flex justify-content-end">
                    {!! $timelinePaginated->appends(['from_date' => $from_date, 'to_date' => $to_date])->links() !!}
                </div>
            </div>
        </div>
    </div>

    @push('script')
        <style>
            .bg-light-green {
                background-color: #f0f9ff !important;
            }

            .bg-light-blue {
                background-color: #f0fff4 !important;
            }
        </style>
    @endpush
@endsection