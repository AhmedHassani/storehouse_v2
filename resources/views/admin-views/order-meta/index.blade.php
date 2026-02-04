@extends('layouts.admin.app')

@section('title', translate('Order Attributes'))

@push('css_or_js')
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
            <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                <img width="20" src="{{asset('public/assets/admin/img/attribute.png')}}" alt="">
                <span class="page-header-title">
                    {{translate('Order Attributes Management')}}
                </span>
            </h2>
        </div>
        <!-- End Page Header -->

        <div class="row g-3">
            <div class="col-12">
                <div class="card card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <ul class="nav nav-tabs border-0 gap-3">
                                <li class="nav-item">
                                    <a class="nav-link {{$type == 'sale_channel' ? 'active' : ''}}"
                                        href="{{route('admin.order-meta.index', ['type' => 'sale_channel'])}}">
                                        {{translate('Sale Channels')}}
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{$type == 'sale_agent' ? 'active' : ''}}"
                                        href="{{route('admin.order-meta.index', ['type' => 'sale_agent'])}}">
                                        {{translate('Sale Agents')}}
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="mt-4">
                        <form action="{{route('admin.order-meta.store')}}" method="post">
                            @csrf
                            <input type="hidden" name="type" value="{{$type}}">
                            <div class="row align-items-end g-3">
                                <div class="col-md-8">
                                    <label class="input-label">{{translate('Add New')}}
                                        {{translate(str_replace('_', ' ', $type))}}</label>
                                    <input type="text" name="name" class="form-control"
                                        placeholder="{{translate('Enter Name')}}" required>
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-primary btn-block">{{translate('Add')}}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-12 mt-3">
                <div class="card">
                    <div class="table-responsive datatable-custom">
                        <table
                            class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                            <thead class="thead-light">
                                <tr>
                                    <th>{{translate('SL')}}</th>
                                    <th>{{translate('Name')}}</th>
                                    <th class="text-center">{{translate('Action')}}</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($options as $key => $option)
                                    <tr>
                                        <td>{{$key + 1}}</td>
                                        <td>{{$option->name}}</td>
                                        <td>
                                            <div class="d-flex justify-content-center gap-2">
                                                <a class="btn btn-outline-info btn-sm edit square-btn"
                                                    title="{{translate('Edit')}}"
                                                    href="{{route('admin.order-meta.edit', [$option->id])}}">
                                                    <i class="tio-edit"></i>
                                                </a>
                                                <button class="btn btn-outline-danger btn-sm delete square-btn"
                                                    title="{{translate('Delete')}}"
                                                    onclick="form_alert('option-{{$option->id}}','{{translate('Want to delete this option ?')}}')">
                                                    <i class="tio-delete-outlined"></i>
                                                </button>
                                                <form action="{{route('admin.order-meta.delete', [$option->id])}}" method="post"
                                                    id="option-{{$option->id}}">
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
        </div>
    </div>
@endsection

@push('script_2')
@endpush