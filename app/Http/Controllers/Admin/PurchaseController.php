<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Supplier;
use App\Models\Product;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\DB;
use App\CentralLogics\Helpers;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $search = $request['search'];
        $query = Purchase::with('supplier');

        if ($search) {
            $query->whereHas('supplier', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })->orWhere('id', $search);
        }

        $purchases = $query->orderBy('created_at', 'desc')->paginate(25);
        return view('admin-views.purchase.index', compact('purchases', 'search'));
    }

    public function create()
    {
        $suppliers = Supplier::orderBy('name')->get();
        $products = Product::orderBy('name')->get();
        // In a real app with many products, we should use AJAX to search products, but for now getting all is fine or we can pass empty and use ajax.
        return view('admin-views.purchase.create', compact('suppliers', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required',
            'product_id' => 'required|array',
            'quantity' => 'required|array',
            'purchase_price' => 'required|array',
        ]);

        DB::beginTransaction();
        try {
            $purchase = new Purchase();
            $purchase->user_id = auth()->user()->id ?? 0; // Assuming admin user
            $purchase->supplier_id = $request->supplier_id;
            $purchase->notes = $request->notes;
            $purchase->payment_method = $request->payment_method;

            // Image upload
            if (!empty($request->file('image'))) {
                $purchase->image = Helpers::upload('purchase/', 'png', $request->file('image'));
            }

            $purchase->status = $request->status ?? 'not_entered';

            $purchase->save();

            $total_amount = 0;

            foreach ($request->product_id as $key => $product_id) {
                if ($product_id) {
                    $qty = $request->quantity[$key];
                    $price = $request->purchase_price[$key];
                    $item_total = $qty * $price;
                    $total_amount += $item_total;

                    $detail = new PurchaseDetail();
                    $detail->purchase_id = $purchase->id;
                    $detail->product_id = $product_id;
                    $detail->quantity = $qty;
                    $detail->purchase_price = $price;
                    $detail->system_price = 0; // Or fetch current system purchase price
                    $detail->total_price = $item_total;
                    $detail->save();

                    // Update product stock if status is entered (or maybe strictly when 'received').
                    // For now, let's assume 'entered' means update stock.
                    if ($purchase->status == 'entered') {
                        $product = Product::find($product_id);
                        $product->total_stock += $qty;
                        // Update purchase price in product master if needed?
                        $product->purchase_price = $price;
                        $product->save();
                    }
                }
            }

            $purchase->total_amount = $total_amount;
            $purchase->due_amount = $total_amount; // Initially fully due until paid via payments? Or partial here?

            // If there's a paid amount field in form (optional quick payment)
            if ($request->paid_amount) {
                $purchase->paid_amount = $request->paid_amount;
                $purchase->due_amount = $total_amount - $request->paid_amount;
            }

            $purchase->save();

            // Handle initial payment record if paid_amount > 0
            if ($purchase->paid_amount > 0) {
                // Create a payment record... 
                // We need to import SupplierPayment model or DB insert
                // For now let's keep it simple.
            }

            DB::commit();
            Toastr::success(translate('Purchase created successfully!'));
            return redirect()->route('admin.purchase.index');

        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error(translate('Failed to create purchase: ') . $e->getMessage());
            return back();
        }
    }

    public function invoice($id)
    {
        $purchase = Purchase::with(['details.product', 'supplier'])->findOrFail($id);
        return view('admin-views.purchase.invoice', compact('purchase'));
    }

    public function delete(Request $request)
    {
        $purchase = Purchase::findOrFail($request->id);
        // If status was 'entered', we might need to rollback stock?
        // For now simple delete.
        $purchase->delete();
        Toastr::success(translate('Purchase deleted!'));
        return back();
    }
    public function edit($id)
    {
        $purchase = Purchase::with(['details'])->findOrFail($id);
        $suppliers = Supplier::orderBy('name')->get();
        $products = Product::orderBy('name')->get();
        return view('admin-views.purchase.edit', compact('purchase', 'suppliers', 'products'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'supplier_id' => 'required',
            'product_id' => 'required|array',
            'quantity' => 'required|array',
            'purchase_price' => 'required|array',
        ]);

        DB::beginTransaction();
        try {
            $purchase = Purchase::findOrFail($id);

            // Revert stock if previously entered
            if ($purchase->status == 'entered') {
                foreach ($purchase->details as $detail) {
                    $product = Product::find($detail->product_id);
                    if ($product) {
                        $product->total_stock -= $detail->quantity;
                        $product->save();
                    }
                }
            }

            $purchase->supplier_id = $request->supplier_id;
            $purchase->notes = $request->notes;
            $purchase->payment_method = $request->payment_method;
            if (!empty($request->file('image'))) {
                $purchase->image = Helpers::upload('purchase/', 'png', $request->file('image'));
            }
            $purchase->status = $request->status;
            $purchase->paid_amount = $request->paid_amount ?? 0;
            $purchase->save();

            // Clear old details
            PurchaseDetail::where('purchase_id', $purchase->id)->delete();

            $total_amount = 0;
            foreach ($request->product_id as $key => $product_id) {
                if ($product_id) {
                    $qty = $request->quantity[$key];
                    $price = $request->purchase_price[$key];
                    $item_total = $qty * $price;
                    $total_amount += $item_total;

                    $detail = new PurchaseDetail();
                    $detail->purchase_id = $purchase->id;
                    $detail->product_id = $product_id;
                    $detail->quantity = $qty;
                    $detail->purchase_price = $price;
                    $detail->system_price = 0;
                    $detail->total_price = $item_total;
                    $detail->save();

                    // Update stock if status is entered
                    if ($purchase->status == 'entered') {
                        $product = Product::find($product_id);
                        if ($product) {
                            $product->total_stock += $qty;
                            $product->purchase_price = $price;
                            $product->save();
                        }
                    }
                }
            }

            $purchase->total_amount = $total_amount;
            $purchase->due_amount = $total_amount - $purchase->paid_amount;
            $purchase->save();

            DB::commit();
            Toastr::success(translate('Purchase updated successfully!'));
            return redirect()->route('admin.purchase.index');

        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error(translate('Failed to update purchase: ') . $e->getMessage());
            return back();
        }
    }
}
