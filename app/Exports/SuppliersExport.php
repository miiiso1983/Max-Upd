<?php

namespace App\Exports;

use App\Modules\Suppliers\Models\Supplier;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;

class SuppliersExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
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
        return Supplier::when($this->filters['type'] ?? null, function ($query, $type) {
                         return $query->where('type', $type);
                     })
                     ->when($this->filters['status'] ?? null, function ($query, $status) {
                         return $query->where('status', $status);
                     })
                     ->when($this->filters['country'] ?? null, function ($query, $country) {
                         return $query->where('country', $country);
                     })
                     ->when($this->filters['is_preferred'] ?? null, function ($query, $isPreferred) {
                         return $query->where('is_preferred', $isPreferred);
                     })
                     ->orderBy('name')
                     ->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'رقم المورد',
            'اسم المورد',
            'الاسم بالعربية',
            'النوع',
            'كود المورد',
            'البريد الإلكتروني',
            'الهاتف',
            'الجوال',
            'الفاكس',
            'الموقع الإلكتروني',
            'العنوان',
            'المدينة',
            'المحافظة',
            'البلد',
            'الرمز البريدي',
            'الرقم الضريبي',
            'رقم الترخيص',
            'الشخص المسؤول',
            'هاتف المسؤول',
            'بريد المسؤول',
            'شروط الدفع (أيام)',
            'حد الائتمان',
            'العملة',
            'اسم البنك',
            'رقم الحساب',
            'IBAN',
            'SWIFT Code',
            'التقييم',
            'مورد مفضل',
            'الحالة',
            'تاريخ الإنشاء',
            'آخر تحديث',
            'ملاحظات'
        ];
    }

    /**
     * @param mixed $supplier
     * @return array
     */
    public function map($supplier): array
    {
        return [
            $supplier->id,
            $supplier->name,
            $supplier->name_ar,
            $this->getTypeLabel($supplier->type),
            $supplier->code,
            $supplier->email,
            $supplier->phone,
            $supplier->mobile,
            $supplier->fax,
            $supplier->website,
            $supplier->address,
            $supplier->city,
            $supplier->state,
            $supplier->country,
            $supplier->postal_code,
            $supplier->tax_number,
            $supplier->license_number,
            $supplier->contact_person,
            $supplier->contact_phone,
            $supplier->contact_email,
            $supplier->payment_terms,
            number_format($supplier->credit_limit ?? 0, 2),
            $supplier->currency,
            $supplier->bank_name,
            $supplier->bank_account,
            $supplier->iban,
            $supplier->swift_code,
            $supplier->rating ? number_format($supplier->rating, 1) : '',
            $supplier->is_preferred ? 'نعم' : 'لا',
            $this->getStatusLabel($supplier->status),
            $supplier->created_at->format('Y-m-d H:i:s'),
            $supplier->updated_at->format('Y-m-d H:i:s'),
            $supplier->notes
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as header
            1 => [
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
                    'vertical' => Alignment::VERTICAL_CENTER,
                ]
            ],
            // Style all cells
            'A:AG' => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_RIGHT,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'font' => [
                    'size' => 10
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 10,  // رقم المورد
            'B' => 25,  // اسم المورد
            'C' => 25,  // الاسم بالعربية
            'D' => 15,  // النوع
            'E' => 15,  // كود المورد
            'F' => 25,  // البريد الإلكتروني
            'G' => 15,  // الهاتف
            'H' => 15,  // الجوال
            'I' => 15,  // الفاكس
            'J' => 25,  // الموقع الإلكتروني
            'K' => 30,  // العنوان
            'L' => 15,  // المدينة
            'M' => 15,  // المحافظة
            'N' => 15,  // البلد
            'O' => 12,  // الرمز البريدي
            'P' => 15,  // الرقم الضريبي
            'Q' => 15,  // رقم الترخيص
            'R' => 20,  // الشخص المسؤول
            'S' => 15,  // هاتف المسؤول
            'T' => 25,  // بريد المسؤول
            'U' => 12,  // شروط الدفع
            'V' => 15,  // حد الائتمان
            'W' => 10,  // العملة
            'X' => 20,  // اسم البنك
            'Y' => 20,  // رقم الحساب
            'Z' => 25,  // IBAN
            'AA' => 15, // SWIFT Code
            'AB' => 10, // التقييم
            'AC' => 12, // مورد مفضل
            'AD' => 12, // الحالة
            'AE' => 18, // تاريخ الإنشاء
            'AF' => 18, // آخر تحديث
            'AG' => 30, // ملاحظات
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'قائمة الموردين';
    }

    /**
     * Get type label in Arabic
     */
    private function getTypeLabel($type)
    {
        $types = [
            'manufacturer' => 'مصنع',
            'distributor' => 'موزع',
            'wholesaler' => 'تاجر جملة',
            'importer' => 'مستورد',
            'local_supplier' => 'مورد محلي'
        ];

        return $types[$type] ?? $type;
    }

    /**
     * Get status label in Arabic
     */
    private function getStatusLabel($status)
    {
        $statuses = [
            'active' => 'نشط',
            'inactive' => 'غير نشط',
            'suspended' => 'معلق',
            'blocked' => 'محظور'
        ];

        return $statuses[$status] ?? $status;
    }
}
