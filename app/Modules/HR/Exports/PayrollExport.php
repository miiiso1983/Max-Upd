<?php

namespace App\Modules\HR\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PayrollExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    protected $payrolls;
    protected $filters;

    public function __construct($payrolls, $filters = [])
    {
        $this->payrolls = $payrolls;
        $this->filters = $filters;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->payrolls;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'رقم الموظف',
            'اسم الموظف',
            'القسم',
            'المنصب',
            'بداية الفترة',
            'نهاية الفترة',
            'الراتب الأساسي',
            'البدلات',
            'المكافآت',
            'ساعات إضافية',
            'أجر الساعات الإضافية',
            'إجمالي الاستحقاقات',
            'ضريبة الدخل',
            'الضمان الاجتماعي',
            'التأمين الصحي',
            'استقطاعات أخرى',
            'إجمالي الاستقطاعات',
            'صافي الراتب',
            'العملة',
            'الحالة',
            'ملاحظات',
            'تاريخ الإنشاء',
        ];
    }

    /**
     * @param mixed $payroll
     * @return array
     */
    public function map($payroll): array
    {
        return [
            $payroll->employee->employee_id ?? '',
            ($payroll->employee->first_name_ar ?: $payroll->employee->first_name) . ' ' . 
            ($payroll->employee->last_name_ar ?: $payroll->employee->last_name),
            optional($payroll->employee->department)->name_ar ?: optional($payroll->employee->department)->name ?: '',
            optional($payroll->employee->position)->title_ar ?: optional($payroll->employee->position)->title ?: '',
            $payroll->pay_period_start,
            $payroll->pay_period_end,
            number_format($payroll->basic_salary, 2),
            number_format($payroll->allowances, 2),
            number_format($payroll->bonuses, 2),
            $payroll->overtime_hours,
            number_format($payroll->overtime_rate, 2),
            number_format($payroll->gross_salary, 2),
            number_format($payroll->tax_deduction, 2),
            number_format($payroll->social_security_deduction, 2),
            number_format($payroll->health_insurance_deduction, 2),
            number_format($payroll->other_deductions, 2),
            number_format($payroll->total_deductions, 2),
            number_format($payroll->net_salary, 2),
            $payroll->currency,
            $this->getStatusText($payroll->status),
            $payroll->notes ?? '',
            $payroll->created_at->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        // Set RTL direction
        $sheet->setRightToLeft(true);

        // Style the header row
        $sheet->getStyle('A1:V1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F46E5'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Style data rows
        $lastRow = $this->payrolls->count() + 1;
        $sheet->getStyle("A2:V{$lastRow}")->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC'],
                ],
            ],
        ]);

        // Alternate row colors
        for ($i = 2; $i <= $lastRow; $i++) {
            if ($i % 2 == 0) {
                $sheet->getStyle("A{$i}:V{$i}")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F8FAFC'],
                    ],
                ]);
            }
        }

        // Set row height
        $sheet->getDefaultRowDimension()->setRowHeight(25);
        $sheet->getRowDimension('1')->setRowHeight(35);

        return [];
    }

    /**
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 15, // رقم الموظف
            'B' => 25, // اسم الموظف
            'C' => 20, // القسم
            'D' => 20, // المنصب
            'E' => 15, // بداية الفترة
            'F' => 15, // نهاية الفترة
            'G' => 18, // الراتب الأساسي
            'H' => 15, // البدلات
            'I' => 15, // المكافآت
            'J' => 15, // ساعات إضافية
            'K' => 18, // أجر الساعات الإضافية
            'L' => 20, // إجمالي الاستحقاقات
            'M' => 15, // ضريبة الدخل
            'N' => 18, // الضمان الاجتماعي
            'O' => 15, // التأمين الصحي
            'P' => 18, // استقطاعات أخرى
            'Q' => 20, // إجمالي الاستقطاعات
            'R' => 18, // صافي الراتب
            'S' => 10, // العملة
            'T' => 15, // الحالة
            'U' => 30, // ملاحظات
            'V' => 20, // تاريخ الإنشاء
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'كشوف الرواتب';
    }

    /**
     * Get status text in Arabic
     */
    private function getStatusText($status)
    {
        $statusMap = [
            'draft' => 'مسودة',
            'pending' => 'في الانتظار',
            'approved' => 'معتمد',
            'paid' => 'مدفوع',
            'cancelled' => 'ملغى',
        ];

        return $statusMap[$status] ?? $status;
    }
}
