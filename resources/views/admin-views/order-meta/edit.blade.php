@extends('layouts.admin.app')

@section('title', translate('Edit Attribute Option'))

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
            <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                <img width="20" src="{{asset('public/assets/admin/img/attribute.png')}}" alt="">
                <span class="page-header-title">
                    {{translate('Update')}} {{translate(str_replace('_', ' ', $option->type))}}
                </span>
            </h2>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{route('admin.order-meta.update', [$option->id])}}" method="post">
                            @csrf
                            <div class="form-group">
                                <label class="input-label">{{translate('Name')}}</label>
                                <input type="text" name="name" value="{{$option->name}}" class="form-control" required>
                            </div>
                            <div class="d-flex justify-content-end gap-3">
                                <button type="reset" class="btn btn-secondary">{{translate('reset')}}</button>
                                <button type="submit" class="btn btn-primary">{{translate('update')}}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection