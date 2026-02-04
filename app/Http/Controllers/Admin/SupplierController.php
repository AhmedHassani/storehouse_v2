<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Supplier;
use Brian2694\Toastr\Facades\Toastr;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::orderBy('created_at', 'desc')->paginate(25);
        return view('admin-views.supplier.index', compact('suppliers'));
    }

    public function list()
    {
        $suppliers = Supplier::orderBy('created_at', 'desc')->paginate(25);
        return view('admin-views.supplier.list', compact('suppliers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'phone' => 'required|unique:suppliers',
        ]);

        $supplier = new Supplier();
        $supplier->name = $request->name;
        $supplier->phone = $request->phone;
        $supplier->address = $request->address;
        $supplier->notes = $request->notes;
        $supplier->save();

        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => translate('Supplier added successfully!'),
                'supplier' => $supplier
            ]);
        }

        Toastr::success(translate('Supplier added successfully!'));
        return back();
    }

    public function edit($id)
    {
        $supplier = Supplier::findOrFail($id);
        return view('admin-views.supplier.edit', compact('supplier'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'phone' => 'required|unique:suppliers,phone,' . $id,
        ]);

        $supplier = Supplier::findOrFail($id);
        $supplier->name = $request->name;
        $supplier->phone = $request->phone;
        $supplier->address = $request->address;
        $supplier->notes = $request->notes;
        $supplier->save();

        Toastr::success(translate('Supplier updated successfully!'));
        return redirect()->route('admin.supplier.add-new');
    }

    public function delete(Request $request)
    {
        $supplier = Supplier::find($request->id);
        $supplier->delete();
        Toastr::success(translate('Supplier removed!'));
        return back();
    }
}
