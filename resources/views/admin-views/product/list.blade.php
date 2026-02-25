@extends('layouts.admin.app')

@section('title', translate('Product List'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex flex-wrap gap-3 align-items-center mb-3">
            <h2 class="text-capitalize mb-0 d-flex align-items-center gap-2">
                <img width="20" src="{{asset('assets/admin/img/icons/product.png')}}"
                    alt="{{ translate('product') }}">
                {{translate('product_list')}}
            </h2>
            <span class="badge badge-soft-dark rounded-50 fs-14">{{$products->total()}}</span>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <!-- Search & Actions Bar - Exact Match to Image -->
                    <div class="card-body py-3">
                        <div class="row g-2 align-items-center">
                            <!-- Search Input -->
                            <div class="col-lg-6">
                                <form action="{{url()->current()}}" method="GET" id="search-form">
                                    @if(request('stock_filter'))
                                        <input type="hidden" name="stock_filter" value="{{request('stock_filter')}}">
                                    @endif
                                    @if(request('status_filter'))
                                        <input type="hidden" name="status_filter" value="{{request('status_filter')}}">
                                    @endif
                                    <div class="position-relative">
                                        <input type="search" name="search" class="form-control search-input-modern"
                                            placeholder="{{translate('ابحث برقم المنتج أو الاسم')}}" value="{{$search}}"
                                            autocomplete="off">
                                        <i class="tio-search search-icon-modern"></i>
                                    </div>
                                </form>
                            </div>

                            <!-- Action Buttons -->
                            <div class="col-lg-6">
                                <div class="d-flex gap-2 justify-content-lg-end">
                                    <!-- Filter Button -->
                                    <button type="button" class="btn btn-outline-modern" data-toggle="collapse"
                                        data-target="#filtersCollapse">
                                        <i class="tio-tune mr-1"></i>
                                        <span>{{translate('Filter')}}</span>
                                    </button>


                                    <!-- Add New Button -->
                                    <a href="{{route('admin.product.add-new')}}" class="btn btn-primary-modern">
                                        <i class="tio-add mr-1"></i>
                                        <span>{{translate('إضافة منتج')}}</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Collapsible Filters Section -->
                    <div class="collapse" id="filtersCollapse">
                        <div class="filters-section">
                            <!-- Stock Status Filters -->
                            <div class="mb-4">
                                <label class="d-block mb-3"
                                    style="font-size: 13px; font-weight: 700; color: #374151; text-transform: uppercase; letter-spacing: 0.5px;">
                                    <i class="tio-layers-outlined mr-1" style="color: #6b7280;"></i>
                                    {{translate('حالة المخزون')}}
                                </label>
                                <div class="filter-pills-container">
                                    <a href="{{route('admin.product.list', array_merge(request()->except('stock_filter'), request('status_filter') ? ['status_filter' => request('status_filter')] : [], request('search') ? ['search' => request('search')] : []))}}"
                                        class="modern-filter-pill {{ (!$stock_filter || $stock_filter == 'all') ? 'active' : '' }}">
                                        {{translate('الكل')}}
                                    </a>
                                    <a href="{{route('admin.product.list', array_merge(['stock_filter' => 'in_stock'], request('status_filter') ? ['status_filter' => request('status_filter')] : [], request('search') ? ['search' => request('search')] : []))}}"
                                        class="modern-filter-pill pill-success {{ $stock_filter == 'in_stock' ? 'active' : '' }}">
                                        <span>✓</span>
                                        <span>{{translate('متوفر')}}</span>
                                        <span class="pill-count">+10</span>
                                    </a>
                                    <a href="{{route('admin.product.list', array_merge(['stock_filter' => 'low_stock'], request('status_filter') ? ['status_filter' => request('status_filter')] : [], request('search') ? ['search' => request('search')] : []))}}"
                                        class="modern-filter-pill pill-warning {{ $stock_filter == 'low_stock' ? 'active' : '' }}">
                                        <span>!</span>
                                        <span>{{translate('منخفض')}}</span>
                                        <span class="pill-count">1-10</span>
                                    </a>
                                    <a href="{{route('admin.product.list', array_merge(['stock_filter' => 'out_of_stock'], request('status_filter') ? ['status_filter' => request('status_filter')] : [], request('search') ? ['search' => request('search')] : []))}}"
                                        class="modern-filter-pill pill-danger {{ $stock_filter == 'out_of_stock' ? 'active' : '' }}">
                                        <span>×</span>
                                        <span>{{translate('نفذ')}}</span>
                                        <span class="pill-count">0</span>
                                    </a>
                                </div>
                            </div>

                            <!-- Product Status Filters -->
                            <div class="mb-0">
                                <label class="d-block mb-3"
                                    style="font-size: 13px; font-weight: 700; color: #374151; text-transform: uppercase; letter-spacing: 0.5px;">
                                    <i class="tio-toggle mr-1" style="color: #6b7280;"></i>
                                    {{translate('حالة المنتج')}}
                                </label>
                                <div class="filter-pills-container">
                                    <a href="{{route('admin.product.list', array_merge(request()->except('status_filter'), request('stock_filter') ? ['stock_filter' => request('stock_filter')] : [], request('search') ? ['search' => request('search')] : []))}}"
                                        class="modern-filter-pill {{ (!$status_filter || $status_filter == 'all') ? 'active' : '' }}">
                                        {{translate('الكل')}}
                                    </a>
                                    <a href="{{route('admin.product.list', array_merge(['status_filter' => 'active'], request('stock_filter') ? ['stock_filter' => request('stock_filter')] : [], request('search') ? ['search' => request('search')] : []))}}"
                                        class="modern-filter-pill pill-success {{ $status_filter == 'active' ? 'active' : '' }}">
                                        <span>●</span>
                                        <span>{{translate('نشط')}}</span>
                                    </a>
                                    <a href="{{route('admin.product.list', array_merge(['status_filter' => 'inactive'], request('stock_filter') ? ['stock_filter' => request('stock_filter')] : [], request('search') ? ['search' => request('search')] : []))}}"
                                        class="modern-filter-pill pill-danger {{ $status_filter == 'inactive' ? 'active' : '' }}">
                                        <span>●</span>
                                        <span>{{translate('غير نشط')}}</span>
                                    </a>
                                </div>
                            </div>

                            <!-- Active Filters Summary -->
                            @if(($stock_filter && $stock_filter != 'all') || ($status_filter && $status_filter != 'all'))
                                <div class="mt-4 pt-4" style="border-top: 2px dashed #e5e7eb;">
                                    <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                                        <small class="text-muted"
                                            style="font-weight: 600;">{{translate('الفلاتر النشطة:')}}</small>
                                        @if($stock_filter && $stock_filter != 'all')
                                            <span class="badge"
                                                style="background: {{ $stock_filter == 'in_stock' ? '#d1fae5' : ($stock_filter == 'low_stock' ? '#fef3c7' : '#fee2e2') }}; color: {{ $stock_filter == 'in_stock' ? '#059669' : ($stock_filter == 'low_stock' ? '#d97706' : '#dc2626') }}; padding: 6px 12px; border-radius: 16px; font-size: 13px; font-weight: 600;">
                                                @if($stock_filter == 'in_stock')
                                                    <i class="tio-checkmark-circle-outlined"></i> {{translate('متوفر')}}
                                                @elseif($stock_filter == 'low_stock')
                                                    <i class="tio-warning-outlined"></i> {{translate('مخزون منخفض')}}
                                                @else
                                                    <i class="tio-clear-circle-outlined"></i> {{translate('نفذ المخزون')}}
                                                @endif
                                                <a href="{{route('admin.product.list', array_merge(request()->except('stock_filter'), request('status_filter') ? ['status_filter' => request('status_filter')] : []))}}"
                                                    class="mr-1" style="color: inherit; opacity: 0.7;">
                                                    <i class="tio-clear"></i>
                                                </a>
                                            </span>
                                        @endif
                                        @if($status_filter && $status_filter != 'all')
                                            <span class="badge"
                                                style="background: {{ $status_filter == 'active' ? '#d1fae5' : '#fee2e2' }}; color: {{ $status_filter == 'active' ? '#059669' : '#dc2626' }}; padding: 6px 12px; border-radius: 16px; font-size: 13px; font-weight: 600;">
                                                <i
                                                    class="tio-{{ $status_filter == 'active' ? 'checkmark' : 'close' }}-circle-outlined"></i>
                                                {{ $status_filter == 'active' ? translate('نشط') : translate('غير نشط') }}
                                                <a href="{{route('admin.product.list', array_merge(request()->except('status_filter'), request('stock_filter') ? ['stock_filter' => request('stock_filter')] : []))}}"
                                                    class="mr-1" style="color: inherit; opacity: 0.7;">
                                                    <i class="tio-clear"></i>
                                                </a>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>


                    <!-- Products Table -->
                    <div class="table-responsive datatable-custom">
                        <table
                            class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                            <thead class="thead-light">
                                <tr>
                                    <th>{{translate('SL')}}</th>
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
                                                    <img src="{{$product['image_fullpath'][0]}}" class="img-fit rounded"
                                                        alt="{{ translate('product') }}">
                                                </div>
                                                <a href="{{route('admin.product.view', [$product['id']])}}"
                                                    class="media-body text-dark">
                                                    {{substr($product['name'], 0, 20)}}{{strlen($product['name']) > 20 ? '...' : ''}}
                                                </a>
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
                                                <label class="badge badge-soft-success fs-14">
                                                    <i class="tio-checkmark-circle"></i> {{$product['total_stock']}}
                                                </label>
                                            @elseif($product['total_stock'] > 0 && $product['total_stock'] <= 10)
                                                <label class="badge badge-soft-warning fs-14">
                                                    <i class="tio-warning"></i> {{$product['total_stock']}}
                                                </label>
                                            @else
                                                <label class="badge badge-soft-danger fs-14">
                                                    <i class="tio-clear-circle"></i> {{$product['total_stock']}}
                                                </label>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2 justify-content-center">
                                                <a class="btn btn-outline-primary square-btn"
                                                    href="{{route('admin.product.edit', [$product['id']])}}">
                                                    <i class="tio tio-edit"></i>
                                                </a>
                                                <a class="btn btn-outline-danger square-btn form-alert" href="javascript:"
                                                    data-id="product-{{$product['id']}}"
                                                    data-message="{{translate('Want to delete this product ?')}}">
                                                    <i class="tio tio-delete"></i>
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

                    <div class="table-responsive mt-4 px-3">
                        <div class="d-flex justify-content-end">
                            {!! $products->links() !!}
                        </div>
                    </div>
                    @if(count($products) == 0)
                        <div class="text-center p-4">
                            <img class="mb-3 width-7rem" src="{{asset('assets/admin/svg/illustrations/sorry.svg')}}"
                                alt="{{ translate('image') }}">
                            <p class="mb-0">{{ translate('No data to show') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

@endsection

@push('script')
    <style>
        /* ==========================================================================
                       💎 PRO PRODUCT LIST UI - Senior Front-End Implementation
                       ========================================================================== */

        :root {
            /* 🎨 Color Palette */
            --primary: #3b82f6;
            /* Brand Blue */
            --primary-dark: #2563eb;
            /* Darker Blue for Hover */
            --secondary: #6b7280;
            /* Gray Text */
            --success: #10b981;
            /* Green */
            --warning: #f59e0b;
            /* Orange */
            --danger: #ef4444;
            /* Red */

            /* 🌑 Backgrounds & Borders */
            --bg-body: #f3f4f6;
            --bg-card: #ffffff;
            --bg-input: #f9fafb;
            --border-color: #e5e7eb;


            /* 📐 Spacing & Radius - SOFTER FEEL */
            --radius-sm: 0.5rem;
            --radius-md: 0.85rem;
            /* Increased from 0.5rem to 14px */
            --radius-lg: 1.25rem;
            /* Increased from 0.75rem to 20px */
            --radius-pill: 9999px;

            /* 🌑 Shadows - SOFTER & DIFFUSED */
            --shadow-sm: 0 2px 8px -2px rgba(0, 0, 0, 0.05);
            /* Softer float */
            --shadow-md: 0 8px 16px -4px rgba(0, 0, 0, 0.08);
            --shadow-focus: 0 0 0 4px rgba(59, 130, 246, 0.12);
            /* Bigger, lighter focus ring */
        }

        /* --------------------------------------------------------------------------
                   📦 Card Component - Softer
                   -------------------------------------------------------------------------- */
        .card.border-0.shadow-sm {
            background-color: var(--bg-card);
            border: 1px solid rgba(229, 231, 235, 0.5) !important;
            /* Lighter border */
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm) !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: visible;
        }

        .card.border-0.shadow-sm:hover {
            box-shadow: var(--shadow-md) !important;
            transform: translateY(-2px);
        }

        .card-body.py-3 {
            padding: 1.25rem 1.5rem !important;
        }

        /* --------------------------------------------------------------------------
               🔍 Search Input - Compact & Soft
               -------------------------------------------------------------------------- */
        .search-input-modern {
            height: 40px;
            /* Reduced from 50px */
            background-color: #f8fafc;
            border: 1px solid transparent;
            border-radius: 40px;
            /* Fully Rounded */
            padding-left: 1rem;
            padding-right: 2.75rem;
            font-size: 0.875rem;
            /* 14px */
            color: #1f2937;
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.02);
            transition: all 0.25s ease;
        }

        .search-input-modern:hover {
            background-color: #fff;
            box-shadow: 0 0 0 1px #e5e7eb;
        }

        .search-input-modern:focus {
            background-color: #fff;
            border-color: var(--primary);
            box-shadow: var(--shadow-focus);
            outline: none;
        }

        .search-input-modern::placeholder {
            color: #9ca3af;
            font-weight: 400;
        }

        .search-icon-modern {
            position: absolute;
            right: 1.25rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 1.1rem;
            pointer-events: none;
            transition: color 0.2s ease;
        }

        .search-input-modern:focus~.search-icon-modern {
            color: var(--primary);
        }

        /* --------------------------------------------------------------------------
               🔘 Buttons - Compact & Soft
               -------------------------------------------------------------------------- */
        .btn-outline-modern {
            height: 40px;
            /* Reduced from 50px */
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 1.25rem;
            /* Reduced padding */
            background-color: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 40px;
            color: var(--secondary);
            font-weight: 600;
            font-size: 0.875rem;
            /* 14px */
            transition: all 0.25s ease;
            gap: 0.5rem;
        }

        .btn-outline-modern:hover {
            border-color: #d1d5db;
            color: var(--text-dark);
            background-color: #f9fafb;
            transform: translateY(-1px);
            box-shadow: var(--shadow-sm);
        }

        .btn-primary-modern {
            height: 40px;
            /* Reduced from 50px */
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 1.25rem;
            /* Reduced padding */
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            border: none;
            border-radius: 40px;
            color: #fff !important;
            font-weight: 600;
            font-size: 0.875rem;
            /* 14px */
            box-shadow: 0 4px 10px rgba(59, 130, 246, 0.25);
            transition: all 0.25s ease;
            gap: 0.5rem;
            text-decoration: none !important;
        }

        .btn-primary-modern:hover {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            transform: translateY(-1px);
            box-shadow: 0 6px 14px rgba(59, 130, 246, 0.35);
            color: #fff !important;
        }

        .btn-primary-modern:active {
            transform: translateY(0);
        }

        /* --------------------------------------------------------------------------
                       🏷️ Filters Section
                       -------------------------------------------------------------------------- */
        .filters-section {
            background-color: #fcfcfc;
            border-top: 1px solid var(--border-color);
            padding: 1.5rem;
            animation: slideDown 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .filter-pills-container {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
        }


        /* Modern Filter Pill - Compact */
        .modern-filter-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.35rem 0.85rem;
            /* Reduced padding */
            background-color: #fff;
            border: 1px solid var(--border-color);
            border-radius: 30px;
            /* Fully rounded */
            color: var(--secondary);
            font-size: 0.8125rem;
            /* 13px */
            font-weight: 500;
            transition: all 0.2s ease;
            text-decoration: none !important;
            cursor: pointer;
            user-select: none;
        }

        .modern-filter-pill:hover {
            background-color: var(--bg-input);
            color: var(--text-dark);
            border-color: #d1d5db;
        }

        .modern-filter-pill.active {
            background-color: #eff6ff;
            /* Light Blue BG */
            border-color: var(--primary);
            color: var(--primary);
            box-shadow: 0 0 0 1px var(--primary);
            font-weight: 600;
        }

        /* 🚥 Status-Specific Active States */
        .modern-filter-pill.pill-success.active {
            background-color: #ecfdf5;
            border-color: var(--success);
            color: #047857;
            box-shadow: 0 0 0 1px var(--success);
        }

        .modern-filter-pill.pill-warning.active {
            background-color: #fffbeb;
            border-color: var(--warning);
            color: #b45309;
            box-shadow: 0 0 0 1px var(--warning);
        }

        .modern-filter-pill.pill-danger.active {
            background-color: #fef2f2;
            border-color: var(--danger);
            color: #b91c1c;
            box-shadow: 0 0 0 1px var(--danger);
        }

        /* 🔢 Pill Count - Compact */
        .pill-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.1em 0.5em;
            /* Tighter padding */
            background-color: rgba(0, 0, 0, 0.05);
            border-radius: 20px;
            font-size: 0.7rem;
            /* Smaller font */
            font-weight: 700;
            color: inherit;
            line-height: 1;
            min-width: 18px;
            height: 18px;
        }

        .modern-filter-pill.active .pill-count {
            background-color: rgba(255, 255, 255, 0.5);
            /* Semi-transparent white */
        }

        /* --------------------------------------------------------------------------
                       📊 Table Badges
                       -------------------------------------------------------------------------- */
        .badge {
            padding: 0.4em 0.8em;
            border-radius: var(--radius-sm);
            font-weight: 600;
            letter-spacing: 0.02em;
        }

        .badge.badge-soft-success {
            background-color: #d1fae5;
            color: #065f46;
        }

        .badge.badge-soft-warning {
            background-color: #fef3c7;
            color: #92400e;
        }

        .badge.badge-soft-danger {
            background-color: #fee2e2;
            color: #b91c1c;
        }

        /* --------------------------------------------------------------------------
                       📱 Responsiveness
                       -------------------------------------------------------------------------- */
        @media (max-width: 991px) {

            .btn-outline-modern,
            .btn-primary-modern {
                width: 100%;
                margin-bottom: 0.75rem;
            }

            .search-input-modern {
                margin-bottom: 1rem;
            }
        }

        /* Utilities */
        .mr-1 {
            margin-right: 0.25rem !important;
        }

        .gap-2 {
            gap: 0.5rem !important;
        }

        /* Reset any Bootstrap conflicts */
        .btn:focus,
        .form-control:focus {
            box-shadow: none;
        }
    </style>
@endpush