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

class AttendanceExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    protected $attendances;
    protected $filters;

    public function __construct($attendances, $filters = [])
    {
        $this->attendances = $attendances;
        $this->filters = $filters;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->attendances;
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
            'التاريخ',
            'وقت الحضور',
            'وقت الانصراف',
            'ساعات العمل',
            'إجمالي الساعات',
            'الحالة',
            'ملاحظات',
            'تاريخ الإنشاء',
        ];
    }

    /**
     * @param mixed $attendance
     * @return array
     */
    public function map($attendance): array
    {
        return [
            $attendance->employee->employee_id ?? '',
            ($attendance->employee->first_name_ar ?: $attendance->employee->first_name) . ' ' . 
            ($attendance->employee->last_name_ar ?: $attendance->employee->last_name),
            optional($attendance->employee->department)->name_ar ?: optional($attendance->employee->department)->name ?: '',
            optional($attendance->employee->position)->title_ar ?: optional($attendance->employee->position)->title ?: '',
            $attendance->date,
            $attendance->check_in_time ? $attendance->check_in_time->format('H:i:s') : '',
            $attendance->check_out_time ? $attendance->check_out_time->format('H:i:s') : '',
            $attendance->formatted_working_hours ?? '',
            $attendance->total_hours ?? '',
            $this->getStatusText($attendance->status),
            $attendance->notes ?? '',
            $attendance->created_at->format('Y-m-d H:i:s'),
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
        $sheet->getStyle('A1:L1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '059669'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Style data rows
        $lastRow = $this->attendances->count() + 1;
        $sheet->getStyle("A2:L{$lastRow}")->applyFromArray([
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
                $sheet->getStyle("A{$i}:L{$i}")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F0FDF4'],
                    ],
                ]);
            }
        }

        // Color code status cells
        for ($i = 2; $i <= $lastRow; $i++) {
            $statusCell = "J{$i}";
            $statusValue = $sheet->getCell($statusCell)->getValue();
            
            $statusColors = [
                'حاضر' => 'D1FAE5', // Green
                'متأخر' => 'FEF3C7', // Yellow
                'غائب' => 'FEE2E2', // Red
                'إجازة' => 'E0E7FF', // Blue
            ];

            if (isset($statusColors[$statusValue])) {
                $sheet->getStyle($statusCell)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => $statusColors[$statusValue]],
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
            'E' => 15, // التاريخ
            'F' => 15, // وقت الحضور
            'G' => 15, // وقت الانصراف
            'H' => 15, // ساعات العمل
            'I' => 15, // إجمالي الساعات
            'J' => 15, // الحالة
            'K' => 30, // ملاحظات
            'L' => 20, // تاريخ الإنشاء
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'سجل الحضور والانصراف';
    }

    /**
     * Get status text in Arabic
     */
    private function getStatusText($status)
    {
        $statusMap = [
            'present' => 'حاضر',
            'late' => 'متأخر',
            'absent' => 'غائب',
            'leave' => 'إجازة',
            'sick_leave' => 'إجازة مرضية',
            'vacation' => 'إجازة',
            'holiday' => 'عطلة',
        ];

        return $statusMap[$status] ?? $status;
    }
}
