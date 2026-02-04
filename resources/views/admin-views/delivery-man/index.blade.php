@extends('layouts.admin.app')

@section('title', translate('Add new delivery-man'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-3">
            <h2 class="text-capitalize mb-0 d-flex align-items-center gap-2">
                <img width="20" src="{{asset('public/assets/admin/img/icons/deliveryman.png')}}"
                    alt="{{ translate('deliveryman') }}">
                {{translate('Add_New_Deliveryman')}}
            </h2>
        </div>

        <form action="{{route('admin.delivery-man.store')}}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="tio-user"></i>
                        {{translate('General_Information')}}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="input-label">{{translate('first')}} {{translate('name')}}</label>
                                <input type="text" name="f_name" class="form-control"
                                    placeholder="{{translate('first')}} {{translate('name')}}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="input-label">{{translate('last')}} {{translate('name')}}</label>
                                <input type="text" name="l_name" class="form-control"
                                    placeholder="{{translate('last')}} {{translate('name')}}" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="input-label">{{translate('phone')}}</label>
                                <input type="text" name="phone" class="form-control"
                                    placeholder="{{ translate('Ex : 017********') }}" required>
                            </div>
                        </div>
                        {{-- Branch field hidden - default value is 'All' (0) --}}
                        <input type="hidden" name="branch_id" value="0">

                        {{-- Identity fields hidden with default values --}}
                        <input type="hidden" name="identity_type" value="nid">
                        <input type="hidden" name="identity_number" value="N/A">
                        
                        {{-- Delivery man image and identity images are now optional in controller --}}
                    </div>
                </div>
            </div>
            {{-- Account Information section hidden --}}
            {{-- Email and Password fields are now nullable in controller --}}

            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-end gap-3">
                        <button type="reset" class="btn btn-secondary">{{translate('reset')}}</button>
                        <button type="submit" class="btn btn-primary">{{translate('submit')}}</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

@endsection

@push('script_2')
    <script src="{{asset('public/assets/admin/js/deliveryman.js')}}"></script>
    {{-- Spartan multi image picker removed - identity images are hidden --}}
@endpush