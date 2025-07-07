<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>أمر البيع - {{ $order->order_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            font-size: 14px;
            line-height: 1.6;
            color: #333;
            background: white;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 20px;
        }
        
        .company-name {
            font-size: 28px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 5px;
        }
        
        .company-info {
            font-size: 12px;
            color: #666;
            margin-bottom: 15px;
        }
        
        .document-title {
            font-size: 24px;
            font-weight: bold;
            color: #1f2937;
            margin-top: 15px;
        }
        
        .order-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .info-section {
            background: #f8fafc;
            padding: 15px;
            border-radius: 8px;
            border-right: 4px solid #2563eb;
        }
        
        .info-title {
            font-size: 16px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 10px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 5px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        
        .info-label {
            font-weight: bold;
            color: #4b5563;
        }
        
        .info-value {
            color: #1f2937;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .items-table th {
            background: #2563eb;
            color: white;
            padding: 12px 8px;
            text-align: center;
            font-weight: bold;
            border: 1px solid #1d4ed8;
        }
        
        .items-table td {
            padding: 10px 8px;
            text-align: center;
            border: 1px solid #e5e7eb;
            background: white;
        }
        
        .items-table tbody tr:nth-child(even) {
            background: #f8fafc;
        }
        
        .items-table tbody tr:hover {
            background: #e0f2fe;
        }
        
        .product-name {
            text-align: right;
            font-weight: bold;
        }
        
        .product-name-ar {
            font-size: 12px;
            color: #666;
            font-weight: normal;
        }
        
        .totals {
            background: #f8fafc;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            margin-bottom: 30px;
        }
        
        .totals-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            padding: 5px 0;
        }
        
        .total-label {
            font-weight: bold;
            color: #4b5563;
        }
        
        .total-value {
            font-weight: bold;
            color: #1f2937;
        }
        
        .grand-total {
            border-top: 2px solid #2563eb;
            padding-top: 10px;
            margin-top: 10px;
        }
        
        .grand-total .total-label,
        .grand-total .total-value {
            font-size: 18px;
            color: #2563eb;
        }
        
        .notes {
            background: #fffbeb;
            border: 1px solid #fbbf24;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 30px;
        }
        
        .notes-title {
            font-weight: bold;
            color: #92400e;
            margin-bottom: 8px;
        }
        
        .notes-content {
            color: #78350f;
            line-height: 1.5;
        }
        
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 12px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-draft { background: #f3f4f6; color: #374151; }
        .status-confirmed { background: #dbeafe; color: #1e40af; }
        .status-processing { background: #fef3c7; color: #92400e; }
        .status-shipped { background: #e0e7ff; color: #3730a3; }
        .status-delivered { background: #d1fae5; color: #065f46; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }
        
        @media print {
            body { font-size: 12px; }
            .container { padding: 10px; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="company-name">MaxCon ERP</div>
            <div class="company-info">
                نظام إدارة الموارد المؤسسية للصيدليات<br>
                العراق - بغداد | الهاتف: +964-XXX-XXXX | البريد: info@maxcon-erp.com
            </div>
            <div class="document-title">أمر البيع</div>
        </div>

        <!-- Order Information -->
        <div class="order-info">
            <div class="info-section">
                <div class="info-title">معلومات الأمر</div>
                <div class="info-row">
                    <span class="info-label">رقم الأمر:</span>
                    <span class="info-value">{{ $order->order_number }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">تاريخ الأمر:</span>
                    <span class="info-value">{{ $order->order_date->format('Y/m/d') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">تاريخ التسليم:</span>
                    <span class="info-value">{{ $order->delivery_date ? $order->delivery_date->format('Y/m/d') : 'غير محدد' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">الحالة:</span>
                    <span class="info-value">
                        <span class="status-badge status-{{ $order->status }}">
                            @switch($order->status)
                                @case('draft') مسودة @break
                                @case('confirmed') مؤكد @break
                                @case('processing') قيد المعالجة @break
                                @case('shipped') تم الشحن @break
                                @case('delivered') تم التسليم @break
                                @case('cancelled') ملغي @break
                                @default {{ $order->status }}
                            @endswitch
                        </span>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">طريقة الدفع:</span>
                    <span class="info-value">
                        @switch($order->payment_method)
                            @case('cash') نقداً @break
                            @case('credit') آجل @break
                            @case('card') بطاقة @break
                            @case('transfer') تحويل @break
                            @default {{ $order->payment_method }}
                        @endswitch
                    </span>
                </div>
            </div>

            <div class="info-section">
                <div class="info-title">معلومات العميل</div>
                <div class="info-row">
                    <span class="info-label">اسم العميل:</span>
                    <span class="info-value">{{ $order->customer->name_ar ?: $order->customer->name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">رمز العميل:</span>
                    <span class="info-value">{{ $order->customer->code }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">الهاتف:</span>
                    <span class="info-value">{{ $order->customer->phone ?: $order->customer->mobile }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">العنوان:</span>
                    <span class="info-value">{{ $order->customer->address }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">المدينة:</span>
                    <span class="info-value">{{ $order->customer->city }}</span>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 5%">#</th>
                    <th style="width: 35%">المنتج</th>
                    <th style="width: 10%">الكمية</th>
                    <th style="width: 12%">سعر الوحدة</th>
                    <th style="width: 10%">الخصم</th>
                    <th style="width: 12%">المجموع</th>
                    <th style="width: 16%">رقم الدفعة / الانتهاء</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="product-name">
                        <div>{{ $item->product->name }}</div>
                        @if($item->product->name_ar)
                            <div class="product-name-ar">{{ $item->product->name_ar }}</div>
                        @endif
                        <small style="color: #666;">{{ $item->product->sku }}</small>
                    </td>
                    <td>{{ number_format($item->quantity) }}</td>
                    <td>{{ number_format($item->unit_price, 2) }} د.ع</td>
                    <td>
                        @if($item->discount_percentage > 0)
                            {{ $item->discount_percentage }}%<br>
                            <small>({{ number_format($item->discount_amount, 2) }} د.ع)</small>
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ number_format($item->total_amount, 2) }} د.ع</td>
                    <td>
                        @if($item->batch_number)
                            <div><strong>{{ $item->batch_number }}</strong></div>
                        @endif
                        @if($item->expiry_date)
                            <small>{{ $item->expiry_date->format('Y/m/d') }}</small>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals">
            <div class="totals-grid">
                <div>
                    <div class="total-row">
                        <span class="total-label">المجموع الفرعي:</span>
                        <span class="total-value">{{ number_format($order->subtotal, 2) }} د.ع</span>
                    </div>
                    <div class="total-row">
                        <span class="total-label">الخصم:</span>
                        <span class="total-value">{{ number_format($order->discount_amount, 2) }} د.ع</span>
                    </div>
                </div>
                <div>
                    <div class="total-row">
                        <span class="total-label">الضريبة:</span>
                        <span class="total-value">{{ number_format($order->tax_amount, 2) }} د.ع</span>
                    </div>
                    <div class="total-row">
                        <span class="total-label">الشحن:</span>
                        <span class="total-value">{{ number_format($order->shipping_amount, 2) }} د.ع</span>
                    </div>
                </div>
            </div>
            <div class="total-row grand-total">
                <span class="total-label">المجموع الإجمالي:</span>
                <span class="total-value">{{ number_format($order->total_amount, 2) }} د.ع</span>
            </div>
        </div>

        <!-- Notes -->
        @if($order->notes)
        <div class="notes">
            <div class="notes-title">ملاحظات:</div>
            <div class="notes-content">{{ $order->notes }}</div>
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p>تم إنشاء هذا الأمر بواسطة: {{ $order->creator->name }} في {{ $order->created_at->format('Y/m/d H:i') }}</p>
            <p>المستودع: {{ $order->warehouse->name_ar ?: $order->warehouse->name }}</p>
            @if($order->salesRep)
                <p>مندوب المبيعات: {{ $order->salesRep->name }}</p>
            @endif
            <p style="margin-top: 15px;">MaxCon ERP - نظام إدارة الموارد المؤسسية</p>
        </div>
    </div>

    <script>
        // Auto print when page loads
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
