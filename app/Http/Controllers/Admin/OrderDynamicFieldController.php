<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderDynamicField;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrderDynamicFieldController extends Controller
{
    public function __construct(
        private OrderDynamicField $dynamicField
    ) {
    }

    /**
     * Display listing of dynamic fields
     */
    public function index(): View|Factory|Application
    {
        $fields = $this->dynamicField->orderBy('sort_order')->get();
        return view('admin-views.order-dynamic-fields.index', compact('fields'));
    }

    /**
     * Store a new dynamic field
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'field_name' => 'required|string|max:255',
            'field_key' => 'required|string|max:255|unique:order_dynamic_fields,field_key',
            'field_type' => 'required|in:text,textarea,number,date,select,checkbox,radio',
        ]);

        $field = new OrderDynamicField();
        $field->field_name = $request->field_name;
        $field->field_key = $request->field_key;
        $field->field_type = $request->field_type;

        // Handle options for select/radio/checkbox fields
        if (in_array($request->field_type, ['select', 'radio', 'checkbox'])) {
            if ($request->has('field_options') && is_array($request->field_options)) {
                $field->field_options = json_encode(array_filter($request->field_options));
            } else {
                $field->field_options = json_encode([]);
            }
        }

        $field->default_value = $request->default_value;
        $field->is_required = $request->has('is_required') ? 1 : 0;
        $field->is_active = $request->has('is_active') ? 1 : 0;
        $field->sort_order = $request->sort_order ?? 0;
        $field->save();

        Toastr::success(translate('Dynamic field added successfully'));
        return redirect()->route('admin.order-dynamic-fields.index');
    }

    /**
     * Show edit form
     */
    public function edit($id): View|Factory|Application
    {
        $field = $this->dynamicField->findOrFail($id);
        return view('admin-views.order-dynamic-fields.edit', compact('field'));
    }

    /**
     * Update dynamic field
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $request->validate([
            'field_name' => 'required|string|max:255',
            'field_key' => 'required|string|max:255|unique:order_dynamic_fields,field_key,' . $id,
            'field_type' => 'required|in:text,textarea,number,date,select,checkbox,radio',
        ]);

        $field = $this->dynamicField->findOrFail($id);
        $field->field_name = $request->field_name;
        $field->field_key = $request->field_key;
        $field->field_type = $request->field_type;

        // Handle options for select/radio/checkbox fields
        if (in_array($request->field_type, ['select', 'radio', 'checkbox'])) {
            if ($request->has('field_options') && is_array($request->field_options)) {
                $field->field_options = json_encode(array_filter($request->field_options));
            } else {
                $field->field_options = json_encode([]);
            }
        }

        $field->default_value = $request->default_value;
        $field->is_required = $request->has('is_required') ? 1 : 0;
        $field->is_active = $request->has('is_active') ? 1 : 0;
        $field->sort_order = $request->sort_order ?? 0;
        $field->save();

        Toastr::success(translate('Dynamic field updated successfully'));
        return redirect()->route('admin.order-dynamic-fields.index');
    }

    /**
     * Delete dynamic field
     */
    public function delete($id): RedirectResponse
    {
        $field = $this->dynamicField->findOrFail($id);
        $field->delete();

        Toastr::success(translate('Dynamic field deleted successfully'));
        return back();
    }
}
