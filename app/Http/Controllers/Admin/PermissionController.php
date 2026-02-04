<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::orderBy('module')->get();
        // Group by module for display if needed, but for list we can just list them.
        return view('admin-views.system.permission.index', compact('permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'key' => 'required|unique:permissions,key',
            'module' => 'required',
        ], [
            'name.required' => translate('Name is required'),
            'key.required' => translate('Key is required'),
            'key.unique' => translate('Key must be unique'),
        ]);

        $permission = new Permission();
        $permission->name = $request->name;
        $permission->key = $request->key;
        $permission->module = $request->module;
        $permission->description = $request->description;
        $permission->status = 1;
        $permission->save();

        Toastr::success(translate('Permission created successfully!'));
        return back();
    }

    public function update(Request $request, $id)
    {
        $permission = Permission::findOrFail($id);
        $request->validate([
            'name' => 'required',
            'key' => 'required|unique:permissions,key,' . $permission->id,
            'module' => 'required',
        ]);

        $permission->name = $request->name;
        $permission->key = $request->key;
        $permission->module = $request->module;
        $permission->description = $request->description;
        $permission->save();

        Toastr::success(translate('Permission updated successfully!'));
        return back();
    }

    public function status(Request $request)
    {
        $permission = Permission::findOrFail($request->id);
        $permission->status = $request->status;
        $permission->save();
        Toastr::success(translate('Status updated!'));
        return back();
    }

    public function delete(Request $request)
    {
        $permission = Permission::findOrFail($request->id);
        $permission->delete();
        Toastr::success(translate('Permission deleted successfully!'));
        return back();
    }
}
