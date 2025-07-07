<?php

namespace App\Exports;

use App\Modules\Sales\Models\Invoice;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class InvoicesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $query = Invoice::with(['customer', 'salesOrder.warehouse', 'creator']);

        // Apply filters
        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (!empty($this->filters['customer_id'])) {
            $query->where('customer_id', $this->filters['customer_id']);
        }

        if (!empty($this->filters['date_from'])) {
            $query->whereDate('invoice_date', '>=', $this->filters['date_from']);
        }

        if (!empty($this->filters['date_to'])) {
            $query->whereDate('invoice_date', '<=', $this->filters['date_to']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'رقم الفاتورة',
            'العميل',
            'تاريخ الفاتورة',
            'تاريخ الاستحقاق',
            'الحالة',
            'المجموع الفرعي',
            'الضريبة',
            'الخصم',
            'المجموع الإجمالي',
            'المدفوع',
            'المتبقي',
            'المستودع',
            'أمر البيع',
            'المنشئ',
            'تاريخ الإنشاء',
            'ملاحظات'
        ];
    }

    /**
     * @param Invoice $invoice
     */
    public function map($invoice): array
    {
        return [
            $invoice->invoice_number,
            $invoice->customer ? ($invoice->customer->name_ar ?: $invoice->customer->name) : '',
            $invoice->invoice_date ? $invoice->invoice_date->format('Y/m/d') : '',
            $invoice->due_date ? $invoice->due_date->format('Y/m/d') : '',
            $this->getStatusInArabic($invoice->status),
            number_format($invoice->subtotal, 2),
            number_format($invoice->tax_amount, 2),
            number_format($invoice->discount_amount, 2),
            number_format($invoice->total_amount, 2),
            number_format($invoice->paid_amount, 2),
            number_format($invoice->total_amount - $invoice->paid_amount, 2),
            ($invoice->salesOrder && $invoice->salesOrder->warehouse) ? ($invoice->salesOrder->warehouse->name_ar ?: $invoice->salesOrder->warehouse->name) : '',
            $invoice->salesOrder ? $invoice->salesOrder->order_number : '',
            $invoice->creator ? $invoice->creator->name : '',
            $invoice->created_at ? $invoice->created_at->format('Y/m/d H:i') : '',
            $invoice->notes ?: ''
        ];
    }

    /**
     * @param Worksheet $sheet
     */
    public function styles(Worksheet $sheet)
    {
        // Style the header row
        $sheet->getStyle('A1:P1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);

        // Set row height for header
        $sheet->getRowDimension('1')->setRowHeight(25);

        // Set text direction to RTL
        $sheet->setRightToLeft(true);

        return [];
    }

    /**
     * Get status in Arabic
     */
    private function getStatusInArabic($status)
    {
        $statuses = [
            'draft' => 'مسودة',
            'pending' => 'معلقة',
            'paid' => 'مدفوعة',
            'overdue' => 'متأخرة',
            'cancelled' => 'ملغية'
        ];

        return $statuses[$status] ?? $status;
    }
}
