<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إيصال دفعة - {{ $payment->payment_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            background: white;
            direction: rtl;
        }

        .receipt {
            max-width: 100%;
            margin: 0;
            padding: 15px;
            background: white;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 5px;
        }

        .receipt-title {
            font-size: 16px;
            font-weight: bold;
            margin: 10px 0;
            color: #333;
        }

        .receipt-number {
            font-size: 14px;
            color: #666;
        }

        .info-section {
            margin-bottom: 20px;
        }

        .info-grid {
            width: 100%;
            margin-bottom: 15px;
        }

        .info-row {
            display: table;
            width: 100%;
            margin-bottom: 8px;
        }

        .info-cell {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding: 2px 5px;
        }

        .info-label {
            font-weight: bold;
            color: #555;
            display: inline-block;
            width: 80px;
        }

        .info-value {
            color: #333;
        }

        .amount-section {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            padding: 15px;
            text-align: center;
            margin: 20px 0;
        }

        .amount-label {
            font-size: 14px;
            color: #666;
            margin-bottom: 8px;
        }

        .amount-value {
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 5px;
        }

        .amount-words {
            font-size: 12px;
            color: #666;
            font-style: italic;
        }

        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: bold;
        }

        .status-completed {
            background: #d1fae5;
            color: #065f46;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-cancelled {
            background: #fee2e2;
            color: #991b1b;
        }

        .invoice-section {
            border: 1px solid #e5e7eb;
            padding: 10px;
            margin: 15px 0;
            background: #f9fafb;
        }

        .invoice-title {
            font-weight: bold;
            color: #374151;
            margin-bottom: 8px;
            border-bottom: 1px solid #d1d5db;
            padding-bottom: 3px;
        }

        .notes-section {
            margin-top: 20px;
            padding: 10px;
            background: #f8f9fa;
            border-left: 3px solid #2563eb;
        }

        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #666;
            font-size: 10px;
        }

        .signature-section {
            margin-top: 30px;
            padding-top: 15px;
        }

        .signature-row {
            display: table;
            width: 100%;
        }

        .signature-cell {
            display: table-cell;
            width: 50%;
            text-align: center;
            border-top: 1px solid #333;
            padding-top: 8px;
        }

        .section-title {
            margin-bottom: 10px;
            color: #374151;
            border-bottom: 1px solid #d1d5db;
            padding-bottom: 3px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="receipt">
        <!-- Header -->
        <div class="header">
            <div class="company-name">{{ config('app.name', 'MaxCon SaaS') }}</div>
            <div class="receipt-title">إيصال دفعة</div>
            <div class="receipt-number">{{ $payment->payment_number }}</div>
        </div>

        <!-- Payment Info -->
        <div class="info-section">
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-cell">
                        <span class="info-label">تاريخ الدفع:</span>
                        <span class="info-value">{{ $payment->payment_date->format('Y-m-d') }}</span>
                    </div>
                    <div class="info-cell">
                        <span class="info-label">الحالة:</span>
                        <span class="status-badge 
                            @switch($payment->status)
                                @case('completed') status-completed @break
                                @case('pending') status-pending @break
                                @case('cancelled') status-cancelled @break
                            @endswitch">
                            @switch($payment->status)
                                @case('completed') مكتملة @break
                                @case('pending') معلقة @break
                                @case('cancelled') ملغية @break
                                @default {{ $payment->status }}
                            @endswitch
                        </span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-cell">
                        <span class="info-label">طريقة الدفع:</span>
                        <span class="info-value">
                            @switch($payment->payment_method)
                                @case('cash') نقدي @break
                                @case('bank_transfer') تحويل مصرفي @break
                                @case('check') شيك @break
                                @case('credit_card') بطاقة ائتمان @break
                                @case('mobile_payment') دفع عبر الهاتف @break
                                @default {{ $payment->payment_method }}
                            @endswitch
                        </span>
                    </div>
                    <div class="info-cell">
                        <span class="info-label">تاريخ الإنشاء:</span>
                        <span class="info-value">{{ $payment->created_at->format('Y-m-d H:i') }}</span>
                    </div>
                </div>
                @if($payment->reference_number)
                <div class="info-row">
                    <div class="info-cell">
                        <span class="info-label">رقم المرجع:</span>
                        <span class="info-value">{{ $payment->reference_number }}</span>
                    </div>
                    <div class="info-cell">
                        <span class="info-label">المنشئ:</span>
                        <span class="info-value">{{ $payment->creator->name ?? 'غير محدد' }}</span>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Customer Info -->
        <div class="info-section">
            <div class="section-title">معلومات العميل</div>
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-cell">
                        <span class="info-label">اسم العميل:</span>
                        <span class="info-value">{{ $payment->customer->name_ar ?: $payment->customer->name }}</span>
                    </div>
                    <div class="info-cell">
                        <span class="info-label">رمز العميل:</span>
                        <span class="info-value">{{ $payment->customer->code }}</span>
                    </div>
                </div>
                @if($payment->customer->phone || $payment->customer->email)
                <div class="info-row">
                    @if($payment->customer->phone)
                    <div class="info-cell">
                        <span class="info-label">الهاتف:</span>
                        <span class="info-value">{{ $payment->customer->phone }}</span>
                    </div>
                    @endif
                    @if($payment->customer->email)
                    <div class="info-cell">
                        <span class="info-label">البريد الإلكتروني:</span>
                        <span class="info-value">{{ $payment->customer->email }}</span>
                    </div>
                    @endif
                </div>
                @endif
            </div>
        </div>

        <!-- Amount Section -->
        <div class="amount-section">
            <div class="amount-label">المبلغ المدفوع</div>
            <div class="amount-value">{{ number_format($payment->amount) }} {{ $payment->currency }}</div>
            <div class="amount-words">
                {{ \App\Helpers\NumberToWords::convert($payment->amount) }} {{ $payment->currency === 'IQD' ? 'دينار عراقي' : $payment->currency }}
            </div>
        </div>

        <!-- Invoice Info -->
        @if($payment->invoice)
        <div class="invoice-section">
            <div class="invoice-title">معلومات الفاتورة</div>
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-cell">
                        <span class="info-label">رقم الفاتورة:</span>
                        <span class="info-value">{{ $payment->invoice->invoice_number }}</span>
                    </div>
                    <div class="info-cell">
                        <span class="info-label">إجمالي الفاتورة:</span>
                        <span class="info-value">{{ number_format($payment->invoice->total_amount) }} د.ع</span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-cell">
                        <span class="info-label">المبلغ المدفوع:</span>
                        <span class="info-value">{{ number_format($payment->invoice->paid_amount) }} د.ع</span>
                    </div>
                    <div class="info-cell">
                        <span class="info-label">المبلغ المتبقي:</span>
                        <span class="info-value">{{ number_format($payment->invoice->total_amount - $payment->invoice->paid_amount) }} د.ع</span>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Notes -->
        @if($payment->notes)
        <div class="notes-section">
            <strong>ملاحظات:</strong><br>
            {{ $payment->notes }}
        </div>
        @endif

        <!-- Signature Section -->
        <div class="signature-section">
            <div class="signature-row">
                <div class="signature-cell">توقيع المستلم</div>
                <div class="signature-cell">توقيع المحاسب</div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>تم إنشاء هذا الإيصال تلقائياً بواسطة نظام {{ config('app.name', 'MaxCon SaaS') }}</p>
            <p>تاريخ الطباعة: {{ now()->format('Y-m-d H:i:s') }}</p>
        </div>
    </div>
</body>
</html>
