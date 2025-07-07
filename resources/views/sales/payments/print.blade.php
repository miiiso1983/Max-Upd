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
            font-family: 'Arial', sans-serif;
            font-size: 14px;
            line-height: 1.6;
            color: #333;
            background: white;
            direction: rtl;
        }

        .receipt {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: white;
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
            color: #2563eb;
            margin-bottom: 5px;
        }

        .receipt-title {
            font-size: 20px;
            font-weight: bold;
            margin: 15px 0;
            color: #333;
        }

        .receipt-number {
            font-size: 16px;
            color: #666;
            font-family: monospace;
        }

        .info-section {
            margin-bottom: 25px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .info-item {
            margin-bottom: 10px;
        }

        .info-label {
            font-weight: bold;
            color: #555;
            display: inline-block;
            width: 120px;
        }

        .info-value {
            color: #333;
        }

        .amount-section {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 25px 0;
        }

        .amount-label {
            font-size: 16px;
            color: #666;
            margin-bottom: 10px;
        }

        .amount-value {
            font-size: 32px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 5px;
        }

        .amount-words {
            font-size: 14px;
            color: #666;
            font-style: italic;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
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
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            background: #f9fafb;
        }

        .invoice-title {
            font-weight: bold;
            color: #374151;
            margin-bottom: 10px;
            border-bottom: 1px solid #d1d5db;
            padding-bottom: 5px;
        }

        .notes-section {
            margin-top: 25px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #2563eb;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #666;
            font-size: 12px;
        }

        .signature-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-top: 40px;
            padding-top: 20px;
        }

        .signature-box {
            text-align: center;
            border-top: 1px solid #333;
            padding-top: 10px;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            
            .receipt {
                max-width: none;
                margin: 0;
                padding: 15px;
            }
            
            .no-print {
                display: none !important;
            }
        }

        @page {
            margin: 1cm;
            size: A4;
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
                <div>
                    <div class="info-item">
                        <span class="info-label">تاريخ الدفع:</span>
                        <span class="info-value">{{ $payment->payment_date->format('Y-m-d') }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">طريقة الدفع:</span>
                        <span class="info-value">
                            @switch($payment->payment_method)
                                @case('cash')
                                    نقدي
                                    @break
                                @case('bank_transfer')
                                    تحويل مصرفي
                                    @break
                                @case('check')
                                    شيك
                                    @break
                                @case('credit_card')
                                    بطاقة ائتمان
                                    @break
                                @case('mobile_payment')
                                    دفع عبر الهاتف
                                    @break
                                @default
                                    {{ $payment->payment_method }}
                            @endswitch
                        </span>
                    </div>
                    @if($payment->reference_number)
                    <div class="info-item">
                        <span class="info-label">رقم المرجع:</span>
                        <span class="info-value">{{ $payment->reference_number }}</span>
                    </div>
                    @endif
                </div>
                <div>
                    <div class="info-item">
                        <span class="info-label">الحالة:</span>
                        <span class="status-badge 
                            @switch($payment->status)
                                @case('completed')
                                    status-completed
                                    @break
                                @case('pending')
                                    status-pending
                                    @break
                                @case('cancelled')
                                    status-cancelled
                                    @break
                            @endswitch">
                            @switch($payment->status)
                                @case('completed')
                                    مكتملة
                                    @break
                                @case('pending')
                                    معلقة
                                    @break
                                @case('cancelled')
                                    ملغية
                                    @break
                                @default
                                    {{ $payment->status }}
                            @endswitch
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">تاريخ الإنشاء:</span>
                        <span class="info-value">{{ $payment->created_at->format('Y-m-d H:i') }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">المنشئ:</span>
                        <span class="info-value">{{ $payment->creator->name ?? 'غير محدد' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer Info -->
        <div class="info-section">
            <h3 style="margin-bottom: 15px; color: #374151; border-bottom: 1px solid #d1d5db; padding-bottom: 5px;">معلومات العميل</h3>
            <div class="info-grid">
                <div>
                    <div class="info-item">
                        <span class="info-label">اسم العميل:</span>
                        <span class="info-value">{{ $payment->customer->name_ar ?: $payment->customer->name }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">رمز العميل:</span>
                        <span class="info-value">{{ $payment->customer->code }}</span>
                    </div>
                </div>
                <div>
                    @if($payment->customer->phone)
                    <div class="info-item">
                        <span class="info-label">الهاتف:</span>
                        <span class="info-value">{{ $payment->customer->phone }}</span>
                    </div>
                    @endif
                    @if($payment->customer->email)
                    <div class="info-item">
                        <span class="info-label">البريد الإلكتروني:</span>
                        <span class="info-value">{{ $payment->customer->email }}</span>
                    </div>
                    @endif
                </div>
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
                <div>
                    <div class="info-item">
                        <span class="info-label">رقم الفاتورة:</span>
                        <span class="info-value">{{ $payment->invoice->invoice_number }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">إجمالي الفاتورة:</span>
                        <span class="info-value">{{ number_format($payment->invoice->total_amount) }} د.ع</span>
                    </div>
                </div>
                <div>
                    <div class="info-item">
                        <span class="info-label">المبلغ المدفوع:</span>
                        <span class="info-value">{{ number_format($payment->invoice->paid_amount) }} د.ع</span>
                    </div>
                    <div class="info-item">
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
            <div class="signature-box">
                <div>توقيع المستلم</div>
            </div>
            <div class="signature-box">
                <div>توقيع المحاسب</div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>تم إنشاء هذا الإيصال تلقائياً بواسطة نظام {{ config('app.name', 'MaxCon SaaS') }}</p>
            <p>تاريخ الطباعة: {{ now()->format('Y-m-d H:i:s') }}</p>
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
