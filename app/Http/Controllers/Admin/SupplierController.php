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

    public function show(Request $request, $id)
    {
        $supplier = Supplier::with(['purchases', 'payments'])->findOrFail($id);

        $from_date = $request->get('from_date');
        $to_date = $request->get('to_date');

        // Get purchases with date filter
        $purchasesQuery = $supplier->purchases();
        if ($from_date) {
            $purchasesQuery->whereDate('created_at', '>=', $from_date);
        }
        if ($to_date) {
            $purchasesQuery->whereDate('created_at', '<=', $to_date);
        }
        $purchases = $purchasesQuery->orderBy('created_at', 'desc')->get();

        // Get payments with date filter
        $paymentsQuery = $supplier->payments();
        if ($from_date) {
            $paymentsQuery->whereDate('payment_date', '>=', $from_date);
        }
        if ($to_date) {
            $paymentsQuery->whereDate('payment_date', '<=', $to_date);
        }
        $payments = $paymentsQuery->orderBy('payment_date', 'desc')->get();

        // Calculate totals
        $total_purchase = $purchases->sum('total_amount');
        $paid_on_purchase = $purchases->sum('paid_amount');
        $paid_via_portal = $payments->sum('amount');
        $total_paid = $paid_on_purchase + $paid_via_portal;
        $balance_due = $total_purchase - $total_paid;

        // Combine purchases and payments for timeline
        $timeline = collect();

        // Add purchases to timeline
        foreach ($purchases as $purchase) {
            $timeline->push([
                'type' => 'purchase',
                'date' => $purchase->created_at,
                'data' => $purchase,
            ]);
        }

        // Add payments to timeline
        foreach ($payments as $payment) {
            $timeline->push([
                'type' => 'payment',
                'date' => $payment->payment_date,
                'data' => $payment,
            ]);
        }

        // Sort timeline by date (newest first)
        $timeline = $timeline->sortByDesc('date');

        // Paginate timeline
        $perPage = 20;
        $currentPage = $request->get('page', 1);
        $timelineItems = $timeline->forPage($currentPage, $perPage);

        // Create paginator
        $timelinePaginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $timelineItems,
            $timeline->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('admin-views.supplier.show', compact(
            'supplier',
            'timelinePaginated',
            'total_purchase',
            'total_paid',
            'balance_due',
            'paid_on_purchase',
            'paid_via_portal',
            'from_date',
            'to_date'
        ));
    }

    public function exportTimeline(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);

        $from_date = $request->get('from_date');
        $to_date = $request->get('to_date');

        // Get purchases with date filter
        $purchasesQuery = $supplier->purchases();
        if ($from_date) {
            $purchasesQuery->whereDate('created_at', '>=', $from_date);
        }
        if ($to_date) {
            $purchasesQuery->whereDate('created_at', '<=', $to_date);
        }
        $purchases = $purchasesQuery->orderBy('created_at', 'desc')->get();

        // Get payments with date filter
        $paymentsQuery = $supplier->payments();
        if ($from_date) {
            $paymentsQuery->whereDate('payment_date', '>=', $from_date);
        }
        if ($to_date) {
            $paymentsQuery->whereDate('payment_date', '<=', $to_date);
        }
        $payments = $paymentsQuery->orderBy('payment_date', 'desc')->get();

        // Combine timeline
        $timeline = collect();
        foreach ($purchases as $purchase) {
            $timeline->push([
                'type' => 'مشتريات',
                'date' => $purchase->created_at->format('Y-m-d H:i'),
                'total_amount' => $purchase->total_amount,
                'paid_amount' => $purchase->paid_amount,
                'notes' => $purchase->notes ?? '-',
            ]);
        }
        foreach ($payments as $payment) {
            $timeline->push([
                'type' => 'دفعة',
                'date' => \Carbon\Carbon::parse($payment->payment_date)->format('Y-m-d'),
                'total_amount' => '-',
                'paid_amount' => $payment->amount,
                'notes' => $payment->notes ?? '-',
            ]);
        }
        $timeline = $timeline->sortByDesc('date')->values();

        // Create Excel
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="supplier_' . $supplier->id . '_timeline_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($timeline, $supplier) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM

            // Header
            fputcsv($file, ['المورد: ' . $supplier->name]);
            fputcsv($file, ['الهاتف: ' . $supplier->phone]);
            fputcsv($file, ['']);
            fputcsv($file, ['النوع', 'التاريخ', 'المبلغ الإجمالي', 'المبلغ المدفوع', 'الملاحظات']);

            // Data
            foreach ($timeline as $item) {
                fputcsv($file, [
                    $item['type'],
                    $item['date'],
                    $item['total_amount'],
                    $item['paid_amount'],
                    $item['notes'],
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function delete(Request $request)
    {
        $supplier = Supplier::find($request->id);
        $supplier->delete();
        Toastr::success(translate('Supplier removed!'));
        return back();
    }
}
