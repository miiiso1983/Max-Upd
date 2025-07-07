<?php

namespace App\Exports;

use App\Modules\Inventory\Models\Product;
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

class ProductsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
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
        return Product::with(['category', 'manufacturer'])
                     ->when($this->filters['category_id'] ?? null, function ($query, $categoryId) {
                         return $query->where('category_id', $categoryId);
                     })
                     ->when($this->filters['manufacturer_id'] ?? null, function ($query, $manufacturerId) {
                         return $query->where('manufacturer_id', $manufacturerId);
                     })
                     ->when($this->filters['status'] ?? null, function ($query, $status) {
                         if ($status === 'active') {
                             return $query->where('is_active', true);
                         } elseif ($status === 'inactive') {
                             return $query->where('is_active', false);
                         }
                         return $query;
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
            'رقم المنتج',
            'اسم المنتج',
            'الوصف',
            'رمز المنتج (SKU)',
            'الباركود',
            'الفئة',
            'الشركة المصنعة',
            'وحدة القياس',
            'سعر التكلفة',
            'سعر البيع',
            'المخزون الحالي',
            'الحد الأدنى للمخزون',
            'الحد الأقصى للمخزون',
            'نقطة إعادة الطلب',
            'يتطلب وصفة طبية',
            'مادة خاضعة للرقابة',
            'تتبع انتهاء الصلاحية',
            'تتبع الدفعات',
            'الحالة',
            'تاريخ الإنشاء',
            'آخر تحديث',
            'ملاحظات'
        ];
    }

    /**
     * @param mixed $product
     * @return array
     */
    public function map($product): array
    {
        return [
            $product->id,
            $product->name,
            $product->description,
            $product->sku,
            $product->barcode,
            $product->category->name ?? 'غير محدد',
            $product->manufacturer->name ?? 'غير محدد',
            $this->getUnitLabel($product->unit_of_measure),
            number_format($product->purchase_price ?? 0, 2),
            number_format($product->selling_price ?? 0, 2),
            $product->getCurrentStock(),
            $product->min_stock_level ?? 0,
            $product->max_stock_level ?? 0,
            $product->reorder_level ?? 0,
            $product->is_prescription_required ? 'نعم' : 'لا',
            $product->is_controlled_substance ? 'نعم' : 'لا',
            $product->expiry_tracking ? 'نعم' : 'لا',
            $product->batch_tracking ? 'نعم' : 'لا',
            $product->is_active ? 'نشط' : 'غير نشط',
            $product->created_at->format('Y-m-d H:i:s'),
            $product->updated_at->format('Y-m-d H:i:s'),
            $product->notes
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
            'A:V' => [
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
            'A' => 10,  // رقم المنتج
            'B' => 30,  // اسم المنتج
            'C' => 40,  // الوصف
            'D' => 15,  // SKU
            'E' => 15,  // الباركود
            'F' => 20,  // الفئة
            'G' => 25,  // الشركة المصنعة
            'H' => 15,  // وحدة القياس
            'I' => 12,  // سعر التكلفة
            'J' => 12,  // سعر البيع
            'K' => 12,  // المخزون الحالي
            'L' => 15,  // الحد الأدنى
            'M' => 15,  // الحد الأقصى
            'N' => 15,  // نقطة إعادة الطلب
            'O' => 15,  // يتطلب وصفة
            'P' => 15,  // مادة خاضعة للرقابة
            'Q' => 15,  // تتبع انتهاء الصلاحية
            'R' => 15,  // تتبع الدفعات
            'S' => 10,  // الحالة
            'T' => 18,  // تاريخ الإنشاء
            'U' => 18,  // آخر تحديث
            'V' => 30,  // ملاحظات
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'قائمة المنتجات';
    }

    /**
     * Get unit label in Arabic
     */
    private function getUnitLabel($unit)
    {
        $units = [
            'piece' => 'قطعة',
            'tablet' => 'قرص',
            'capsule' => 'كبسولة',
            'bottle' => 'زجاجة',
            'box' => 'علبة',
            'bag' => 'كيس',
            'ampoule' => 'أمبولة',
            'vial' => 'فيال',
            'tube' => 'أنبوب',
            'strip' => 'شريط',
            'ml' => 'مل',
            'mg' => 'مجم',
            'g' => 'جرام',
            'kg' => 'كيلوجرام'
        ];

        return $units[$unit] ?? $unit;
    }
}
