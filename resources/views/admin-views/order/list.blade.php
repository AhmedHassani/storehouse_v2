@extends('layouts.admin.app')

@section('title', translate('Order List'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex flex-wrap gap-3 align-items-center mb-3">
            <h2 class="text-capitalize mb-0 d-flex align-items-center gap-2">
                <img src="{{asset('public/assets/admin/img/icons/all_orders.png')}}"
                    alt="{{ translate('orders') }}">{{translate('all_orders')}}
            </h2>
            <span class="badge badge-soft-dark rounded-50 fs-14">{{$orders->total()}}</span>
        </div>

        <div class="card">
            <div class="card mb-4">
                <div class="card-body">
                    <form action="#" id="form-data" method="GET">
                        <div class="row align-items-end gy-3 gx-2">
                            <div class="col-12 pb-0">
                                <h4>{{translate('Select_Date_Range')}}</h4>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <label for="filter">{{translate('Select_Orders')}}</label>
                                <select class="custom-select custom-select-sm text-capitalize min-h-45px" name="branch_id">
                                    <option disabled>--- {{translate('select')}} {{translate('branch')}} ---</option>
                                    <option value="all" {{ $branchId == 'all' ? 'selected' : ''}}>{{translate('all')}}
                                        {{translate('branch')}}
                                    </option>
                                    @foreach($branches as $branch)
                                        <option value="{{$branch['id']}}" {{ $branch['id'] == $branchId ? 'selected' : ''}}>
                                            {{$branch['name']}}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <div>
                                    <label for="form_date">{{translate('Start_Date')}}</label>
                                    <input type="date" id="start_date" name="start_date" value="{{$startDate}}"
                                        class="js-flatpickr form-control flatpickr-custom min-h-40px" placeholder="yy-mm-dd"
                                        data-hs-flatpickr-options='{ "dateFormat": "Y-m-d"}'>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-3 mt-2 mt-sm-0">
                                <div>
                                    <label for="to_date">{{translate('End_date')}}</label>
                                    <input type="date" id="end_date" name="end_date" value="{{$endDate}}"
                                        class="js-flatpickr form-control flatpickr-custom min-h-40px" placeholder="yy-mm-dd"
                                        data-hs-flatpickr-options='{ "dateFormat": "Y-m-d"}'>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-3 mt-2 mt-sm-0 __btn-row">
                                <a href="{{ route('admin.orders.list', [$status]) }}" id=""
                                    class="btn w-100 btn--reset min-h-45px">{{translate('clear')}}</a>
                                <button type="submit" class="btn btn-primary btn-block">{{translate('Show_Data')}}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="p-3">
                <div class="row justify-content-between align-items-center gy-2">
                    <div class="col-sm-8 col-md-6 col-lg-4">
                        <form action="{{url()->current()}}" method="GET">
                            <div class="input-group">
                                <input id="datatableSearch_" type="search" name="search" class="form-control"
                                    placeholder="{{translate('Search by order ID')}}" aria-label="Search"
                                    value="{{$search}}" required autocomplete="off">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-primary">{{translate('search')}}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="col-sm-4 col-md-6 col-lg-8 d-flex justify-content-end gap-2">
                        <div class="dropdown mr-2">
                            <button type="button" class="btn btn-outline-secondary" data-toggle="dropdown"
                                aria-expanded="false">
                                <i class="tio-settings"></i> {{ translate('bulk_action') }} <i class="tio-chevron-down"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right">
                                <button type="button" class="dropdown-item" onclick="submitBulkStatus('pending')">
                                    {{ translate('pending') }}
                                </button>
                                <button type="button" class="dropdown-item" onclick="submitBulkStatus('confirmed')">
                                    {{ translate('confirmed') }}
                                </button>
                                <button type="button" class="dropdown-item" onclick="submitBulkStatus('processing')">
                                    {{ translate('processing') }}
                                </button>
                                <button type="button" class="dropdown-item" onclick="submitBulkStatus('out_for_delivery')">
                                    {{ translate('out_for_delivery') }}
                                </button>
                                <button type="button" class="dropdown-item" onclick="submitBulkStatus('delivered')">
                                    {{ translate('delivered') }}
                                </button>
                                <button type="button" class="dropdown-item" onclick="submitBulkStatus('returned')">
                                    {{ translate('returned') }}
                                </button>
                                <button type="button" class="dropdown-item" onclick="submitBulkStatus('failed')">
                                    {{ translate('failed') }}
                                </button>
                                <button type="button" class="dropdown-item" onclick="submitBulkStatus('canceled')">
                                    {{ translate('canceled') }}
                                </button>
                            </div>
                        </div>
                        <div>
                            <button type="button" class="btn btn-outline-primary" data-toggle="dropdown"
                                aria-expanded="false">
                                <i class="tio-download-to"></i>{{ translate('Export') }}<i class="tio-chevron-down"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-right w-auto">
                                <li>
                                    <a type="submit" class="dropdown-item d-flex align-items-center gap-2"
                                        href="{{route('admin.orders.export', [$status, 'branch_id' => Request::get('branch_id'), 'start_date' => Request::get('start_date'), 'end_date' => Request::get('end_date'), 'search' => Request::get('search')])}}">
                                        <img width="14" src="{{asset('public/assets/admin/img/icons/excel.png')}}"
                                            alt="{{ translate('excel') }}">
                                        {{translate('excel')}}
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <form action="{{route('admin.orders.bulk-status')}}" method="POST" id="bulk-status-form">
                @csrf
                <input type="hidden" name="order_status" id="bulk-status-input" value="">
                <div class="table-responsive datatable-custom">
                <table
                    class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table text-dark">
                    <thead class="thead-light">
                        <tr>
                            <th>
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="select-all-orders">
                                    <label class="custom-control-label" for="select-all-orders"></label>
                                </div>
                            </th>
                            <th>{{translate('SL')}}</th>
                            <th>{{translate('order_ID')}}</th>
                            <th>{{translate('order_date')}}</th>
                            <th>{{translate('customer_info')}}</th>
                            <th>{{translate('branch')}}</th>
                            <th>{{translate('total_amount')}}</th>
                            <th>{{translate('order_status')}}</th>
                            <th>{{translate('order_type')}}</th>
                            <th class="text-center">{{translate('actions')}}</th>
                        </tr>
                    </thead>

                    <tbody id="set-rows">
                        @foreach($orders as $key => $order)

                            <tr class="status-{{$order['order_status']}} class-all">
                                <td>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input order-checkbox" name="order_ids[]" value="{{$order['id']}}"
                                            id="order-{{$order['id']}}">
                                        <label class="custom-control-label" for="order-{{$order['id']}}"></label>
                                    </div>
                                </td>
                                <td>
                                    {{$orders->firstitem() + $key}}
                                </td>
                                <td>
                                    <a class="text-dark"
                                        href="{{route('admin.orders.details', ['id' => $order['id']])}}">{{$order['id']}}</a>
                                </td>
                                <td>
                                    <div>{{date('d M Y', strtotime($order['created_at']))}}</div>
                                    <div class="fs-12">{{date("h:i A", strtotime($order['created_at']))}}</div>
                                </td>
                                <td>
                                    @if($order->is_guest == 0)
                                        @if($order->customer)
                                            <a class="text-dark text-capitalize"
                                                href="{{route('admin.customer.view', [$order['user_id']])}}">
                                                <h6 class="mb-0">{{$order->customer['f_name'] . ' ' . $order->customer['l_name']}}</h6>
                                            </a>
                                            <a class="text-dark fs-12"
                                                href="tel:{{ $order->customer['phone'] }}">{{ $order->customer['phone'] }}</a>
                                        @else
                                            <h6 class="text-muted text-capitalize">{{translate('customer')}} {{translate('deleted')}}
                                            </h6>
                                        @endif
                                    @else
                                        <h6 class="text-success">{{translate('Guest Customer')}}</h6>
                                    @endif

                                </td>
                                <td>
                                    <label
                                        class="badge badge-soft-primary">{{$order->branch ? $order->branch->name : 'Branch deleted!'}}</label>
                                </td>
                                <td>
                                    <div class="text-dark">{{ Helpers::set_symbol($order['order_amount']) }}</div>
                                    @if($order->payment_status == 'paid')
                                        <span class="text-success">
                                            {{translate('مدفوع')}}
                                        </span>
                                    @elseif($order->payment_status == 'partially_paid')
                                        <span class="text-warning">
                                            {{translate('مدفوع جزئياً')}}
                                        </span>
                                    @else
                                        <span class="text-danger">
                                            {{translate('غير مدفوع')}}
                                        </span>
                                    @endif
                                </td>
                                <td class="text-capitalize">
                                    <div class="dropdown">
                                        <button class="btn btn-soft-secondary btn-sm dropdown-toggle
                                                            {{$order['order_status'] == 'pending' ? 'btn-soft-info' : ''}}
                                                            {{$order['order_status'] == 'confirmed' ? 'btn-soft-info' : ''}}
                                                            {{$order['order_status'] == 'processing' ? 'btn-soft-warning' : ''}}
                                                            {{$order['order_status'] == 'out_for_delivery' ? 'btn-soft-warning' : ''}}
                                                            {{$order['order_status'] == 'delivered' ? 'btn-soft-success' : ''}}
                                                            {{$order['order_status'] == 'returned' ? 'btn-soft-danger' : ''}}
                                                            {{$order['order_status'] == 'failed' ? 'btn-soft-danger' : ''}}
                                                            {{$order['order_status'] == 'canceled' ? 'btn-soft-danger' : ''}}"
                                            type="button" id="dropdownMenuButton{{$order['id']}}" data-toggle="dropdown" data-boundary="viewport"
                                            aria-haspopup="true" aria-expanded="false">
                                            {{translate($order['order_status'])}}
                                        </button>
                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton{{$order['id']}}">
                                            <a class="dropdown-item"
                                                onclick="route_alert('{{route('admin.orders.status', ['id' => $order['id'], 'order_status' => 'pending'])}}','{{translate('Change status to pending ?')}}')"
                                                href="javascript:">{{translate('pending')}}</a>
                                            <a class="dropdown-item"
                                                onclick="route_alert('{{route('admin.orders.status', ['id' => $order['id'], 'order_status' => 'confirmed'])}}','{{translate('Change status to confirmed ?')}}')"
                                                href="javascript:">{{translate('confirmed')}}</a>
                                            <a class="dropdown-item"
                                                onclick="route_alert('{{route('admin.orders.status', ['id' => $order['id'], 'order_status' => 'processing'])}}','{{translate('Change status to processing ?')}}')"
                                                href="javascript:">{{translate('processing')}}</a>
                                            <a class="dropdown-item"
                                                onclick="route_alert('{{route('admin.orders.status', ['id' => $order['id'], 'order_status' => 'out_for_delivery'])}}','{{translate('Change status to out_for_delivery ?')}}')"
                                                href="javascript:">{{translate('out_for_delivery')}}</a>
                                            <a class="dropdown-item"
                                                onclick="route_alert('{{route('admin.orders.status', ['id' => $order['id'], 'order_status' => 'delivered'])}}','{{translate('Change status to delivered ?')}}')"
                                                href="javascript:">{{translate('delivered')}}</a>
                                            <a class="dropdown-item"
                                                onclick="route_alert('{{route('admin.orders.status', ['id' => $order['id'], 'order_status' => 'returned'])}}','{{translate('Change status to returned ?')}}')"
                                                href="javascript:">{{translate('returned')}}</a>
                                            <a class="dropdown-item"
                                                onclick="route_alert('{{route('admin.orders.status', ['id' => $order['id'], 'order_status' => 'failed'])}}','{{translate('Change status to failed ?')}}')"
                                                href="javascript:">{{translate('failed')}}</a>
                                            <a class="dropdown-item"
                                                onclick="route_alert('{{route('admin.orders.status', ['id' => $order['id'], 'order_status' => 'canceled'])}}','{{translate('Change status to canceled ?')}}')"
                                                href="javascript:">{{translate('canceled')}}</a>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-capitalize">
                                    @if($order['order_type'] == 'self_pickup')
                                        <span class="badge badge-soft-primary">{{translate('self_pickup')}}</span>
                                    @elseif($order['order_type'] == 'pos')
                                        <span class="badge badge-soft-info">{{translate('POS')}}</span>
                                    @else
                                        <span class="badge badge-soft-success">{{translate($order['order_type'])}}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-2 justify-content-center">
                                        <a class="btn btn-outline-primary square-btn"
                                            href="{{route('admin.orders.details', ['id' => $order['id']])}}">
                                            <i class="tio-visible"></i>
                                        </a>
                                        <a class="btn btn-outline-info square-btn" target="_blank"
                                            href="{{route('admin.orders.generate-invoice', [$order['id']])}}">
                                            <i class="tio-download"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>

                        @endforeach
                    </tbody>
                </table>
                </div>
            </form>

            <div class="table-responsive mt-4 px-3">
                <div class="d-flex justify-content-end">
                    {!! $orders->links() !!}
                </div>
            </div>
            @if(count($orders) == 0)
                <div class="text-center p-4">
                    <img class="mb-3 width-7rem" src="{{asset('public/assets/admin/svg/illustrations/sorry.svg')}}"
                        alt="{{ translate('image') }}">
                    <p class="mb-0">{{ translate('No data to show') }}</p>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('script_2')
    <script>
        $(document).on('change', '#select-all-orders', function () {
            $('.order-checkbox').prop('checked', this.checked);
        });

        $(document).on('change', '.order-checkbox', function () {
            if ($('.order-checkbox:checked').length == $('.order-checkbox').length) {
                $('#select-all-orders').prop('checked', true);
            } else {
                $('#select-all-orders').prop('checked', false);
            }
        });

        function submitBulkStatus(status) {
            if ($('.order-checkbox:checked').length === 0) {
                Swal.fire({
                    title: '{{translate("Warning")}}',
                    text: '{{translate("Please select at least one order")}}',
                    icon: 'warning',
                    confirmButtonText: '{{translate("OK")}}'
                });
                return;
            }
            $('#bulk-status-input').val(status);
            Swal.fire({
                title: '{{translate("Are you sure?")}}',
                text: '{{translate("You want to change status to ")}}' + status,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: '{{translate("Yes, change it!")}}'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#bulk-status-form').submit();
                }
            })
        }
    </script>
@endpush