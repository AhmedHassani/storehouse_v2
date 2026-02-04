@extends('layouts.admin.app')

@section('title', translate('Edit Dynamic Field'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
            <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                <img width="20" src="{{asset('public/assets/admin/img/attribute.png')}}" alt="">
                <span class="page-header-title">
                    {{translate('Edit Dynamic Field')}}
                </span>
            </h2>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{route('admin.order-dynamic-fields.update', $field->id)}}" method="post">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="input-label">{{translate('Field Name')}} <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="field_name" class="form-control" value="{{$field->field_name}}"
                                        required>
                                </div>

                                <div class="col-md-4">
                                    <label class="input-label">{{translate('Field Key')}} <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="field_key" class="form-control" value="{{$field->field_key}}"
                                        required>
                                </div>

                                <div class="col-md-4">
                                    <label class="input-label">{{translate('Field Type')}} <span
                                            class="text-danger">*</span></label>
                                    <select name="field_type" id="field_type" class="form-control" required>
                                        <option value="text" {{$field->field_type == 'text' ? 'selected' : ''}}>
                                            {{translate('Text Input')}}
                                        </option>
                                        <option value="textarea" {{$field->field_type == 'textarea' ? 'selected' : ''}}>
                                            {{translate('Text Area')}}
                                        </option>
                                        <option value="number" {{$field->field_type == 'number' ? 'selected' : ''}}>
                                            {{translate('Number')}}
                                        </option>
                                        <option value="date" {{$field->field_type == 'date' ? 'selected' : ''}}>
                                            {{translate('Date')}}
                                        </option>
                                        <option value="select" {{$field->field_type == 'select' ? 'selected' : ''}}>
                                            {{translate('Dropdown Select')}}
                                        </option>
                                        <option value="checkbox" {{$field->field_type == 'checkbox' ? 'selected' : ''}}>
                                            {{translate('Checkbox')}}
                                        </option>
                                        <option value="radio" {{$field->field_type == 'radio' ? 'selected' : ''}}>
                                            {{translate('Radio Buttons')}}
                                        </option>
                                    </select>
                                </div>

                                <div class="col-12" id="options_container"
                                    style="display: {{in_array($field->field_type, ['select', 'radio']) ? 'block' : 'none'}};">
                                    <label class="input-label">{{translate('Options')}}</label>
                                    <div id="options_list">
                                        @if($field->options_array)
                                            @foreach($field->options_array as $option)
                                                <div class="input-group mb-2">
                                                    <input type="text" name="field_options[]" class="form-control"
                                                        value="{{$option}}">
                                                    <button type="button" class="btn btn-outline-danger"
                                                        onclick="$(this).closest('.input-group').remove()">
                                                        <i class="tio-delete"></i>
                                                    </button>
                                                </div>
                                            @endforeach
                                        @endif
                                        <div class="input-group mb-2">
                                            <input type="text" name="field_options[]" class="form-control"
                                                placeholder="{{translate('New option')}}">
                                            <button type="button" class="btn btn-outline-success" onclick="addOption()">
                                                <i class="tio-add"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <label class="input-label">{{translate('Default Value')}}</label>
                                    <input type="text" name="default_value" class="form-control"
                                        value="{{$field->default_value}}">
                                </div>

                                <div class="col-md-2">
                                    <label class="input-label">{{translate('Sort Order')}}</label>
                                    <input type="number" name="sort_order" class="form-control"
                                        value="{{$field->sort_order}}" min="0">
                                </div>

                                <div class="col-md-3">
                                    <label class="input-label d-block">{{translate('Options')}}</label>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="is_required"
                                            name="is_required" value="1" {{$field->is_required ? 'checked' : ''}}>
                                        <label class="custom-control-label" for="is_required">
                                            {{translate('Required Field')}}
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <label class="input-label d-block">&nbsp;</label>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active"
                                            value="1" {{$field->is_active ? 'checked' : ''}}>
                                        <label class="custom-control-label" for="is_active">
                                            {{translate('Active')}}
                                        </label>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="d-flex justify-content-end gap-3">
                                        <a href="{{route('admin.order-dynamic-fields.index')}}" class="btn btn-secondary">
                                            {{translate('Cancel')}}
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="tio-save"></i> {{translate('Update Field')}}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
    <script>
        "use strict";

        $('#field_type').on('change', function () {
            let fieldType = $(this).val();
            if (fieldType === 'select' || fieldType === 'radio' || fieldType === 'checkbox') {
                $('#options_container').slideDown();
            } else {
                $('#options_container').slideUp();
            }
        });

        function addOption() {
            let optionHtml = `
                    <div class="input-group mb-2">
                        <input type="text" name="field_options[]" class="form-control" placeholder="{{translate('Option value')}}">
                        <button type="button" class="btn btn-outline-danger" onclick="$(this).closest('.input-group').remove()">
                            <i class="tio-delete"></i>
                        </button>
                    </div>
                `;
            $('#options_list').append(optionHtml);
        }
    </script>
@endpush