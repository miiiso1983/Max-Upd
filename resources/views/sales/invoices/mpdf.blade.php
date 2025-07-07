<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <title>فاتورة رقم {{ $invoice->invoice_number ?? 'INV-001' }}</title>
    <style>
        @page {
            margin: 15mm;
            size: A4 portrait;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "DejaVu Sans", Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
            direction: rtl;
            text-align: right;
        }

        .invoice-container {
            max-width: 100%;
            margin: 0 auto;
            background: white;
        }

        /* Header Styles */
        .invoice-header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            position: relative;
            overflow: hidden;
        }

        .invoice-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.1;
            z-index: 1;
        }

        .header-content {
            position: relative;
            z-index: 2;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .company-info {
            flex: 1;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 8px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .company-details {
            font-size: 12px;
            opacity: 0.9;
            line-height: 1.6;
        }

        .invoice-title {
            text-align: center;
            flex: 1;
        }

        .invoice-title h1 {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 5px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .invoice-subtitle {
            font-size: 14px;
            opacity: 0.9;
        }

        .qr-section {
            text-align: center;
            flex: 1;
        }

        .qr-container {
            background: white;
            padding: 10px;
            border-radius: 8px;
            display: inline-block;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .qr-container img {
            width: 100px;
            height: 100px;
            display: block;
        }

        .qr-text {
            font-size: 9px;
            color: #666;
            margin-top: 5px;
            text-align: center;
        }

        /* Invoice Info Section */
        .invoice-info-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 25px;
            gap: 20px;
        }

        .invoice-details, .customer-details {
            flex: 1;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-right: 4px solid #2a5298;
        }

        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #2a5298;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e9ecef;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            padding: 5px 0;
        }

        .info-label {
            font-weight: bold;
            color: #495057;
            min-width: 120px;
        }

        .info-value {
            color: #212529;
            font-weight: 500;
        }

        /* Balance Section */
        .balance-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            border: 1px solid #dee2e6;
        }

        .balance-grid {
            display: flex;
            justify-content: space-between;
            gap: 15px;
        }

        .balance-item {
            flex: 1;
            text-align: center;
            padding: 15px;
            border-radius: 6px;
            border: 1px solid #dee2e6;
        }

        .balance-previous {
            background: linear-gradient(135deg, #fff5f5 0%, #fed7d7 100%);
            border-color: #fc8181;
        }

        .balance-current {
            background: linear-gradient(135deg, #f0fff4 0%, #c6f6d5 100%);
            border-color: #68d391;
        }

        .balance-total {
            background: linear-gradient(135deg, #fffaf0 0%, #fbd38d 100%);
            border-color: #f6ad55;
        }

        .balance-label {
            font-size: 12px;
            color: #4a5568;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .balance-amount {
            font-size: 18px;
            font-weight: bold;
            color: #2d3748;
        }

        /* Table Styles */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .items-table th {
            background: linear-gradient(135deg, #2a5298 0%, #1e3c72 100%);
            color: white;
            padding: 15px 10px;
            font-weight: bold;
            text-align: center;
            font-size: 12px;
        }

        .items-table td {
            padding: 12px 10px;
            border-bottom: 1px solid #e9ecef;
            text-align: center;
            vertical-align: middle;
        }

        .items-table tbody tr:nth-child(even) {
            background: #f8f9fa;
        }

        .items-table tbody tr:hover {
            background: #e3f2fd;
        }

        .product-name {
            font-weight: bold;
            color: #2a5298;
            text-align: right;
        }

        .product-description {
            font-size: 10px;
            color: #6c757d;
            margin-top: 3px;
            text-align: right;
        }

        /* Totals Section */
        .totals-section {
            background: white;
            border: 2px solid #2a5298;
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
        }

        .totals-grid {
            display: flex;
            justify-content: space-between;
            gap: 30px;
        }

        .totals-left {
            flex: 1;
        }

        .totals-right {
            min-width: 300px;
        }

        .summary-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 15px;
        }

        .summary-title {
            font-size: 14px;
            font-weight: bold;
            color: #2a5298;
            margin-bottom: 10px;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 11px;
        }

        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }

        .totals-table td {
            padding: 10px 15px;
            border-bottom: 1px solid #e9ecef;
        }

        .totals-table .total-label {
            font-weight: bold;
            color: #495057;
        }

        .totals-table .total-amount {
            text-align: left;
            font-weight: bold;
            color: #212529;
        }

        .grand-total {
            background: linear-gradient(135deg, #2a5298 0%, #1e3c72 100%);
            color: white;
            font-size: 16px;
            font-weight: bold;
        }

        /* Payment Section */
        .payment-section {
            background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-right: 4px solid #4299e1;
        }

        .payment-grid {
            display: flex;
            justify-content: space-between;
            gap: 20px;
        }

        .payment-info {
            flex: 1;
        }

        .payment-status {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
        }

        .status-unpaid {
            background: #fed7d7;
            color: #c53030;
        }

        .status-paid {
            background: #c6f6d5;
            color: #2f855a;
        }

        .status-partial {
            background: #fbd38d;
            color: #c05621;
        }

        /* Footer */
        .invoice-footer {
            margin-top: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            border-top: 3px solid #2a5298;
        }

        .terms-section {
            margin-bottom: 20px;
        }

        .terms-title {
            font-size: 14px;
            font-weight: bold;
            color: #2a5298;
            margin-bottom: 10px;
        }

        .terms-list {
            font-size: 10px;
            line-height: 1.6;
            color: #495057;
        }

        .terms-list li {
            margin-bottom: 5px;
            padding-right: 15px;
        }

        .footer-info {
            text-align: center;
            font-size: 10px;
            color: #6c757d;
            border-top: 1px solid #dee2e6;
            padding-top: 15px;
        }

        .footer-info strong {
            color: #2a5298;
        }

        /* Hover Effects */
        .company-name:hover {
            color: #ffffff !important;
            text-shadow: 2px 2px 4px rgba(111, 66, 193, 0.8);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .section-title:hover {
            color: #6f42c1 !important;
            transition: color 0.3s ease;
            cursor: pointer;
        }

        .product-name:hover {
            color: #6f42c1 !important;
            transition: color 0.3s ease;
            cursor: pointer;
        }

        .info-label:hover {
            color: #6f42c1 !important;
            transition: color 0.3s ease;
            cursor: pointer;
        }

        .info-value:hover {
            color: #6f42c1 !important;
            transition: color 0.3s ease;
            cursor: pointer;
        }

        .items-table tbody tr:hover {
            background: rgba(111, 66, 193, 0.1) !important;
        }

        .items-table tbody tr:hover td {
            color: #6f42c1 !important;
            transition: color 0.3s ease;
        }

        .balance-item:hover {
            transform: scale(1.02);
            transition: transform 0.3s ease;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(111, 66, 193, 0.2);
        }

        .balance-item:hover .balance-label,
        .balance-item:hover .balance-amount {
            color: #6f42c1 !important;
            transition: color 0.3s ease;
        }

        .totals-table tr:hover td {
            color: #6f42c1 !important;
            transition: color 0.3s ease;
        }

        .summary-item:hover {
            color: #6f42c1 !important;
            transition: color 0.3s ease;
            cursor: pointer;
        }

        .qr-container:hover {
            transform: scale(1.05);
            transition: transform 0.3s ease;
            cursor: pointer;
            box-shadow: 0 6px 15px rgba(111, 66, 193, 0.3);
        }

        .invoice-details:hover,
        .customer-details:hover {
            border-right-color: #6f42c1 !important;
            transition: border-color 0.3s ease;
        }

        .invoice-details:hover .section-title,
        .customer-details:hover .section-title {
            color: #6f42c1 !important;
            transition: color 0.3s ease;
        }

        /* Utility Classes */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .mb-10 { margin-bottom: 10px; }
        .mb-15 { margin-bottom: 15px; }
        .mb-20 { margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Professional Header -->
        <div class="invoice-header">
            <div class="header-content">
                <div class="company-info">
                    @php
                        $company = \App\Helpers\TenantHelper::getCompanyInfo();
                    @endphp
                    <div class="company-name">{{ $company['name'] }}</div>
                    <div class="company-details">
                        {{ \App\Helpers\TenantHelper::getInvoiceSubtitle() }}<br>
                        {{ $company['address'] }}<br>
                        هاتف: {{ $company['phone'] }}<br>
                        البريد الإلكتروني: {{ $company['email'] }}
                    </div>
                </div>

                <div class="invoice-title">
                    <h1>فاتورة مبيعات</h1>
                    <div class="invoice-subtitle">SALES INVOICE</div>
                </div>

                <div class="qr-section">
                    @php
                        $qrCode = \App\Helpers\InvoiceQrHelper::generateInvoiceQr($invoice);
                    @endphp
                    <div class="qr-container">
                        <img src="data:image/svg+xml;base64,{{ $qrCode }}" alt="QR Code للفاتورة" style="width: 100px; height: 100px;">
                        <div class="qr-text">امسح للتحقق</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Invoice & Customer Information -->
        <div class="invoice-info-section">
            <div class="invoice-details">
                <div class="section-title">
                    <i class="fas fa-file-invoice"></i> تفاصيل الفاتورة
                </div>
                <div class="info-row">
                    <span class="info-label">رقم الفاتورة:</span>
                    <span class="info-value">{{ $invoice->invoice_number ?? 'INV-001' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">تاريخ الإصدار:</span>
                    <span class="info-value">{{ $invoice->created_at ? $invoice->created_at->format('Y-m-d') : now()->format('Y-m-d') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">تاريخ الاستحقاق:</span>
                    <span class="info-value">{{ $invoice->due_date ? $invoice->due_date->format('Y-m-d') : now()->addDays(30)->format('Y-m-d') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">وقت الإنشاء:</span>
                    <span class="info-value">{{ now()->format('H:i:s') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">المندوب:</span>
                    <span class="info-value">{{ $invoice->sales_rep ?? 'أحمد محمد' }}</span>
                </div>
            </div>

            <div class="customer-details">
                <div class="section-title">
                    <i class="fas fa-user-tie"></i> بيانات العميل
                </div>
                <div class="info-row">
                    <span class="info-label">اسم العميل:</span>
                    <span class="info-value">{{ $invoice->customer->name ?? 'صيدلية الشفاء' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">رقم الهاتف:</span>
                    <span class="info-value">{{ $invoice->customer->phone ?? '+964 770 987 6543' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">البريد الإلكتروني:</span>
                    <span class="info-value">{{ $invoice->customer->email ?? 'pharmacy@example.com' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">العنوان:</span>
                    <span class="info-value">{{ $invoice->customer->address ?? 'بغداد - الكرخ - حي الجامعة' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">رقم العميل:</span>
                    <span class="info-value">{{ $invoice->customer->customer_number ?? 'CUST-001' }}</span>
                </div>
            </div>
        </div>

        <!-- Balance Summary -->
        <div class="balance-section">
            <div class="section-title text-center">
                <i class="fas fa-chart-line"></i> ملخص المديونية والمبالغ
            </div>
            <div class="balance-grid">
                <div class="balance-item balance-previous">
                    <div class="balance-label">المديونية السابقة</div>
                    <div class="balance-amount">{{ number_format($invoice->previous_balance ?? 75000, 0) }} د.ع</div>
                </div>
                <div class="balance-item balance-current">
                    <div class="balance-label">فاتورة حالية</div>
                    <div class="balance-amount">{{ number_format($invoice->total_amount ?? 150000, 0) }} د.ع</div>
                </div>
                <div class="balance-item balance-total">
                    <div class="balance-label">المديونية الحالية</div>
                    <div class="balance-amount">{{ number_format(($invoice->previous_balance ?? 75000) + ($invoice->total_amount ?? 150000) - ($invoice->paid_amount ?? 0), 0) }} د.ع</div>
                </div>
            </div>
        </div>

        <!-- Invoice Items -->
        <div class="section-title">
            <i class="fas fa-list-alt"></i> تفاصيل الأصناف والمنتجات
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 6%;">#</th>
                    <th style="width: 35%;">اسم المنتج والوصف</th>
                    <th style="width: 10%;">الوحدة</th>
                    <th style="width: 10%;">الكمية</th>
                    <th style="width: 13%;">سعر الوحدة</th>
                    <th style="width: 10%;">الخصم</th>
                    <th style="width: 16%;">المجموع</th>
                </tr>
            </thead>
            <tbody>
                @if(isset($invoice->items) && $invoice->items->count() > 0)
                    @foreach($invoice->items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <div class="product-name">{{ $item->product->name }}</div>
                            @if($item->product->description)
                                <div class="product-description">{{ $item->product->description }}</div>
                            @endif
                            @if(isset($item->product->code) && $item->product->code)
                                <div class="product-description">كود المنتج: {{ $item->product->code }}</div>
                            @endif
                        </td>
                        <td>{{ $item->unit ?? 'قطعة' }}</td>
                        <td>{{ number_format($item->quantity, 0) }}</td>
                        <td>{{ number_format($item->unit_price, 0) }} د.ع</td>
                        <td>{{ number_format($item->discount_amount ?? 0, 0) }} د.ع</td>
                        <td><strong>{{ number_format($item->total_amount, 0) }} د.ع</strong></td>
                    </tr>
                    @endforeach
                @else
                    <!-- Sample Data -->
                    <tr>
                        <td>1</td>
                        <td>
                            <div class="product-name">باراسيتامول 500 مجم</div>
                            <div class="product-description">أقراص مسكنة للألم وخافضة للحرارة - عبوة 20 قرص</div>
                            <div class="product-description">كود المنتج: MED-001</div>
                        </td>
                        <td>عبوة</td>
                        <td>10</td>
                        <td>2,500 د.ع</td>
                        <td>0 د.ع</td>
                        <td><strong>25,000 د.ع</strong></td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>
                            <div class="product-name">فيتامين د3 كبسولات</div>
                            <div class="product-description">مكمل غذائي لتقوية العظام - عبوة 30 كبسولة</div>
                            <div class="product-description">كود المنتج: VIT-003</div>
                        </td>
                        <td>عبوة</td>
                        <td>5</td>
                        <td>10,000 د.ع</td>
                        <td>0 د.ع</td>
                        <td><strong>50,000 د.ع</strong></td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>
                            <div class="product-name">أوميجا 3 زيت السمك</div>
                            <div class="product-description">مكمل غذائي لصحة القلب والدماغ - عبوة 60 كبسولة</div>
                            <div class="product-description">كود المنتج: SUP-007</div>
                        </td>
                        <td>عبوة</td>
                        <td>3</td>
                        <td>25,000 د.ع</td>
                        <td>0 د.ع</td>
                        <td><strong>75,000 د.ع</strong></td>
                    </tr>
                @endif
            </tbody>
        </table>

        <!-- Totals Section -->
        <div class="totals-section">
            <div class="totals-grid">
                <div class="totals-left">
                    <div class="summary-info">
                        <div class="summary-title">
                            <i class="fas fa-chart-bar"></i> ملخص الفاتورة
                        </div>
                        <div class="summary-item">
                            <span>عدد الأصناف:</span>
                            <span><strong>{{ $invoice->items->count() ?? 3 }} صنف</strong></span>
                        </div>
                        <div class="summary-item">
                            <span>إجمالي الكمية:</span>
                            <span><strong>{{ $invoice->items->sum('quantity') ?? 18 }} قطعة</strong></span>
                        </div>
                        <div class="summary-item">
                            <span>متوسط سعر الوحدة:</span>
                            <span><strong>{{ number_format(($invoice->total_amount ?? 150000) / ($invoice->items->sum('quantity') ?? 18), 0) }} د.ع</strong></span>
                        </div>
                    </div>

                    <div class="summary-info">
                        <div class="summary-title">
                            <i class="fas fa-qrcode"></i> معلومات QR Code
                        </div>
                        <div class="summary-item">
                            <span>رقم الفاتورة:</span>
                            <span>{{ $invoice->invoice_number ?? 'INV-001' }}</span>
                        </div>
                        <div class="summary-item">
                            <span>اسم العميل:</span>
                            <span>{{ $invoice->customer->name ?? 'صيدلية الشفاء' }}</span>
                        </div>
                        <div class="summary-item">
                            <span>تاريخ الإنشاء:</span>
                            <span>{{ $invoice->created_at ? $invoice->created_at->format('Y-m-d H:i') : now()->format('Y-m-d H:i') }}</span>
                        </div>
                        <div class="summary-item">
                            <span>رابط التحقق:</span>
                            <span style="font-size: 9px;">{{ url('/verify-invoice/' . ($invoice->invoice_number ?? 'INV-001')) }}</span>
                        </div>
                    </div>
                </div>

                <div class="totals-right">
                    <table class="totals-table">
                        <tr>
                            <td class="total-label">المجموع الفرعي:</td>
                            <td class="total-amount">{{ number_format($invoice->subtotal ?? 150000, 0) }} د.ع</td>
                        </tr>
                        <tr>
                            <td class="total-label">الخصم الإجمالي:</td>
                            <td class="total-amount">{{ number_format($invoice->discount_amount ?? 0, 0) }} د.ع</td>
                        </tr>
                        <tr>
                            <td class="total-label">الضريبة المضافة:</td>
                            <td class="total-amount">{{ number_format($invoice->tax_amount ?? 0, 0) }} د.ع</td>
                        </tr>
                        <tr>
                            <td class="total-label">رسوم التوصيل:</td>
                            <td class="total-amount">{{ number_format($invoice->delivery_fee ?? 0, 0) }} د.ع</td>
                        </tr>
                        <tr class="grand-total">
                            <td>المبلغ الإجمالي:</td>
                            <td>{{ number_format($invoice->total_amount ?? 150000, 0) }} د.ع</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Payment Information -->
        <div class="payment-section">
            <div class="section-title">
                <i class="fas fa-credit-card"></i> معلومات الدفع والتحصيل
            </div>
            <div class="payment-grid">
                <div class="payment-info">
                    <div class="info-row">
                        <span class="info-label">طريقة الدفع:</span>
                        <span class="info-value">{{ $invoice->payment_method ?? 'نقداً' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">حالة الدفع:</span>
                        <span class="payment-status status-unpaid">{{ $invoice->payment_status ?? 'غير مدفوع' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">تاريخ الدفع:</span>
                        <span class="info-value">{{ $invoice->payment_date ? $invoice->payment_date->format('Y-m-d') : 'لم يتم الدفع بعد' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">المبلغ المدفوع:</span>
                        <span class="info-value">{{ number_format($invoice->paid_amount ?? 0, 0) }} د.ع</span>
                    </div>
                </div>
                <div class="payment-info">
                    <div class="info-row">
                        <span class="info-label">المبلغ المتبقي:</span>
                        <span class="info-value">{{ number_format(($invoice->total_amount ?? 150000) - ($invoice->paid_amount ?? 0), 0) }} د.ع</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">تاريخ الاستحقاق:</span>
                        <span class="info-value">{{ $invoice->due_date ? $invoice->due_date->format('Y-m-d') : now()->addDays(30)->format('Y-m-d') }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">أيام التأخير:</span>
                        <span class="info-value">{{ $invoice->due_date && $invoice->due_date->isPast() ? $invoice->due_date->diffInDays(now()) : '0' }} يوم</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">رقم المرجع:</span>
                        <span class="info-value">{{ $invoice->reference_number ?? 'REF-' . ($invoice->invoice_number ?? 'INV-001') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="invoice-footer">
            <div class="terms-section">
                <div class="terms-title">
                    <i class="fas fa-file-contract"></i> الشروط والأحكام
                </div>
                <ul class="terms-list">
                    <li>يجب دفع المبلغ خلال 30 يوماً من تاريخ الفاتورة، وإلا ستطبق غرامة تأخير 2% شهرياً</li>
                    <li>جميع المنتجات مضمونة حسب شروط الشركة المصنعة ولا يمكن إرجاعها إلا في حالة وجود عيب في التصنيع</li>
                    <li>هذه الفاتورة صالحة قانونياً ولا تحتاج إلى توقيع، وتم إنشاؤها إلكترونياً بواسطة نظام MaxCon ERP</li>
                    <li>في حالة وجود أي استفسار، يرجى التواصل معنا خلال 7 أيام من تاريخ الفاتورة</li>
                    <li>تخضع هذه الفاتورة لقوانين جمهورية العراق والمحاكم العراقية المختصة</li>
                </ul>
            </div>

            <div class="footer-info">
                <p><strong>{{ \App\Helpers\TenantHelper::getInvoiceFooter() }}</strong></p>
                <p>نحو صحة أفضل للجميع | رؤيتنا: أن نكون الشريك الأول في مجال الأدوية والمستلزمات الطبية في العراق</p>
                <p>تم إنشاء هذه الفاتورة بواسطة نظام MaxCon ERP في: {{ now()->format('Y-m-d H:i:s') }}</p>
                <p style="font-size: 9px; color: #999; margin-top: 10px;">
                    هذه فاتورة إلكترونية تم إنشاؤها تلقائياً | للتحقق من صحة الفاتورة امسح QR Code أعلاه
                    <br>
                    رقم الترخيص: {{ $company['license_key'] }} | وزارة الصحة العراقية | {{ $company['city'] }} - {{ $company['governorate'] }}
                </p>
            </div>
        </div>
    </div>
</body>
</html>
