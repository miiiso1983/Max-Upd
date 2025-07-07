<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فاتورة رقم {{ $invoice->invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 14px;
            line-height: 1.6;
            color: #333;
            direction: rtl;
            text-align: right;
            unicode-bidi: bidi-override;
        }

        /* Arabic text support */
        .arabic-text {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            direction: rtl;
            text-align: right;
            unicode-bidi: embed;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            margin-bottom: 30px;
            text-align: center;
            direction: rtl;
            border-radius: 8px;
        }

        .company-name {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            direction: rtl;
            text-align: center;
        }

        .company-info {
            font-size: 16px;
            direction: rtl;
            text-align: center;
        }
        
        .invoice-title {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            border-right: 5px solid #667eea;
        }
        
        .invoice-title h1 {
            font-size: 24px;
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .invoice-meta {
            width: 100%;
            margin-bottom: 30px;
        }

        .invoice-details, .customer-details {
            width: 48%;
            background: #fff;
            padding: 20px;
            border: 1px solid #e9ecef;
            display: inline-block;
            vertical-align: top;
            direction: rtl;
            text-align: right;
        }

        .invoice-details {
            margin-left: 4%;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: #495057;
            margin-bottom: 15px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 5px;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            padding: 5px 0;
        }
        
        .detail-label {
            font-weight: 500;
            color: #6c757d;
        }
        
        .detail-value {
            font-weight: 600;
            color: #212529;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
            background: white;
            direction: rtl;
        }

        .items-table th {
            background: #667eea;
            color: white;
            padding: 15px 10px;
            font-weight: bold;
            text-align: center;
            border: 1px solid #ddd;
            direction: rtl;
        }

        .items-table td {
            padding: 12px 10px;
            border: 1px solid #ddd;
            text-align: center;
            direction: rtl;
        }
        
        .items-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .items-table tr:hover {
            background-color: #e3f2fd;
        }
        
        .totals-section {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 8px;
            margin-top: 30px;
            border: 1px solid #e9ecef;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 8px 0;
        }
        
        .total-row.grand-total {
            border-top: 2px solid #667eea;
            margin-top: 15px;
            padding-top: 15px;
            font-size: 18px;
            font-weight: 700;
            color: #667eea;
        }
        
        .notes-section {
            margin-top: 30px;
            padding: 20px;
            background: #fff3cd;
            border-radius: 8px;
            border-right: 5px solid #ffc107;
        }
        
        .notes-title {
            font-weight: 600;
            color: #856404;
            margin-bottom: 10px;
        }
        
        .notes-content {
            color: #856404;
            line-height: 1.8;
        }
        
        .footer {
            margin-top: 40px;
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            color: #6c757d;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-paid {
            background: #d4edda;
            color: #155724;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-overdue {
            background: #f8d7da;
            color: #721c24;
        }
        
        .amount {
            font-weight: 600;
            color: #28a745;
        }
        
        .currency {
            font-size: 12px;
            color: #6c757d;
        }
        
        @media print {
            body {
                font-size: 12px;
            }
            
            .container {
                padding: 10px;
            }
            
            .header {
                background: #667eea !important;
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            @php
                $company = \App\Helpers\TenantHelper::getCompanyInfo();
            @endphp
            <div class="company-name">{{ $company['name'] }}</div>
            <div class="company-info">
                {{ \App\Helpers\TenantHelper::getInvoiceSubtitle() }}<br>
                {{ $company['address'] }} | هاتف: {{ $company['phone'] }} | البريد: {{ $company['email'] }}
            </div>
        </div>

        <!-- Invoice Title -->
        <div class="invoice-title">
            <h1>فاتورة مبيعات</h1>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span>رقم الفاتورة: <strong>{{ $invoice->invoice_number }}</strong></span>
                <span class="status-badge status-{{ $invoice->status === 'paid' ? 'paid' : ($invoice->status === 'pending' ? 'pending' : 'overdue') }}">
                    {{ $invoice->status === 'paid' ? 'مدفوعة' : ($invoice->status === 'pending' ? 'في الانتظار' : 'متأخرة') }}
                </span>
            </div>
        </div>

        <!-- Invoice Meta -->
        <div class="invoice-meta">
            <!-- Invoice Details -->
            <div class="invoice-details">
                <div class="section-title">تفاصيل الفاتورة</div>
                <div class="detail-row">
                    <span class="detail-label">تاريخ الفاتورة:</span>
                    <span class="detail-value">{{ $invoice->invoice_date ? $invoice->invoice_date->format('Y/m/d') : 'غير محدد' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">تاريخ الاستحقاق:</span>
                    <span class="detail-value">{{ $invoice->due_date ? $invoice->due_date->format('Y/m/d') : 'غير محدد' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">طريقة الدفع:</span>
                    <span class="detail-value">
                        @switch($invoice->payment_method)
                            @case('cash') نقداً @break
                            @case('credit') آجل @break
                            @case('bank_transfer') تحويل بنكي @break
                            @case('check') شيك @break
                            @case('credit_card') بطاقة ائتمان @break
                            @default {{ $invoice->payment_method }}
                        @endswitch
                    </span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">شروط الدفع:</span>
                    <span class="detail-value">{{ $invoice->payment_terms ?? 0 }} يوم</span>
                </div>
            </div>

            <!-- Customer Details -->
            <div class="customer-details">
                <div class="section-title">بيانات العميل</div>
                <div class="detail-row">
                    <span class="detail-label">اسم العميل:</span>
                    <span class="detail-value">{{ $invoice->customer->name ?? 'غير محدد' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">رقم الهاتف:</span>
                    <span class="detail-value">{{ $invoice->customer->phone ?? 'غير محدد' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">البريد الإلكتروني:</span>
                    <span class="detail-value">{{ $invoice->customer->email ?? 'غير محدد' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">العنوان:</span>
                    <span class="detail-value">{{ $invoice->customer->address ?? 'غير محدد' }}</span>
                </div>
            </div>
        </div>

        <!-- Balance Summary -->
        <div style="background: #f8f9fa; padding: 20px; margin: 20px 0; border-radius: 8px; border-right: 4px solid #667eea;">
            <div style="text-align: center; font-size: 16px; font-weight: bold; color: #667eea; margin-bottom: 15px;">
                ملخص المديونية والمبالغ
            </div>
            <div style="display: flex; justify-content: space-between; gap: 15px;">
                <div style="flex: 1; text-align: center; padding: 15px; background: #fff5f5; border-radius: 6px; border: 1px solid #fc8181;">
                    <div style="font-size: 12px; color: #4a5568; margin-bottom: 5px; font-weight: 600;">المديونية السابقة</div>
                    <div style="font-size: 18px; font-weight: bold; color: #e53e3e;">{{ number_format($invoice->previous_balance ?? 0, 0) }} د.ع</div>
                </div>
                <div style="flex: 1; text-align: center; padding: 15px; background: #f0fff4; border-radius: 6px; border: 1px solid #68d391;">
                    <div style="font-size: 12px; color: #4a5568; margin-bottom: 5px; font-weight: 600;">فاتورة حالية</div>
                    <div style="font-size: 18px; font-weight: bold; color: #38a169;">{{ number_format($invoice->total_amount ?? 0, 0) }} د.ع</div>
                </div>
                <div style="flex: 1; text-align: center; padding: 15px; background: #fffaf0; border-radius: 6px; border: 1px solid #f6ad55;">
                    <div style="font-size: 12px; color: #4a5568; margin-bottom: 5px; font-weight: 600;">المديونية الحالية</div>
                    <div style="font-size: 18px; font-weight: bold; color: #d69e2e;">{{ number_format(($invoice->previous_balance ?? 0) + ($invoice->total_amount ?? 0) - ($invoice->paid_amount ?? 0), 0) }} د.ع</div>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 5%">#</th>
                    <th style="width: 40%">اسم المنتج</th>
                    <th style="width: 10%">الكمية</th>
                    <th style="width: 15%">سعر الوحدة</th>
                    <th style="width: 10%">الخصم %</th>
                    <th style="width: 20%">الإجمالي</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoice->items ?? [] as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td style="text-align: right;">{{ $item->product->name ?? 'منتج محذوف' }}</td>
                    <td>{{ number_format($item->quantity) }}</td>
                    <td><span class="amount">{{ number_format($item->unit_price, 2) }}</span> <span class="currency">د.ع</span></td>
                    <td>{{ number_format($item->discount_percentage ?? 0, 1) }}%</td>
                    <td><span class="amount">{{ number_format($item->total_amount, 2) }}</span> <span class="currency">د.ع</span></td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: #6c757d; font-style: italic;">لا توجد أصناف في هذه الفاتورة</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Totals Section -->
        <div class="totals-section">
            <div class="total-row">
                <span>المجموع الفرعي:</span>
                <span class="amount">{{ number_format($invoice->subtotal ?? 0, 2) }} د.ع</span>
            </div>
            <div class="total-row">
                <span>إجمالي الخصم:</span>
                <span class="amount">{{ number_format($invoice->discount_amount ?? 0, 2) }} د.ع</span>
            </div>
            <div class="total-row">
                <span>الضريبة ({{ number_format($invoice->tax_rate ?? 0, 1) }}%):</span>
                <span class="amount">{{ number_format($invoice->tax_amount ?? 0, 2) }} د.ع</span>
            </div>
            <div class="total-row grand-total">
                <span>المجموع الكلي:</span>
                <span class="amount">{{ number_format($invoice->total_amount ?? 0, 2) }} د.ع</span>
            </div>
        </div>

        <!-- Notes Section -->
        @if($invoice->notes)
        <div class="notes-section">
            <div class="notes-title">ملاحظات:</div>
            <div class="notes-content">{{ $invoice->notes }}</div>
        </div>
        @endif

        <!-- QR Code Section -->
        <div style="text-align: center; margin: 20px 0; padding: 15px; background: #f8f9fa; border-radius: 8px;">
            <div style="display: inline-block; padding: 10px; background: white; border: 2px solid #667eea; border-radius: 8px;">
                @php
                    $qrData = json_encode([
                        'company' => $company['name'],
                        'invoice_number' => $invoice->invoice_number,
                        'customer' => $invoice->customer->name ?? 'عميل',
                        'amount' => $invoice->total_amount ?? 0,
                        'date' => $invoice->created_at ? $invoice->created_at->format('Y-m-d') : now()->format('Y-m-d'),
                        'verification_url' => url('/verify-invoice/' . $invoice->invoice_number)
                    ], JSON_UNESCAPED_UNICODE);
                @endphp
                <div style="width: 80px; height: 80px; background: #667eea; color: white; display: flex; align-items: center; justify-content: center; font-size: 10px; text-align: center; border-radius: 4px; margin: 0 auto;">
                    QR<br>{{ $invoice->invoice_number }}<br>{{ $company['name'] }}
                </div>
                <div style="font-size: 10px; color: #666; margin-top: 5px;">امسح للتحقق من الفاتورة</div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>{{ \App\Helpers\TenantHelper::getInvoiceFooter() }}</p>
            <p style="font-size: 12px; margin-top: 10px;">
                تم إنشاء هذه الفاتورة بواسطة نظام MaxCon ERP في {{ now()->format('Y/m/d H:i') }}
            </p>
            <p style="font-size: 10px; color: #999; margin-top: 5px;">
                رقم الترخيص: {{ $company['license_key'] }} | {{ $company['city'] }} - {{ $company['governorate'] }}
            </p>
        </div>
    </div>
</body>
</html>
