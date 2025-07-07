<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Modules\Inventory\Models\Product;
use App\Modules\Inventory\Models\Category;
use App\Modules\Inventory\Models\Manufacturer;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProductImportController extends Controller
{
    /**
     * Download Excel template for product import
     */
    public function downloadTemplate()
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Set headers
            $headers = [
                'A1' => 'اسم المنتج*',
                'B1' => 'الوصف',
                'C1' => 'الفئة*',
                'D1' => 'العلامة التجارية',
                'E1' => 'الشركة المصنعة',
                'F1' => 'رمز المنتج (SKU)',
                'G1' => 'الباركود',
                'H1' => 'وحدة القياس*',
                'I1' => 'سعر التكلفة',
                'J1' => 'سعر البيع',
                'K1' => 'الحد الأدنى للمخزون',
                'L1' => 'الحد الأقصى للمخزون',
                'M1' => 'نقطة إعادة الطلب',
                'N1' => 'يتطلب وصفة طبية (نعم/لا)',
                'O1' => 'مادة خاضعة للرقابة (نعم/لا)',
                'P1' => 'تتبع انتهاء الصلاحية (نعم/لا)',
                'Q1' => 'تتبع الدفعات (نعم/لا)',
                'R1' => 'الحالة (نشط/غير نشط)',
                'S1' => 'ملاحظات'
            ];

            foreach ($headers as $cell => $value) {
                $sheet->setCellValue($cell, $value);
                $sheet->getStyle($cell)->getFont()->setBold(true);
                $sheet->getStyle($cell)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('E3F2FD');
            }

            // Add sample data
            $sampleData = [
                [
                    'باراسيتامول 500 مجم',
                    'مسكن للألم وخافض للحرارة',
                    'أدوية - مسكنات',
                    'فايزر',
                    'شركة فايزر للأدوية',
                    'PAR500',
                    '1234567890123',
                    'قرص',
                    '0.50',
                    '1.00',
                    '100',
                    '1000',
                    '200',
                    'لا',
                    'لا',
                    'نعم',
                    'نعم',
                    'نشط',
                    'مسكن آمن للاستخدام'
                ],
                [
                    'فيتامين د 1000 وحدة',
                    'مكمل غذائي لتقوية العظام',
                    'مكملات غذائية',
                    'نوفارتيس',
                    'شركة نوفارتيس',
                    'VITD1000',
                    '9876543210987',
                    'كبسولة',
                    '2.00',
                    '4.00',
                    '50',
                    '500',
                    '100',
                    'لا',
                    'لا',
                    'نعم',
                    'لا',
                    'نشط',
                    'يؤخذ مع الطعام'
                ]
            ];

            $row = 2;
            foreach ($sampleData as $data) {
                $col = 'A';
                foreach ($data as $value) {
                    $sheet->setCellValue($col . $row, $value);
                    $col++;
                }
                $row++;
            }

            // Auto-size columns
            foreach (range('A', 'S') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Add instructions sheet
            $instructionsSheet = $spreadsheet->createSheet();
            $instructionsSheet->setTitle('تعليمات');
            
            $instructions = [
                'تعليمات استيراد المنتجات:',
                '',
                '1. املأ البيانات في الورقة الأولى (Sheet1)',
                '2. الحقول المطلوبة مميزة بعلامة *',
                '3. استخدم القيم التالية للحقول المحددة:',
                '   - وحدة القياس: قطعة، قرص، كبسولة، زجاجة، علبة، كيس',
                '   - يتطلب وصفة طبية: نعم أو لا',
                '   - مادة خاضعة للرقابة: نعم أو لا',
                '   - تتبع انتهاء الصلاحية: نعم أو لا',
                '   - تتبع الدفعات: نعم أو لا',
                '   - الحالة: نشط أو غير نشط',
                '4. تأكد من صحة البيانات قبل الرفع',
                '5. يمكن رفع حتى 1000 منتج في المرة الواحدة',
                '',
                'ملاحظات مهمة:',
                '- إذا لم يتم تحديد SKU، سيتم إنشاؤه تلقائياً',
                '- إذا لم توجد الفئة، سيتم إنشاؤها تلقائياً',
                '- إذا لم توجد الشركة المصنعة، سيتم إنشاؤها تلقائياً',
                '- الأسعار يجب أن تكون أرقام موجبة',
                '- الكميات يجب أن تكون أرقام صحيحة موجبة'
            ];

            $row = 1;
            foreach ($instructions as $instruction) {
                $instructionsSheet->setCellValue('A' . $row, $instruction);
                if ($row == 1) {
                    $instructionsSheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(14);
                }
                $row++;
            }

            $instructionsSheet->getColumnDimension('A')->setWidth(80);

            // Set active sheet back to first sheet
            $spreadsheet->setActiveSheetIndex(0);

            // Save file
            $filename = 'products_import_template_' . now()->format('Y_m_d_H_i_s') . '.xlsx';
            $filepath = storage_path('app/public/templates/' . $filename);
            
            // Create directory if it doesn't exist
            if (!file_exists(dirname($filepath))) {
                mkdir(dirname($filepath), 0755, true);
            }

            $writer = new Xlsx($spreadsheet);
            $writer->save($filepath);

            return response()->download($filepath, $filename)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إنشاء النموذج: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Import products from Excel file
     */
    public function importProducts(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:10240', // 10MB max
            'skip_duplicates' => 'boolean',
            'update_existing' => 'boolean'
        ]);

        try {
            $file = $request->file('excel_file');
            $skipDuplicates = $request->boolean('skip_duplicates', true);
            $updateExisting = $request->boolean('update_existing', false);

            // Process the Excel file
            $results = $this->processExcelFile($file, $skipDuplicates, $updateExisting);

            return response()->json([
                'success' => true,
                'message' => "تم استيراد {$results['imported']} منتج بنجاح من أصل {$results['total_rows']} صف",
                'summary' => $results,
                'errors' => $results['validation_errors'] ?? []
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء استيراد الملف: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process Excel file and import products
     */
    private function processExcelFile($file, $skipDuplicates, $updateExisting)
    {
        $results = [
            'total_rows' => 0,
            'imported' => 0,
            'updated' => 0,
            'skipped' => 0,
            'failed' => 0,
            'validation_errors' => []
        ];

        try {
            $spreadsheet = IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            // Remove header row
            $headers = array_shift($rows);
            $results['total_rows'] = count($rows);

            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2; // +2 because we removed header and arrays are 0-indexed
                
                // Skip empty rows
                if (empty(array_filter($row))) {
                    $results['skipped']++;
                    continue;
                }

                try {
                    $productData = $this->mapRowToProductData($row);
                    $validation = $this->validateProductData($productData, $rowNumber);

                    if (!$validation['valid']) {
                        $results['validation_errors'] = array_merge($results['validation_errors'], $validation['errors']);
                        $results['failed']++;
                        continue;
                    }

                    // Check if product exists
                    $existingProduct = null;
                    if (!empty($productData['sku'])) {
                        $existingProduct = Product::where('sku', $productData['sku'])->first();
                    }
                    if (!$existingProduct && !empty($productData['name'])) {
                        $existingProduct = Product::where('name', $productData['name'])->first();
                    }

                    if ($existingProduct) {
                        if ($updateExisting) {
                            $existingProduct->update($productData);
                            $results['updated']++;
                        } elseif ($skipDuplicates) {
                            $results['skipped']++;
                        } else {
                            $results['validation_errors'][] = [
                                'row' => $rowNumber,
                                'field' => 'اسم المنتج',
                                'message' => 'المنتج موجود مسبقاً'
                            ];
                            $results['failed']++;
                        }
                    } else {
                        // Create new product
                        Product::create($productData);
                        $results['imported']++;
                    }

                } catch (\Exception $e) {
                    $results['validation_errors'][] = [
                        'row' => $rowNumber,
                        'field' => 'عام',
                        'message' => 'خطأ في معالجة البيانات: ' . $e->getMessage()
                    ];
                    $results['failed']++;
                }
            }

        } catch (\Exception $e) {
            throw new \Exception('خطأ في قراءة ملف Excel: ' . $e->getMessage());
        }

        return $results;
    }

    /**
     * Map Excel row to product data array
     */
    private function mapRowToProductData($row)
    {
        return [
            'name' => trim($row[0] ?? ''),
            'description' => trim($row[1] ?? ''),
            'category_id' => $this->getCategoryId(trim($row[2] ?? '')),
            'brand' => trim($row[3] ?? ''),
            'manufacturer_id' => $this->getManufacturerId(trim($row[4] ?? '')),
            'sku' => trim($row[5] ?? '') ?: $this->generateSku(trim($row[0] ?? '')),
            'barcode' => trim($row[6] ?? ''),
            'unit_of_measure' => $this->normalizeUnit(trim($row[7] ?? '')),
            'purchase_price' => $this->parseDecimal($row[8] ?? 0),
            'selling_price' => $this->parseDecimal($row[9] ?? 0),
            'min_stock_level' => $this->parseInt($row[10] ?? 0),
            'max_stock_level' => $this->parseInt($row[11] ?? 0),
            'reorder_level' => $this->parseInt($row[12] ?? 0),
            'is_prescription_required' => $this->parseBoolean($row[13] ?? 'لا'),
            'is_controlled_substance' => $this->parseBoolean($row[14] ?? 'لا'),
            'expiry_tracking' => $this->parseBoolean($row[15] ?? 'نعم'),
            'batch_tracking' => $this->parseBoolean($row[16] ?? 'نعم'),
            'is_active' => $this->parseStatus($row[17] ?? 'نشط'),
            'notes' => trim($row[18] ?? ''),
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ];
    }

    /**
     * Validate product data
     */
    private function validateProductData($data, $rowNumber)
    {
        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'unit_of_measure' => 'required|string',
            'purchase_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'min_stock_level' => 'nullable|integer|min:0',
            'max_stock_level' => 'nullable|integer|min:0',
            'reorder_level' => 'nullable|integer|min:0',
        ]);

        $errors = [];
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $errors[] = [
                    'row' => $rowNumber,
                    'field' => 'متعدد',
                    'message' => $error
                ];
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Get or create category ID
     */
    private function getCategoryId($categoryName)
    {
        if (empty($categoryName)) {
            return null;
        }

        $category = Category::where('name', $categoryName)->first();
        if (!$category) {
            $category = Category::create([
                'name' => $categoryName,
                'name_ar' => $categoryName,
                'description' => 'تم إنشاؤها تلقائياً من الاستيراد',
                'is_active' => true,
                'created_by' => auth()->id(),
            ]);
        }

        return $category->id;
    }

    /**
     * Get or create manufacturer ID
     */
    private function getManufacturerId($manufacturerName)
    {
        if (empty($manufacturerName)) {
            return null;
        }

        $manufacturer = Manufacturer::where('name', $manufacturerName)->first();
        if (!$manufacturer) {
            $manufacturer = Manufacturer::create([
                'name' => $manufacturerName,
                'description' => 'تم إنشاؤها تلقائياً من الاستيراد',
                'country' => 'غير محدد',
                'is_active' => true,
                'created_by' => auth()->id(),
            ]);
        }

        return $manufacturer->id;
    }

    /**
     * Generate SKU from product name
     */
    private function generateSku($productName)
    {
        if (empty($productName)) {
            return 'PRD-' . strtoupper(Str::random(8));
        }

        $sku = 'PRD-' . strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $productName), 0, 6));

        // Ensure uniqueness
        $counter = 1;
        $originalSku = $sku;
        while (Product::where('sku', $sku)->exists()) {
            $sku = $originalSku . '-' . $counter;
            $counter++;
        }

        return $sku;
    }

    /**
     * Normalize unit of measure
     */
    private function normalizeUnit($unit)
    {
        $unitMap = [
            'قطعة' => 'piece',
            'قرص' => 'tablet',
            'كبسولة' => 'capsule',
            'زجاجة' => 'bottle',
            'علبة' => 'box',
            'كيس' => 'bag',
            'أمبولة' => 'ampoule',
            'فيال' => 'vial',
            'أنبوب' => 'tube',
            'شريط' => 'strip'
        ];

        return $unitMap[$unit] ?? 'piece';
    }

    /**
     * Parse decimal value
     */
    private function parseDecimal($value)
    {
        if (empty($value) || !is_numeric($value)) {
            return 0;
        }
        return (float) $value;
    }

    /**
     * Parse integer value
     */
    private function parseInt($value)
    {
        if (empty($value) || !is_numeric($value)) {
            return 0;
        }
        return (int) $value;
    }

    /**
     * Parse boolean value
     */
    private function parseBoolean($value)
    {
        $value = trim(strtolower($value));
        return in_array($value, ['نعم', 'yes', '1', 'true']);
    }

    /**
     * Parse status value
     */
    private function parseStatus($value)
    {
        $value = trim(strtolower($value));
        return in_array($value, ['نشط', 'active', '1', 'true']);
    }
}
