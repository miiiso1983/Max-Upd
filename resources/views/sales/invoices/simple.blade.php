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
            padding: 25px;
            margin-bottom: 25px;
            border-radius: 10px;
            text-align: center;
        }
        
        .company-name {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .company-details {
            font-size: 13px;
            opacity: 0.95;
            line-height: 1.6;
        }
        
        .invoice-title {
            font-size: 24px;
            font-weight: bold;
            margin: 15px 0 10px 0;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
        }
        
        /* Info Section */
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
        
        /* Footer */
        .invoice-footer {
            margin-top: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            border-top: 3px solid #2a5298;
        }
        
        .footer-info {
            text-align: center;
            font-size: 10px;
            color: #6c757d;
        }
        
        .footer-info strong {
            color: #2a5298;
        }
        
        /* Hover Effects */
        .company-name:hover {
            color: #6f42c1 !important;
            transition: color 0.3s ease;
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

        .invoice-header:hover .company-name {
            color: #ffffff !important;
            text-shadow: 2px 2px 4px rgba(111, 66, 193, 0.5);
            transition: all 0.3s ease;
        }

        /* Utility Classes */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Professional Header -->
        <div class="invoice-header">
            @php
                $company = \App\Helpers\TenantHelper::getCompanyInfo();
            @endphp
            <div class="company-name">{{ $company['name'] }}</div>
            <div class="company-details">
                {{ \App\Helpers\TenantHelper::getInvoiceSubtitle() }}<br>
                {{ $company['address'] }}<br>
                هاتف: {{ $company['phone'] }} | البريد الإلكتروني: {{ $company['email'] }}
            </div>
            <div class="invoice-title">فاتورة مبيعات</div>
        </div>

        <!-- Invoice & Customer Information -->
        <div class="invoice-info-section">
            <div class="invoice-details">
                <div class="section-title">تفاصيل الفاتورة</div>
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
            </div>
            
            <div class="customer-details">
                <div class="section-title">بيانات العميل</div>
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
            </div>
        </div>

        <!-- Balance Summary -->
        <div class="balance-section">
            <div class="section-title text-center">ملخص المديونية والمبالغ</div>
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
        <div class="section-title">تفاصيل الأصناف والمنتجات</div>
        
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 8%;">#</th>
                    <th style="width: 40%;">اسم المنتج والوصف</th>
                    <th style="width: 12%;">الكمية</th>
                    <th style="width: 15%;">سعر الوحدة</th>
                    <th style="width: 10%;">الخصم</th>
                    <th style="width: 15%;">المجموع</th>
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
                        </td>
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
                            <div class="product-description">أقراص مسكنة للألم وخافضة للحرارة</div>
                        </td>
                        <td>10</td>
                        <td>2,500 د.ع</td>
                        <td>0 د.ع</td>
                        <td><strong>25,000 د.ع</strong></td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>
                            <div class="product-name">فيتامين د3 كبسولات</div>
                            <div class="product-description">مكمل غذائي لتقوية العظام</div>
                        </td>
                        <td>5</td>
                        <td>10,000 د.ع</td>
                        <td>0 د.ع</td>
                        <td><strong>50,000 د.ع</strong></td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>
                            <div class="product-name">أوميجا 3 زيت السمك</div>
                            <div class="product-description">مكمل غذائي لصحة القلب والدماغ</div>
                        </td>
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
                <tr class="grand-total">
                    <td>المبلغ الإجمالي:</td>
                    <td>{{ number_format($invoice->total_amount ?? 150000, 0) }} د.ع</td>
                </tr>
            </table>
        </div>

        <!-- Footer -->
        <div class="invoice-footer">
            <div class="footer-info">
                <p><strong>{{ \App\Helpers\TenantHelper::getInvoiceFooter() }}</strong></p>
                <p>نحو صحة أفضل للجميع</p>
                <p>تم إنشاء هذه الفاتورة بواسطة نظام MaxCon ERP في: {{ now()->format('Y-m-d H:i:s') }}</p>
                <p style="margin-top: 10px;">
                    هذه فاتورة إلكترونية تم إنشاؤها تلقائياً | رقم الترخيص: {{ $company['license_key'] }}
                </p>
            </div>
        </div>
    </div>
</body>
</html>
