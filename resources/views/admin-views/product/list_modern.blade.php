@extends('layouts.admin.app')

@section('title', translate('Product List'))

@push('css_or_js')
    <style>
        /* ============= Professional Product List Filters ============= */

        /* Card Styling */
        .products-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .products-header {
            background: #ffffff;
            border-bottom: 1px solid #f3f4f6;
            padding: 24px 28px;
        }

        /* Search Wrapper */
        .modern-search-wrapper {
            position: relative;
            width: 100%;
        }

        .modern-search-wrapper .search-icon {
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 18px;
            z-index: 2;
        }

        .modern-search-input {
            height: 48px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding-right: 50px;
            padding-left: 20px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            background: #f9fafb;
        }

        .modern-search-input:focus {
            border-color: #6366f1;
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
            outline: none;
        }

        .modern-search-input::placeholder {
            color: #9ca3af;
            font-weight: 400;
        }

        .btn-modern-search {
            height: 48px;
            background: #6366f1;
            border: none;
            border-radius: 12px;
            color: #ffffff;
            font-weight: 600;
            font-size: 14px;
            padding: 0 24px;
            margin-right: 12px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.25);
        }

        .btn-modern-search:hover {
            background: #4f46e5;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(99, 102, 241, 0.35);
            color: #ffffff;
        }

        .btn-modern-search i {
            margin-left: 6px;
        }

        /* Add Product Button */
        .btn-add-product {
            height: 48px;
            background: #6366f1;
            border: none;
            border-radius: 12px;
            color: #ffffff;
            font-weight: 600;
            font-size: 14px;
            padding: 0 24px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.25);
        }

        .btn-add-product:hover {
            background: #4f46e5;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(99, 102, 241, 0.35);
            color: #ffffff;
        }

        /* Filters Section */
        .filters-section {
            background: #f9fafb;
            padding: 24px 28px;
            border-top: 1px solid #f3f4f6;
        }

        .filters-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
        }

        .filters-title {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .filters-title h6 {
            font-size: 15px;
            font-weight: 700;
            color: #111827;
            margin: 0;
        }

        .filters-title i {
            color: #6366f1;
            font-size: 20px;
        }

        .product-count-badge {
            background: #e0e7ff;
            color: #6366f1;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }

        .btn-clear-all {
            background: #ffffff;
            border: 2px solid #e5e7eb;
            border-radius: 20px;
            color: #6b7280;
            font-size: 13px;
            font-weight: 600;
            padding: 6px 16px;
            transition: all 0.2s ease;
        }

        .btn-clear-all:hover {
            border-color: #ef4444;
            color: #ef4444;
            background: #fef2f2;
        }

        /* Filter Group */
        .modern-filter-group {
            margin-bottom: 20px;
        }

        .modern-filter-label {
            font-size: 13px;
            font-weight: 700;
            color: #374151;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .modern-filter-label i {
            color: #6b7280;
            font-size: 16px;
        }

        /* Filter Pills */
        .filter-pills-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .modern-filter-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            background: #ffffff;
            border: 2px solid #e5e7eb;
            border-radius: 24px;
            font-size: 14px;
            font-weight: 600;
            color: #6b7280;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
        }

        .modern-filter-pill:hover {
            border-color: #6366f1;
            color: #6366f1;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.15);
            text-decoration: none;
        }

        .modern-filter-pill.active {
            background: #6366f1;
            border-color: #6366f1;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        .modern-filter-pill.active:hover {
            background: #4f46e5;
            border-color: #4f46e5;
            color: #ffffff;
        }

        /* Success Pills */
        .pill-success:not(.active) {
            border-color: #d1fae5;
            background: #ecfdf5;
        }

        .pill-success:not(.active):hover {
            border-color: #10b981;
            color: #10b981;
            background: #d1fae5;
        }

        .pill-success.active {
            background: #10b981;
            border-color: #10b981;
        }

        /* Warning Pills */
        .pill-warning:not(.active) {
            border-color: #fed7aa;
            background: #fffbeb;
        }

        .pill-warning:not(.active):hover {
            border-color: #f59e0b;
            color: #f59e0b;
            background: #fef3c7;
        }

        .pill-warning.active {
            background: #f59e0b;
            border-color: #f59e0b;
        }

        /* Danger Pills */
        .pill-danger:not(.active) {
            border-color: #fecaca;
            background: #fef2f2;
        }

        .pill-danger:not(.active):hover {
            border-color: #ef4444;
            color: #ef4444;
            background: #fee2e2;
        }

        .pill-danger.active {
            background: #ef4444;
            border-color: #ef4444;
        }

        /* Pill Elements */
        .pill-icon {
            font-size: 16px;
            font-weight: bold;
        }

        .pill-count {
            background: rgba(0, 0, 0, 0.1);
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 700;
        }

        .modern-filter-pill.active .pill-count {
            background: rgba(255, 255, 255, 0.25);
            color: #ffffff;
        }

        /* Active Filters */
        .active-filters-section {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px dashed #e5e7eb;
            animation: fadeInUp 0.4s ease;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .active-filter-tag {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }

        .tag-success {
            background: #d1fae5;
            color: #059669;
        }

        .tag-warning {
            background: #fef3c7;
            color: #d97706;
        }

        .tag-danger {
            background: #fee2e2;
            color: #dc2626;
        }

        .tag-remove {
            color: inherit;
            opacity: 0.7;
            margin-right: 4px;
            transition: all 0.2s;
            padding: 2px;
            border-radius: 50%;
        }

        .tag-remove:hover {
            opacity: 1;
            background: rgba(0, 0, 0, 0.1);
        }

        /* Stock Badges in Table */
        .stock-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 6px 12px;
            border-radius: 16px;
            font-size: 13px;
            font-weight: 600;
        }

        .stock-badge-success {
            background: #d1fae5;
            color: #059669;
        }

        .stock-badge-warning {
            background: #fef3c7;
            color: #d97706;
        }

        .stock-badge-danger {
            background: #fee2e2;
            color: #dc2626;
        }

        /* Responsive */
        @media (max-width: 768px) {

            .products-header,
            .filters-section {
                padding: 20px;
            }

            .modern-search-input,
            .btn-modern-search,
            .btn-add-product {
                height: 44px;
                font-size: 13px;
            }

            .modern-filter-pill {
                padding: 8px 14px;
                font-size: 13px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="d-flex flex-wrap gap-3 align-items-center mb-3">
            <h2 class="text-capitalize mb-0 d-flex align-items-center gap-2">
                <img width="20" src="{{asset('assets/admin/img/icons/product.png')}}"
                    alt="{{ translate('product') }}">
                {{translate('product_list')}}
            </h2>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card products-card">
                    <!-- Header: Search + Add Button -->
                    <div class="products-header">
                        <div class="row align-items-center g-3">
                            <!-- Search -->
                            <div class="col-lg-8">
                                <div class="d-flex gap-2">
                                    <form action="{{url()->current()}}" method="GET" class="flex-grow-1">
                                        @if(request('stock_filter'))
                                            <input type="hidden" name="stock_filter" value="{{request('stock_filter')}}">
                                        @endif
                                        @if(request('status_filter'))
                                            <input type="hidden" name="status_filter" value="{{request('status_filter')}}">
                                        @endif
                                        <div class="modern-search-wrapper">
                                            <i class="tio-search search-icon"></i>
                                            <input type="search" name="search" class="form-control modern-search-input"
                                                placeholder="{{translate('ابحث عن منتج...')}}" value="{{$search}}"
                                                autocomplete="off">
                                        </div>
                                    </form>
                                    <button type="submit" form="search-form" class="btn btn-modern-search">
                                        {{translate('بحث')}}
                                        <i class="tio-search"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Add Product Button -->
                            <div class="col-lg-4 text-lg-left">
                                <a href="{{route('admin.product.add-new')}}" class="btn btn-add-product w-100 w-lg-auto">
                                    <i class="tio-add-circle-outlined"></i>
                                    <span>{{translate('إضافة منتج جديد')}}</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Filters Section -->
                    <div class="filters-section">
                        <!-- Filters Header -->
                        <div class="filters-header">
                            <div class="filters-title">
                                <i class="tio-tune"></i>
                                <h6>{{translate('تصفية المنتجات')}}</h6>
                                <span class="product-count-badge">{{$products->total()}} {{translate('منتج')}}</span>
                            </div>
                            @if(($stock_filter && $stock_filter != 'all') || ($status_filter && $status_filter != 'all') || $search)
                                <a href="{{route('admin.product.list')}}" class="btn btn-clear-all">
                                    <i class="tio-clear mr-1"></i>
                                    {{translate('مسح الكل')}}
                                </a>
                            @endif
                        </div>

                        <!-- Stock Status Filters -->
                        <div class="modern-filter-group">
                            <label class="modern-filter-label">
                                <i class="tio-layers-outlined"></i>
                                {{translate('حالة المخزون')}}
                            </label>
                            <div class="filter-pills-container">
                                <a href="{{route('admin.product.list', array_merge(request()->except('stock_filter'), request('status_filter') ? ['status_filter' => request('status_filter')] : [], request('search') ? ['search' => request('search')] : []))}}"
                                    class="modern-filter-pill {{ (!$stock_filter || $stock_filter == 'all') ? 'active' : '' }}">
                                    <span class="pill-text">{{translate('الكل')}}</span>
                                </a>
                                <a href="{{route('admin.product.list', array_merge(['stock_filter' => 'in_stock'], request('status_filter') ? ['status_filter' => request('status_filter')] : [], request('search') ? ['search' => request('search')] : []))}}"
                                    class="modern-filter-pill pill-success {{ $stock_filter == 'in_stock' ? 'active' : '' }}">
                                    <span class="pill-icon">✓</span>
                                    <span class="pill-text">{{translate('متوفر')}}</span>
                                    <span class="pill-count">+10</span>
                                </a>
                                <a href="{{route('admin.product.list', array_merge(['stock_filter' => 'low_stock'], request('status_filter') ? ['status_filter' => request('status_filter')] : [], request('search') ? ['search' => request('search')] : []))}}"
                                    class="modern-filter-pill pill-warning {{ $stock_filter == 'low_stock' ? 'active' : '' }}">
                                    <span class="pill-icon">!</span>
                                    <span class="pill-text">{{translate('منخفض')}}</span>
                                    <span class="pill-count">1-10</span>
                                </a>
                                <a href="{{route('admin.product.list', array_merge(['stock_filter' => 'out_of_stock'], request('status_filter') ? ['status_filter' => request('status_filter')] : [], request('search') ? ['search' => request('search')] : []))}}"
                                    class="modern-filter-pill pill-danger {{ $stock_filter == 'out_of_stock' ? 'active' : '' }}">
                                    <span class="pill-icon">×</span>
                                    <span class="pill-text">{{translate('نفذ')}}</span>
                                    <span class="pill-count">0</span>
                                </a>
                            </div>
                        </div>

                        <!-- Product Status Filters -->
                        <div class="modern-filter-group mb-0">
                            <label class="modern-filter-label">
                                <i class="tio-toggle"></i>
                                {{translate('حالة المنتج')}}
                            </label>
                            <div class="filter-pills-container">
                                <a href="{{route('admin.product.list', array_merge(request()->except('status_filter'), request('stock_filter') ? ['stock_filter' => request('stock_filter')] : [], request('search') ? ['search' => request('search')] : []))}}"
                                    class="modern-filter-pill {{ (!$status_filter || $status_filter == 'all') ? 'active' : '' }}">
                                    <span class="pill-text">{{translate('الكل')}}</span>
                                </a>
                                <a href="{{route('admin.product.list', array_merge(['status_filter' => 'active'], request('stock_filter') ? ['stock_filter' => request('stock_filter')] : [], request('search') ? ['search' => request('search')] : []))}}"
                                    class="modern-filter-pill pill-success {{ $status_filter == 'active' ? 'active' : '' }}">
                                    <span class="pill-icon">●</span>
                                    <span class="pill-text">{{translate('نشط')}}</span>
                                </a>
                                <a href="{{route('admin.product.list', array_merge(['status_filter' => 'inactive'], request('stock_filter') ? ['stock_filter' => request('stock_filter')] : [], request('search') ? ['search' => request('search')] : []))}}"
                                    class="modern-filter-pill pill-danger {{ $status_filter == 'inactive' ? 'active' : '' }}">
                                    <span class="pill-icon">●</span>
                                    <span class="pill-text">{{translate('غير نشط')}}</span>
                                </a>
                            </div>
                        </div>

                        <!-- Active Filters Tags -->
                        @if(($stock_filter && $stock_filter != 'all') || ($status_filter && $status_filter != 'all'))
                            <div class="active-filters-section">
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <small class="text-muted fw-bold">{{translate('الفلاتر النشطة:')}}</small>
                                    @if($stock_filter && $stock_filter != 'all')
                                        <span
                                            class="active-filter-tag tag-{{ $stock_filter == 'in_stock' ? 'success' : ($stock_filter == 'low_stock' ? 'warning' : 'danger') }}">
                                            @if($stock_filter == 'in_stock')
                                                <i class="tio-checkmark-circle-outlined"></i> {{translate('متوفر')}}
                                            @elseif($stock_filter == 'low_stock')
                                                <i class="tio-warning-outlined"></i> {{translate('مخزون منخفض')}}
                                            @else
                                                <i class="tio-clear-circle-outlined"></i> {{translate('نفذ المخزون')}}
                                            @endif
                                            <a href="{{route('admin.product.list', array_merge(request()->except('stock_filter'), request('status_filter') ? ['status_filter' => request('status_filter')] : []))}}"
                                                class="tag-remove">
                                                <i class="tio-clear"></i>
                                            </a>
                                        </span>
                                    @endif
                                    @if($status_filter && $status_filter != 'all')
                                        <span class="active-filter-tag tag-{{ $status_filter == 'active' ? 'success' : 'danger' }}">
                                            <i
                                                class="tio-{{ $status_filter == 'active' ? 'checkmark' : 'close' }}-circle-outlined"></i>
                                            {{ $status_filter == 'active' ? translate('نشط') : translate('غير نشط') }}
                                            <a href="{{route('admin.product.list', array_merge(request()->except('status_filter'), request('stock_filter') ? ['stock_filter' => request('stock_filter')] : []))}}"
                                                class="tag-remove">
                                                <i class="tio-clear"></i>
                                            </a>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Products Table -->
                    <div class="table-responsive datatable-custom">
                        <table
                            class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                            <thead class="thead-light">
                                <tr>
                                    <th>{{translate('#')}}</th>
                                    <th>{{translate('product_name')}}</th>
                                    <th>{{translate('status')}}</th>
                                    <th>{{translate('price')}}</th>
                                    <th>{{translate('stock')}}</th>
                                    <th class="text-center">{{translate('action')}}</th>
                                </tr>
                            </thead>

                            <tbody id="set-rows">
                                @foreach($products as $key => $product)
                                    <tr>
                                        <td>{{$products->firstitem() + $key}}</td>
                                        <td>
                                            <div class="media gap-3 align-items-center">
                                                <div class="avatar rounded border">
                                                    <img class="img-fit"
                                                        src="{{asset('storage/product')}}/{{$product['image']}}"
                                                        onerror="this.src='{{asset('assets/admin/img/160x160/img2.jpg')}}'"
                                                        alt="{{ translate('image') }}">
                                                </div>
                                                <div class="media-body">
                                                    <a href="{{route('admin.product.view', [$product['id']])}}"
                                                        class="title-color hover-c1">{{Str::limit($product['name'], 25)}}</a>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($product['status'] == 1)
                                                <label class="switcher">
                                                    <input type="checkbox" class="switcher_input change-status" checked
                                                        id="{{$product['id']}}"
                                                        data-route="{{route('admin.product.status', [$product['id'], 0])}}">
                                                    <span class="switcher_control"></span>
                                                </label>
                                            @else
                                                <label class="switcher">
                                                    <input type="checkbox" class="switcher_input change-status"
                                                        id="{{$product['id']}}"
                                                        data-route="{{route('admin.product.status', [$product['id'], 1])}}">
                                                    <span class="switcher_control"></span>
                                                </label>
                                            @endif
                                        </td>
                                        <td>{{ Helpers::set_symbol($product['price']) }}</td>
                                        <td>
                                            @if($product['total_stock'] > 10)
                                                <span class="stock-badge stock-badge-success">
                                                    <i class="tio-checkmark-circle"></i> {{$product['total_stock']}}
                                                </span>
                                            @elseif($product['total_stock'] > 0 && $product['total_stock'] <= 10)
                                                <span class="stock-badge stock-badge-warning">
                                                    <i class="tio-warning"></i> {{$product['total_stock']}}
                                                </span>
                                            @else
                                                <span class="stock-badge stock-badge-danger">
                                                    <i class="tio-clear-circle"></i> {{$product['total_stock']}}
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2 justify-content-center">
                                                <a class="btn btn-outline-info btn-sm edit square-btn"
                                                    title="{{ translate('Edit') }}"
                                                    href="{{route('admin.product.edit', [$product['id']])}}">
                                                    <i class="tio-edit"></i>
                                                </a>
                                                <a class="btn btn-outline-danger btn-sm delete square-btn"
                                                    title="{{ translate('Delete') }}" id="{{$product['id']}}">
                                                    <i class="tio-delete"></i>
                                                </a>
                                            </div>
                                            <form action="{{route('admin.product.delete', [$product['id']])}}" method="post"
                                                id="product-{{$product['id']}}">
                                                @csrf @method('delete')
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if(count($products) == 0)
                        <div class="text-center p-4">
                            <img class="mb-3 width-7rem" src="{{asset('assets/admin/svg/illustrations/sorry.svg')}}"
                                alt="{{ translate('image') }}">
                            <p class="mb-0">{{ translate('No data to show') }}</p>
                        </div>
                    @endif

                    <div class="table-responsive mt-4 px-3">
                        <div class="d-flex justify-content-lg-end">
                            {!! $products->links() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection