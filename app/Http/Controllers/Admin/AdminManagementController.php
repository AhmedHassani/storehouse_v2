<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Permission;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\CentralLogics\Helpers;

class AdminManagementController extends Controller
{
    public function index()
    {
        $admins = Admin::with('permissions')->where('id', '!=', auth('admin')->id())->get();
        // Exclude self? Or show all. Usually good to show all but maybe unrelated logic. 
        // Showing all is fine.
        $admins = Admin::with('permissions')->get();
        return view('admin-views.admin.list', compact('admins'));
    }

    public function create()
    {
        $permissions = Permission::where('status', 1)->orderBy('module')->get()->groupBy('module');
        return view('admin-views.admin.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'username' => 'required|unique:admins',
            'email' => 'required|email|unique:admins',
            'phone' => 'required',
            'password' => 'required|min:6',
            'permissions' => 'array'
        ]);

        $admin = new Admin();
        $admin->name = $request->name;
        $admin->username = $request->username;
        $admin->email = $request->email;
        $admin->phone = $request->phone;
        $admin->password = Hash::make($request->password);
        $admin->status = $request->status ?? 1;

        if ($request->hasFile('image')) {
            $admin->image = Helpers::upload('admin/', 'png', $request->file('image'));
        }

        $admin->save();

        if ($request->has('permissions')) {
            $admin->permissions()->sync($request->permissions);
        }

        Toastr::success(translate('Admin added successfully!'));
        return redirect()->route('admin.management.index');
    }

    public function edit($id)
    {
        $admin = Admin::with('permissions')->findOrFail($id);
        $permissions = Permission::where('status', 1)->orderBy('module')->get()->groupBy('module');
        return view('admin-views.admin.edit', compact('admin', 'permissions'));
    }

    public function update(Request $request, $id)
    {
        $admin = Admin::findOrFail($id);

        $request->validate([
            'name' => 'required',
            'username' => 'required|unique:admins,username,' . $admin->id,
            'email' => 'required|email|unique:admins,email,' . $admin->id,
            'phone' => 'required',
        ]);

        $admin->name = $request->name;
        $admin->username = $request->username;
        $admin->email = $request->email;
        $admin->phone = $request->phone;

        if ($request->password) {
            $admin->password = Hash::make($request->password);
        }

        // Don't accidentally set status to null if checkbox not sent? 
        // Form usually sends status if checked. We should handle it carefully.
        // Assuming status is sent as 1 or 0, or we handle toggle separately.
        // User asked for "Status (Active/Inactive)" in add/edit fields.
        if ($request->has('status')) {
            $admin->status = $request->status;
        }

        if ($request->hasFile('image')) {
            $admin->image = Helpers::upload('admin/', 'png', $request->file('image'));
        }

        $admin->save();

        if ($request->has('permissions')) {
            $admin->permissions()->sync($request->permissions);
        } else {
            $admin->permissions()->detach();
        }

        Toastr::success(translate('Admin updated successfully!'));
        return redirect()->route('admin.management.index');
    }

    public function status(Request $request)
    {
        $admin = Admin::findOrFail($request->id);
        $admin->status = $request->status;
        $admin->save();
        Toastr::success(translate('Status updated!'));
        return back();
    }

    public function delete(Request $request)
    {
        $admin = Admin::findOrFail($request->id);
        if ($admin->id == 1) { // Prevent deleting super admin if ID 1
            Toastr::warning(translate('Cannot delete main admin!'));
            return back();
        }
        $admin->delete();
        Toastr::success(translate('Admin deleted!'));
        return back();
    }
}
