<x-mail::message>
# فاتورة رقم {{ $invoice->invoice_number }}

مرحباً {{ $invoice->customer->name_ar ?: $invoice->customer->name }},

نرسل لكم فاتورة المبيعات التالية:

**تفاصيل الفاتورة:**
- رقم الفاتورة: {{ $invoice->invoice_number }}
- تاريخ الفاتورة: {{ $invoice->invoice_date->format('Y/m/d') }}
- تاريخ الاستحقاق: {{ $invoice->due_date ? $invoice->due_date->format('Y/m/d') : 'غير محدد' }}
- المبلغ الإجمالي: {{ number_format($invoice->total_amount, 2) }} د.ع

@if($customMessage)
**رسالة خاصة:**
{{ $customMessage }}
@endif

<x-mail::table>
| المنتج | الكمية | سعر الوحدة | المجموع |
|:-------|:------:|:----------:|:--------:|
@foreach($invoice->items as $item)
| {{ $item->product->name_ar ?: $item->product->name }} | {{ number_format($item->quantity) }} | {{ number_format($item->unit_price, 2) }} د.ع | {{ number_format($item->total_amount, 2) }} د.ع |
@endforeach
</x-mail::table>

**ملخص المبالغ:**
- المجموع الفرعي: {{ number_format($invoice->subtotal, 2) }} د.ع
- الضريبة: {{ number_format($invoice->tax_amount, 2) }} د.ع
- الخصم: {{ number_format($invoice->discount_amount, 2) }} د.ع
- **المجموع الإجمالي: {{ number_format($invoice->total_amount, 2) }} د.ع**

<x-mail::button :url="route('sales.invoices.show', $invoice->id)">
عرض الفاتورة
</x-mail::button>

@if($invoice->notes)
**ملاحظات:**
{{ $invoice->notes }}
@endif

شكراً لتعاملكم معنا،<br>
فريق {{ config('app.name', 'MaxCon ERP') }}

---
هذا بريد إلكتروني تلقائي، يرجى عدم الرد عليه مباشرة.
للاستفسارات، يرجى التواصل معنا على: support@maxcon-erp.com
</x-mail::message>
