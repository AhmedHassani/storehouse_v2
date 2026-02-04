<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Models\Purchase;
use Brian2694\Toastr\Facades\Toastr;
use App\CentralLogics\Helpers;

class SupplierPaymentController extends Controller
{
    public function index(Request $request)
    {
        $supplier_id = $request->supplier_id;
        $suppliers = Supplier::orderBy('name')->get();

        $total_purchase = 0;
        $total_paid_all = 0; // Total paid via payments + paid on purchase creation
        $balance_due = 0;

        $payments = SupplierPayment::with('supplier');

        if ($supplier_id) {
            $payments = $payments->where('supplier_id', $supplier_id);

            // Calculate totals for selected supplier
            $purchases = Purchase::where('supplier_id', $supplier_id)->get();
            $total_purchase = $purchases->sum('total_amount');

            // Paid on purchase creation
            $paid_on_purchase = $purchases->sum('paid_amount');

            // Paid via Payment Portal
            $paid_via_portal = SupplierPayment::where('supplier_id', $supplier_id)->sum('amount');

            $total_paid_all = $paid_on_purchase + $paid_via_portal;
            $balance_due = $total_purchase - $total_paid_all;
        }

        $payments = $payments->orderBy('payment_date', 'desc')->paginate(20);

        return view('admin-views.supplier-payment.index', compact('suppliers', 'payments', 'total_purchase', 'total_paid_all', 'balance_due', 'supplier_id'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
        ]);

        $payment = new SupplierPayment();
        $payment->supplier_id = $request->supplier_id;
        $payment->amount = $request->amount;
        $payment->payment_date = $request->payment_date;
        $payment->notes = $request->notes;

        if ($request->hasFile('image')) {
            $payment->image = Helpers::upload('supplier_payment/', 'png', $request->file('image'));
        }

        $payment->save();

        // Optional: Auto-allocate payment to oldest due purchase?
        // For now, we just record payment on account.

        Toastr::success(translate('Payment recorded successfully!'));
        return back();
    }

    public function delete(Request $request)
    {
        $payment = SupplierPayment::findOrFail($request->id);
        Helpers::delete('supplier_payment/' . $payment['image']);
        $payment->delete();
        Toastr::success(translate('Payment deleted!'));
        return back();
    }
}
