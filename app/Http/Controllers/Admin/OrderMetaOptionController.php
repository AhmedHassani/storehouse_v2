<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderMetaOption;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;

class OrderMetaOptionController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->get('type', 'sale_channel');
        $options = OrderMetaOption::where('type', $type)->get();
        return view('admin-views.order-meta.index', compact('options', 'type'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|string',
            'name' => 'required|string|max:255',
        ]);

        OrderMetaOption::create([
            'type' => $request->type,
            'name' => $request->name,
        ]);

        Toastr::success(translate('Option added successfully!'));
        return back();
    }

    public function edit($id)
    {
        $option = OrderMetaOption::findOrFail($id);
        return view('admin-views.order-meta.edit', compact('option'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $option = OrderMetaOption::findOrFail($id);
        $option->update([
            'name' => $request->name,
        ]);

        Toastr::success(translate('Option updated successfully!'));
        return redirect()->route('admin.order-meta.index', ['type' => $option->type]);
    }

    public function delete($id)
    {
        $option = OrderMetaOption::findOrFail($id);
        $option->delete();

        Toastr::success(translate('Option deleted successfully!'));
        return back();
    }
}
