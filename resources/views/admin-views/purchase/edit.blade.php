@extends('layouts.admin.app')

@section('title', translate('تعديل الشراء'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-3">
            <h2 class="text-capitalize mb-0 d-flex align-items-center gap-2">
                <i class="tio-add-circle-outlined"></i>
                {{translate('تعديل شراء')}} #{{$purchase->id}}
            </h2>
        </div>

        <form action="{{route('admin.purchase.update', [$purchase['id']])}}" method="post" enctype="multipart/form-data" id="purchase-form">
            @csrf
            <div class="row">
                <div class="col-md-12 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="input-label">{{translate('المورد')}}</label>
                                        <div class="d-flex gap-2">
                                            <select name="supplier_id" id="supplier_id"
                                                class="form-control js-select2-custom" required>
                                                <option value="">{{translate('اختر المورد')}}</option>
                                                @foreach($suppliers as $supplier)
                                                    <option value="{{$supplier->id}}" {{$purchase->supplier_id == $supplier->id ? 'selected' : ''}}>{{$supplier->name}} ({{$supplier->phone}})
                                                    </option>
                                                @endforeach
                                            </select>
                                            <button type="button" class="btn btn-primary" data-toggle="modal"
                                                data-target="#addSupplierModal">
                                                <i class="tio-add"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="input-label">{{translate('تاريخ الشراء')}}</label>
                                        <input type="text" value="{{$purchase->created_at->format('Y-m-d')}}" class="form-control" disabled>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="input-label">{{translate('مرجع / رقم الفاتورة')}}</label>
                                        <input type="text" name="notes" class="form-control" value="{{$purchase->notes}}"
                                            placeholder="{{translate('رقم المرجع / ملاحظات')}}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-12 mb-3">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">{{translate('المنتجات')}}</h5>
                            <button type="button" class="btn btn-primary" onclick="addRow()">
                                <i class="tio-add"></i> {{translate('إضافة منتج')}}
                            </button>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table
                                    class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                                    id="product_table">
                                    <thead class="thead-light">
                                        <tr>
                                            <th style="width: 30%;">{{translate('المنتج')}}</th>
                                            <th style="width: 15%;">{{translate('الكمية')}}</th>
                                            <th style="width: 20%;">{{translate('سعر الوحدة')}}</th>
                                            <th style="width: 20%;">{{translate('المجموع')}}</th>
                                            <th style="width: 15%;" class="text-center">{{translate('إجراء')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="product_tbody">
                                        @foreach($purchase->details as $detail)
                                            <tr>
                                                <td>
                                                    <select name="product_id[]" class="form-control js-select2-custom-dynamic" onchange="getProductData(this)" required>
                                                        <option value="">{{translate('اختر المنتج')}}</option>
                                                        @foreach($products as $product)
                                                            <option value="{{$product->id}}" data-price="{{$product->purchase_price}}" {{$detail->product_id == $product->id ? 'selected' : ''}}>{{$product->name}}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="number" name="quantity[]" class="form-control" min="1" value="{{$detail->quantity}}" onchange="calculateRowTotal(this)" onkeyup="calculateRowTotal(this)" required>
                                                </td>
                                                <td>
                                                    <input type="number" name="purchase_price[]" class="form-control" step="0.01" min="0" value="{{$detail->purchase_price}}" onchange="calculateRowTotal(this)" onkeyup="calculateRowTotal(this)" required>
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control row-total" readonly value="{{$detail->total_price}}">
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">
                                                        <i class="tio-delete"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3" class="text-right font-weight-bold">
                                                {{translate('المبلغ الإجمالي')}}:
                                            </td>
                                            <td colspan="2">
                                                <input type="text" name="total_amount_display" id="total_amount_display"
                                                    class="form-control w-100" readonly value="0">
                                                <input type="hidden" name="total_amount" id="total_amount" value="{{$purchase->total_amount}}">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" class="text-right font-weight-bold">
                                                {{translate('المدفوع')}}:
                                            </td>
                                            <td colspan="2">
                                                <input type="number" name="paid_amount" id="paid_amount"
                                                    class="form-control w-100" min="0" step="0.01" value="{{$purchase->paid_amount}}"
                                                    onkeyup="calculateDue()">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" class="text-right font-weight-bold">{{translate('المتبقي')}}:
                                            </td>
                                            <td colspan="2">
                                                <input type="text" id="due_amount_display" class="form-control w-100"
                                                    readonly value="0">
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="input-label">{{translate('طريقة الدفع')}}</label>
                                        <select name="payment_method" class="form-control">
                                            <option value="cash" {{$purchase->payment_method == 'cash' ? 'selected' : ''}}>{{translate('كاش')}}</option>
                                            <option value="bank" {{$purchase->payment_method == 'bank' ? 'selected' : ''}}>{{translate('تحويل بنكي')}}</option>
                                            <option value="check" {{$purchase->payment_method == 'check' ? 'selected' : ''}}>{{translate('شيك')}}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="input-label">{{translate('الحالة')}}</label>
                                        <select name="status" class="form-control">
                                            <option value="not_entered" {{$purchase->status == 'not_entered' ? 'selected' : ''}}>{{translate('لم يتم الإدخال للمخزون')}}</option>
                                            <option value="entered" {{$purchase->status == 'entered' ? 'selected' : ''}}>{{translate('تم الإدخال للمخزون')}}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="input-label">{{translate('صورة الإيصال')}}</label>
                                        <div class="custom-file">
                                            <input type="file" name="image" id="customFileEg1" class="custom-file-input"
                                                accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                            <label class="custom-file-label" for="customFileEg1">{{translate('اختر')}}
                                                {{translate('ملف')}}</label>
                                        </div>
                                        <div class="text-center mt-3">
                                            <img style="height: 200px;border: 1px solid; border-radius: 10px;" id="viewer"
                                                src="{{$purchase->image ? asset('storage/app/public/purchase/' . $purchase->image) : asset('public/assets/admin/img/400x400/img2.jpg')}}"
                                                alt="receipt image" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">{{translate('تحديث الشراء')}}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Add Supplier Modal -->
    <div class="modal fade" id="addSupplierModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{translate('إضافة مورد جديد')}}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="add_supplier_form">
                        @csrf
                        <div class="form-group">
                            <label>{{translate('الاسم')}}</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>{{translate('الهاتف')}}</label>
                            <input type="text" name="phone" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>{{translate('العنوان')}}</label>
                            <input type="text" name="address" class="form-control">
                        </div>
                        <button type="button" class="btn btn-primary"
                            onclick="submitNewSupplier()">{{translate('حفظ')}}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('script_2')
    <script>
        function readURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    $('#viewer').attr('src', e.target.result);
                }

                reader.readAsDataURL(input.files[0]);
            }
        }

        $("#customFileEg1").change(function () {
            readURL(this);
        });

        // Initialize with one row
        $(document).ready(function () {
            if ($('#product_tbody tr').length == 0) {
                addRow();
            }
            calculateGrandTotal();
        });

        function addRow() {
            let rowCount = $('#product_tbody tr').length;
            let html = `
                        <tr>
                            <td>
                                <select name="product_id[]" class="form-control js-select2-custom-dynamic" onchange="getProductData(this)" required>
                                    <option value="">{{translate('اختر المنتج')}}</option>
                                    @foreach($products as $product)
                                        <option value="{{$product->id}}" data-price="{{$product->purchase_price}}">{{$product->name}}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="number" name="quantity[]" class="form-control" min="1" value="1" onchange="calculateRowTotal(this)" onkeyup="calculateRowTotal(this)" required>
                            </td>
                            <td>
                                <input type="number" name="purchase_price[]" class="form-control" step="0.01" min="0" value="0" onchange="calculateRowTotal(this)" onkeyup="calculateRowTotal(this)" required>
                            </td>
                            <td>
                                <input type="number" class="form-control row-total" readonly value="0">
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">
                                    <i class="tio-delete"></i>
                                </button>
                            </td>
                        </tr>
                    `;
            $('#product_tbody').append(html);
            // Re-initialize select2 for new element if needed.
            // Since standard select2 init might only target existing elements, we might need to target the new one.
            // However, depending on the admin theme's JS, generic class might not auto-upgrade new DOM elements.
            // We'll see. If it fails, we remove js-select2-custom class or re-init.
        }

        function removeRow(btn) {
            $(btn).closest('tr').remove();
            calculateGrandTotal();
        }

        function getProductData(select) {
            let price = $(select).find(':selected').data('price');
            let row = $(select).closest('tr');
            row.find('input[name="purchase_price[]"]').val(price);
            calculateRowTotal(select);
        }

        function calculateRowTotal(element) {
            let row = $(element).closest('tr');
            let qty = parseFloat(row.find('input[name="quantity[]"]').val()) || 0;
            let price = parseFloat(row.find('input[name="purchase_price[]"]').val()) || 0;
            let total = qty * price;
            row.find('.row-total').val(total.toFixed(2));
            calculateGrandTotal();
        }

        function calculateGrandTotal() {
            let grandTotal = 0;
            $('.row-total').each(function () {
                grandTotal += parseFloat($(this).val()) || 0;
            });
            $('#total_amount').val(grandTotal.toFixed(2));
            $('#total_amount_display').val(grandTotal.toFixed(2));
            calculateDue();
        }

        function calculateDue() {
            let total = parseFloat($('#total_amount').val()) || 0;
            let paid = parseFloat($('#paid_amount').val()) || 0;
            let due = total - paid;
            $('#due_amount_display').val(due.toFixed(2));
        }

        function submitNewSupplier() {
            let formData = $('#add_supplier_form').serialize();
            $.ajax({
                url: "{{route('admin.supplier.store')}}",
                type: 'POST',
                data: formData,
                success: function (response) {
                    location.reload(); // Simple reload to see the new supplier in select list. 
                    // Ideally we should append it to the select and close modal, but reload is safer for now.
                },
                error: function (xhr) {
                    // Show error
                    alert('Error adding supplier');
                }
            });
        }
    </script>
@endpush