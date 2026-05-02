<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>{{ translate('فاتورة شراء') }} #{{$purchase['id']}}</title>
    
    {{-- Google Fonts: Cairo --}}
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="{{asset('assets/admin/css/vendor.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/admin/css/bootstrap.min.css')}}">

    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background-color: white !important;
            color: #334155;
            -webkit-print-color-adjust: exact;
        }

        .invoice-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 30px;
        }

        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .invoice-title {
            color: #1e293b;
            font-weight: 700;
            margin: 0;
            font-size: 28px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 40px;
        }

        .info-card h3 {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
            border-bottom: 2px solid #3b82f6;
            display: inline-block;
            margin-bottom: 15px;
            padding-bottom: 5px;
        }

        .info-item {
            margin-bottom: 8px;
            display: flex;
            gap: 10px;
            font-size: 14px;
        }

        .info-label {
            font-weight: 600;
            color: #64748b;
            min-width: 100px;
        }

        .info-value {
            color: #1e293b;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .table thead th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: 700;
            text-align: right;
            padding: 12px 15px;
            border-bottom: 2px solid #e2e8f0;
            font-size: 14px;
        }

        .table tbody td {
            padding: 12px 15px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 14px;
            color: #334155;
        }

        .table tbody tr:nth-child(even) {
            background-color: #fcfdfe;
        }

        .totals-section {
            display: flex;
            justify-content: flex-end;
        }

        .totals-card {
            width: 300px;
            background-color: #f8fafc;
            padding: 20px;
            border-radius: 8px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 15px;
        }

        .total-row.grand-total {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid #e2e8f0;
            color: #0f172a;
            font-weight: 700;
            font-size: 18px;
        }

        .total-row.due-amount {
            color: #ef4444;
            font-weight: 700;
        }

        .notes-section {
            margin-top: 40px;
            padding: 15px;
            background-color: #fff9f0;
            border-right: 4px solid #f59e0b;
            border-radius: 4px;
        }

        .notes-title {
            font-weight: 700;
            margin-bottom: 5px;
            color: #92400e;
            font-size: 14px;
        }

        @media print {
            body { padding: 0; margin: 0; }
            .invoice-container { padding: 0; max-width: 100%; }
            .no-print { display: none; }
        }
    </style>
</head>

<body>
    <div class="invoice-container">
        {{-- Header --}}
        <div class="header-section">
            <div>
                @php($logo = Helpers::get_business_settings('logo'))
                <img width="140" src="{{Helpers::onErrorImage(
                    $logo,
                    asset('storage/ecommerce') . '/' . $logo,
                    asset('assets/admin/img/160x160/img2.jpg'),
                    'ecommerce/'
                )}}" alt="logo">
            </div>
            <div class="text-left">
                <h1 class="invoice-title">{{ translate('فاتورة شراء') }}</h1>
                <div class="mt-2 text-muted">#{{$purchase['id']}}</div>
            </div>
        </div>

        {{-- Info Grid --}}
        <div class="info-grid">
            {{-- Supplier Info (Right in RTL, User said Left but typically in RTL it's better structured) --}}
            {{-- User requested: Supplier (Left), General (Right). In RTL this means General (Right) and Supplier (Left) --}}
            <div class="info-card">
                <h3>{{ translate('معلومات المورد') }}</h3>
                @if($purchase->supplier)
                    <div class="info-item">
                        <span class="info-label">{{ translate('الاسم') }}:</span>
                        <span class="info-value">{{$purchase->supplier['name']}}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">{{ translate('الهاتف') }}:</span>
                        <span class="info-value" dir="ltr">{{$purchase->supplier['phone']}}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">{{ translate('العنوان') }}:</span>
                        <span class="info-value">{{$purchase->supplier['address']}}</span>
                    </div>
                @else
                    <div class="text-danger">{{ translate('المورد غير موجود') }}</div>
                @endif
            </div>

            <div class="info-card">
                <h3>{{ translate('المعلومات العامة') }}</h3>
                <div class="info-item">
                    <span class="info-label">{{ translate('التاريخ') }}:</span>
                    <span class="info-value">{{$purchase->created_at->format('d M, Y h:i A')}}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">{{ translate('طريقة الدفع') }}:</span>
                    <span class="info-value">{{translate($purchase->payment_method)}}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">{{ translate('حالة المخزن') }}:</span>
                    <span class="info-value">
                        @if($purchase->status == 'entered')
                            <span class="text-success">{{translate('تم الإدخال')}}</span>
                        @else
                            <span class="text-warning">{{translate('قيد الانتظار')}}</span>
                        @endif
                    </span>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <table class="table">
            <thead>
                <tr>
                    <th width="50">{{ translate('ت') }}</th>
                    <th>{{ translate('وصف المنتج') }}</th>
                    <th width="120">{{ translate('سعر الوحدة') }}</th>
                    <th width="80">{{ translate('الكمية') }}</th>
                    <th width="120" class="text-left">{{ translate('الإجمالي') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchase->details as $detail)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        @if($detail->product)
                            <strong>{{$detail->product->name}}</strong>
                        @else
                            <span class="text-danger">{{translate('منتج محذوف')}}</span>
                        @endif
                    </td>
                    <td>{{ Helpers::set_symbol($detail['purchase_price']) }}</td>
                    <td>{{$detail['quantity']}}</td>
                    <td class="text-left">
                        {{ Helpers::set_symbol($detail['total_price']) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Totals --}}
        <div class="totals-section">
            <div class="totals-card">
                <div class="total-row">
                    <span>{{ translate('المجموع الفرعي') }}:</span>
                    <span>{{ Helpers::set_symbol($purchase['total_amount']) }}</span>
                </div>
                <div class="total-row">
                    <span>{{ translate('المبلغ المدفوع') }}:</span>
                    <span>{{ Helpers::set_symbol($purchase['paid_amount']) }}</span>
                </div>
                <div class="total-row grand-total">
                    <span>{{ translate('الإجمالي الكلي') }}:</span>
                    <span>{{ Helpers::set_symbol($purchase['total_amount']) }}</span>
                </div>
                <div class="total-row due-amount">
                    <span>{{ translate('المبلغ المستحق') }}:</span>
                    <span>{{ Helpers::set_symbol($purchase['due_amount']) }}</span>
                </div>
            </div>
        </div>
        
        {{-- Notes --}}
        @if($purchase->notes)
        <div class="notes-section">
            <div class="notes-title">{{ translate('ملاحظات إضافية') }}:</div>
            <div style="font-size: 13px; line-height: 1.6;">{{ $purchase->notes }}</div>
        </div>
        @endif

    </div>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>

</html>
