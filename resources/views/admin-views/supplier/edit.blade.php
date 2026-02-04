@extends('layouts.admin.app')

@section('title', translate('تحديث المورد'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-3">
            <h2 class="text-capitalize mb-0 d-flex align-items-center gap-2">
                <i class="tio-edit"></i>
                {{translate('تحديث بيانات المورد')}}
            </h2>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{route('admin.supplier.update', [$supplier['id']])}}" method="post">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="input-label" for="name">{{translate('الاسم')}} <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" value="{{$supplier['name']}}"
                                    placeholder="{{ translate('اسم المورد') }}" required maxlength="255">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="input-label" for="phone">{{translate('الهاتف')}} <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="phone" class="form-control" value="{{$supplier['phone']}}"
                                    placeholder="{{ translate('رقم الهاتف') }}" required maxlength="20">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="input-label" for="address">{{translate('العنوان')}}</label>
                                <input type="text" name="address" class="form-control" value="{{$supplier['address']}}"
                                    placeholder="{{ translate('العنوان') }}" maxlength="255">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="input-label" for="notes">{{translate('ملاحظات')}}</label>
                                <textarea name="notes" class="form-control" placeholder="{{ translate('ملاحظات') }}"
                                    rows="1">{{$supplier['notes']}}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-3">
                        <button type="reset" class="btn btn-secondary">{{translate('إعادة تعيين')}}</button>
                        <button type="submit" class="btn btn-primary">{{translate('تحديث')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection