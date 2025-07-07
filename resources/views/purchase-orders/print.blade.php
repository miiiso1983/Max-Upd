<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>طلب الشراء {{ $purchaseOrder->order_number }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            background: white;
            color: #333;
            direction: rtl;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .company-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .document-title {
            font-size: 20px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 5px;
        }
        
        .order-number {
            font-size: 16px;
            color: #666;
        }
        
        .info-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        
        .info-box {
            width: 48%;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
        }
        
        .info-box h3 {
            margin: 0 0 15px 0;
            font-size: 16px;
            color: #2563eb;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        
        .info-label {
            font-weight: bold;
            color: #555;
        }
        
        .info-value {
            color: #333;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        
        .items-table th,
        .items-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: right;
        }
        
        .items-table th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #2563eb;
        }
        
        .items-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .totals-section {
            width: 300px;
            margin-left: auto;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        
        .total-row.final {
            border-top: 2px solid #333;
            padding-top: 8px;
            font-weight: bold;
            font-size: 16px;
        }
        
        .footer {
            margin-top: 50px;
            border-top: 1px solid #ddd;
            padding-top: 20px;
            text-align: center;
            color: #666;
            font-size: 12px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .status-pending { background-color: #fef3c7; color: #92400e; }
        .status-approved { background-color: #dbeafe; color: #1e40af; }
        .status-ordered { background-color: #e0e7ff; color: #5b21b6; }
        .status-partially_received { background-color: #fed7aa; color: #ea580c; }
        .status-completed { background-color: #dcfce7; color: #166534; }
        .status-cancelled { background-color: #fecaca; color: #dc2626; }
        
        @media print {
            body { margin: 0; padding: 15px; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <!-- Print Button (hidden when printing) -->
    <div class="no-print" style="text-align: center; margin-bottom: 20px;">
        <button onclick="window.print()" style="background: #2563eb; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-family: Arial;">
            🖨️ طباعة
        </button>
        <button onclick="window.close()" style="background: #6b7280; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin-right: 10px; font-family: Arial;">
            ❌ إغلاق
        </button>
    </div>

    <!-- Header -->
    <div class="header">
        <div class="company-name">{{ config('app.name', 'MaxCon ERP') }}</div>
        <div class="document-title">طلب شراء</div>
        <div class="order-number">رقم الطلب: {{ $purchaseOrder->order_number }}</div>
    </div>

    <!-- Order Information -->
    <div class="info-section">
        <!-- Order Details -->
        <div class="info-box">
            <h3>تفاصيل الطلب</h3>
            <div class="info-row">
                <span class="info-label">رقم الطلب:</span>
                <span class="info-value">{{ $purchaseOrder->order_number }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">تاريخ الطلب:</span>
                <span class="info-value">{{ $purchaseOrder->order_date->format('Y-m-d') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">تاريخ التسليم المتوقع:</span>
                <span class="info-value">{{ $purchaseOrder->expected_delivery_date ? $purchaseOrder->expected_delivery_date->format('Y-m-d') : 'غير محدد' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">الحالة:</span>
                <span class="info-value">
                    @switch($purchaseOrder->status)
                        @case('pending')
                            <span class="status-badge status-pending">في الانتظار</span>
                            @break
                        @case('approved')
                            <span class="status-badge status-approved">معتمد</span>
                            @break
                        @case('ordered')
                            <span class="status-badge status-ordered">مطلوب</span>
                            @break
                        @case('partially_received')
                            <span class="status-badge status-partially_received">مستلم جزئياً</span>
                            @break
                        @case('completed')
                            <span class="status-badge status-completed">مكتمل</span>
                            @break
                        @case('cancelled')
                            <span class="status-badge status-cancelled">ملغي</span>
                            @break
                        @default
                            <span class="status-badge">{{ $purchaseOrder->status }}</span>
                    @endswitch
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">طريقة الدفع:</span>
                <span class="info-value">
                    @switch($purchaseOrder->payment_method)
                        @case('cash')
                            نقداً
                            @break
                        @case('credit')
                            آجل
                            @break
                        @case('bank_transfer')
                            تحويل بنكي
                            @break
                        @case('check')
                            شيك
                            @break
                        @default
                            {{ $purchaseOrder->payment_method }}
                    @endswitch
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">شروط الدفع:</span>
                <span class="info-value">{{ $purchaseOrder->payment_terms ?? 0 }} يوم</span>
            </div>
        </div>

        <!-- Supplier Information -->
        <div class="info-box">
            <h3>معلومات المورد</h3>
            <div class="info-row">
                <span class="info-label">اسم المورد:</span>
                <span class="info-value">{{ $purchaseOrder->supplier->name }}</span>
            </div>
            @if($purchaseOrder->supplier->name_ar)
            <div class="info-row">
                <span class="info-label">الاسم بالعربية:</span>
                <span class="info-value">{{ $purchaseOrder->supplier->name_ar }}</span>
            </div>
            @endif
            <div class="info-row">
                <span class="info-label">نوع المورد:</span>
                <span class="info-value">
                    @switch($purchaseOrder->supplier->type)
                        @case('manufacturer')
                            مصنع
                            @break
                        @case('distributor')
                            موزع
                            @break
                        @case('wholesaler')
                            تاجر جملة
                            @break
                        @default
                            {{ $purchaseOrder->supplier->type }}
                    @endswitch
                </span>
            </div>
            @if($purchaseOrder->supplier->email)
            <div class="info-row">
                <span class="info-label">البريد الإلكتروني:</span>
                <span class="info-value">{{ $purchaseOrder->supplier->email }}</span>
            </div>
            @endif
            @if($purchaseOrder->supplier->phone)
            <div class="info-row">
                <span class="info-label">الهاتف:</span>
                <span class="info-value">{{ $purchaseOrder->supplier->phone }}</span>
            </div>
            @endif
            @if($purchaseOrder->supplier->address)
            <div class="info-row">
                <span class="info-label">العنوان:</span>
                <span class="info-value">{{ $purchaseOrder->supplier->address }}</span>
            </div>
            @endif
        </div>
    </div>

    <!-- Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 30%;">المنتج</th>
                <th style="width: 15%;">الكمية المطلوبة</th>
                <th style="width: 15%;">سعر الوحدة</th>
                <th style="width: 10%;">الخصم</th>
                <th style="width: 15%;">الإجمالي</th>
                <th style="width: 10%;">الحالة</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchaseOrder->items as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>
                    <strong>{{ $item->product->name }}</strong><br>
                    @if($item->product->name_ar)
                        <small>{{ $item->product->name_ar }}</small><br>
                    @endif
                    <small style="color: #666;">SKU: {{ $item->product->sku }}</small>
                </td>
                <td>{{ number_format($item->quantity) }} {{ $item->product->unit_of_measure }}</td>
                <td>{{ number_format($item->unit_cost, 2) }} د.ع</td>
                <td>
                    @if($item->discount_amount > 0)
                        {{ number_format($item->discount_amount, 2) }} د.ع
                    @elseif($item->discount_percentage > 0)
                        {{ number_format($item->discount_percentage, 1) }}%
                    @else
                        -
                    @endif
                </td>
                <td>{{ number_format($item->total_amount, 2) }} د.ع</td>
                <td>
                    @if($item->is_fully_received)
                        <span style="color: #166534;">مستلم بالكامل</span>
                    @elseif($item->total_received > 0)
                        <span style="color: #ea580c;">مستلم جزئياً</span>
                    @else
                        <span style="color: #6b7280;">لم يستلم بعد</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totals -->
    <div class="totals-section">
        <div class="total-row">
            <span>المجموع الفرعي:</span>
            <span>{{ number_format($purchaseOrder->subtotal, 2) }} د.ع</span>
        </div>
        @if($purchaseOrder->discount_amount > 0)
        <div class="total-row">
            <span>الخصم:</span>
            <span style="color: #dc2626;">-{{ number_format($purchaseOrder->discount_amount, 2) }} د.ع</span>
        </div>
        @endif
        @if($purchaseOrder->tax_amount > 0)
        <div class="total-row">
            <span>الضريبة:</span>
            <span>{{ number_format($purchaseOrder->tax_amount, 2) }} د.ع</span>
        </div>
        @endif
        @if($purchaseOrder->shipping_amount > 0)
        <div class="total-row">
            <span>الشحن:</span>
            <span>{{ number_format($purchaseOrder->shipping_amount, 2) }} د.ع</span>
        </div>
        @endif
        <div class="total-row final">
            <span>الإجمالي:</span>
            <span>{{ number_format($purchaseOrder->total_amount, 2) }} د.ع</span>
        </div>
    </div>

    <!-- Notes -->
    @if($purchaseOrder->notes)
    <div style="margin-top: 30px; border: 1px solid #ddd; padding: 15px; border-radius: 5px;">
        <h3 style="margin: 0 0 10px 0; color: #2563eb;">ملاحظات:</h3>
        <p style="margin: 0; line-height: 1.6;">{{ $purchaseOrder->notes }}</p>
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>تم إنشاء هذا الطلب بواسطة: {{ $purchaseOrder->creator->name ?? 'غير محدد' }}</p>
        <p>تاريخ الطباعة: {{ now()->format('Y-m-d H:i:s') }}</p>
        <p>{{ config('app.name', 'MaxCon ERP') }} - نظام إدارة موارد المؤسسات</p>
    </div>

    <script>
        // Auto print when page loads (optional)
        // window.onload = function() { window.print(); }

        // Add keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl+P for print
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                window.print();
            }
            // Escape to close
            if (e.key === 'Escape') {
                window.close();
            }
        });

        // Improve print experience
        window.addEventListener('beforeprint', function() {
            document.title = 'طلب الشراء {{ $purchaseOrder->order_number }}';
        });
    </script>
</body>
</html>
