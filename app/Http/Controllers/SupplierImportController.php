<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Modules\Suppliers\Models\Supplier;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SupplierImportController extends Controller
{
    /**
     * Download Excel template for supplier import
     */
    public function downloadTemplate()
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Set headers
            $headers = [
                'A1' => 'اسم المورد*',
                'B1' => 'الاسم بالعربية',
                'C1' => 'النوع*',
                'D1' => 'كود المورد',
                'E1' => 'البريد الإلكتروني*',
                'F1' => 'الهاتف',
                'G1' => 'الجوال',
                'H1' => 'الفاكس',
                'I1' => 'الموقع الإلكتروني',
                'J1' => 'العنوان',
                'K1' => 'المدينة',
                'L1' => 'المحافظة',
                'M1' => 'البلد',
                'N1' => 'الرمز البريدي',
                'O1' => 'الرقم الضريبي',
                'P1' => 'رقم الترخيص',
                'Q1' => 'الشخص المسؤول',
                'R1' => 'هاتف المسؤول',
                'S1' => 'بريد المسؤول',
                'T1' => 'شروط الدفع (أيام)',
                'U1' => 'حد الائتمان',
                'V1' => 'العملة',
                'W1' => 'اسم البنك',
                'X1' => 'رقم الحساب',
                'Y1' => 'IBAN',
                'Z1' => 'SWIFT Code',
                'AA1' => 'التقييم (1-5)',
                'AB1' => 'مورد مفضل (نعم/لا)',
                'AC1' => 'الحالة',
                'AD1' => 'ملاحظات'
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
                    'شركة الأدوية المتحدة',
                    'United Pharmaceuticals',
                    'مصنع',
                    'MAN-001',
                    'info@unitedpharma.com',
                    '07901234567',
                    '07701234567',
                    '07901234568',
                    'www.unitedpharma.com',
                    'شارع الكندي، حي الجامعة',
                    'بغداد',
                    'بغداد',
                    'العراق',
                    '10001',
                    'TAX123456789',
                    'LIC987654321',
                    'أحمد محمد علي',
                    '07901234569',
                    'ahmed@unitedpharma.com',
                    '30',
                    '100000',
                    'IQD',
                    'بنك بغداد',
                    '1234567890',
                    'IQ12BBAG1234567890123456',
                    'BBAGIQBA',
                    '4.5',
                    'نعم',
                    'نشط',
                    'مورد موثوق للأدوية'
                ],
                [
                    'شركة التوزيع الطبي',
                    'Medical Distribution Co.',
                    'موزع',
                    'DIS-001',
                    'sales@medidist.com',
                    '07801234567',
                    '07601234567',
                    '',
                    'www.medidist.com',
                    'شارع الرشيد، المنصور',
                    'بغداد',
                    'بغداد',
                    'العراق',
                    '10002',
                    'TAX987654321',
                    'LIC123456789',
                    'فاطمة أحمد',
                    '07801234568',
                    'fatima@medidist.com',
                    '45',
                    '50000',
                    'IQD',
                    'بنك الرافدين',
                    '0987654321',
                    'IQ34RAFI0987654321098765',
                    'RAFIIQBA',
                    '4.0',
                    'لا',
                    'نشط',
                    'موزع للمستلزمات الطبية'
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
            $columns = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD'];
            foreach ($columns as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Add instructions sheet
            $instructionsSheet = $spreadsheet->createSheet();
            $instructionsSheet->setTitle('تعليمات');
            
            $instructions = [
                'تعليمات استيراد الموردين:',
                '',
                '1. املأ البيانات في الورقة الأولى (Sheet1)',
                '2. الحقول المطلوبة مميزة بعلامة *',
                '3. استخدم القيم التالية للحقول المحددة:',
                '   - النوع: مصنع، موزع، تاجر جملة، مستورد، مورد محلي',
                '   - العملة: IQD، USD، EUR',
                '   - مورد مفضل: نعم أو لا',
                '   - الحالة: نشط، غير نشط، معلق، محظور',
                '   - التقييم: رقم من 1 إلى 5',
                '4. تأكد من صحة البيانات قبل الرفع',
                '5. يمكن رفع حتى 500 مورد في المرة الواحدة',
                '',
                'ملاحظات مهمة:',
                '- إذا لم يتم تحديد كود المورد، سيتم إنشاؤه تلقائياً',
                '- البريد الإلكتروني يجب أن يكون فريد لكل مورد',
                '- شروط الدفع بالأيام (مثال: 30 يوم)',
                '- حد الائتمان بالعملة المحددة',
                '- التقييم اختياري ويجب أن يكون بين 1 و 5'
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
            $filename = 'suppliers_import_template_' . now()->format('Y_m_d_H_i_s') . '.xlsx';
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
     * Import suppliers from Excel file
     */
    public function importSuppliers(Request $request)
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
                'message' => "تم استيراد {$results['imported']} مورد بنجاح من أصل {$results['total_rows']} صف",
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
     * Process Excel file and import suppliers
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
                    $supplierData = $this->mapRowToSupplierData($row);
                    $validation = $this->validateSupplierData($supplierData, $rowNumber);

                    if (!$validation['valid']) {
                        $results['validation_errors'] = array_merge($results['validation_errors'], $validation['errors']);
                        $results['failed']++;
                        continue;
                    }

                    // Check if supplier exists
                    $existingSupplier = null;
                    if (!empty($supplierData['email'])) {
                        $existingSupplier = Supplier::where('email', $supplierData['email'])->first();
                    }
                    if (!$existingSupplier && !empty($supplierData['code'])) {
                        $existingSupplier = Supplier::where('code', $supplierData['code'])->first();
                    }
                    if (!$existingSupplier && !empty($supplierData['name'])) {
                        $existingSupplier = Supplier::where('name', $supplierData['name'])->first();
                    }

                    if ($existingSupplier) {
                        if ($updateExisting) {
                            $existingSupplier->update($supplierData);
                            $results['updated']++;
                        } elseif ($skipDuplicates) {
                            $results['skipped']++;
                        } else {
                            $results['validation_errors'][] = [
                                'row' => $rowNumber,
                                'field' => 'اسم المورد',
                                'message' => 'المورد موجود مسبقاً'
                            ];
                            $results['failed']++;
                        }
                    } else {
                        // Create new supplier
                        Supplier::create($supplierData);
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
     * Map Excel row to supplier data array
     */
    private function mapRowToSupplierData($row)
    {
        return [
            'name' => trim($row[0] ?? ''),
            'name_ar' => trim($row[1] ?? '') ?: trim($row[0] ?? ''),
            'type' => $this->normalizeSupplierType(trim($row[2] ?? '')),
            'code' => trim($row[3] ?? '') ?: $this->generateSupplierCode(trim($row[0] ?? '')),
            'email' => trim($row[4] ?? ''),
            'phone' => trim($row[5] ?? ''),
            'mobile' => trim($row[6] ?? ''),
            'fax' => trim($row[7] ?? ''),
            'website' => trim($row[8] ?? ''),
            'address' => trim($row[9] ?? ''),
            'city' => trim($row[10] ?? ''),
            'state' => trim($row[11] ?? ''),
            'country' => trim($row[12] ?? '') ?: 'العراق',
            'postal_code' => trim($row[13] ?? ''),
            'tax_number' => trim($row[14] ?? ''),
            'license_number' => trim($row[15] ?? ''),
            'contact_person' => trim($row[16] ?? ''),
            'contact_phone' => trim($row[17] ?? ''),
            'contact_email' => trim($row[18] ?? ''),
            'payment_terms' => $this->parseInt($row[19] ?? 30),
            'credit_limit' => $this->parseDecimal($row[20] ?? 0),
            'currency' => trim($row[21] ?? '') ?: 'IQD',
            'bank_name' => trim($row[22] ?? ''),
            'bank_account' => trim($row[23] ?? ''),
            'iban' => trim($row[24] ?? ''),
            'swift_code' => trim($row[25] ?? ''),
            'rating' => $this->parseRating($row[26] ?? null),
            'is_preferred' => $this->parseBoolean($row[27] ?? 'لا'),
            'status' => $this->normalizeStatus(trim($row[28] ?? 'نشط')),
            'notes' => trim($row[29] ?? ''),
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ];
    }

    /**
     * Validate supplier data
     */
    private function validateSupplierData($data, $rowNumber)
    {
        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'payment_terms' => 'nullable|integer|min:0',
            'credit_limit' => 'nullable|numeric|min:0',
            'rating' => 'nullable|numeric|min:1|max:5',
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
     * Generate supplier code from name
     */
    private function generateSupplierCode($supplierName)
    {
        if (empty($supplierName)) {
            return 'SUP-' . strtoupper(Str::random(6));
        }

        $code = 'SUP-' . strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $supplierName), 0, 6));

        // Ensure uniqueness
        $counter = 1;
        $originalCode = $code;
        while (Supplier::where('code', $code)->exists()) {
            $code = $originalCode . '-' . $counter;
            $counter++;
        }

        return $code;
    }

    /**
     * Normalize supplier type
     */
    private function normalizeSupplierType($type)
    {
        $typeMap = [
            'مصنع' => 'manufacturer',
            'موزع' => 'distributor',
            'تاجر جملة' => 'wholesaler',
            'مستورد' => 'importer',
            'مورد محلي' => 'local_supplier',
            'manufacturer' => 'manufacturer',
            'distributor' => 'distributor',
            'wholesaler' => 'wholesaler',
            'importer' => 'importer',
            'local_supplier' => 'local_supplier'
        ];

        return $typeMap[$type] ?? 'local_supplier';
    }

    /**
     * Normalize status
     */
    private function normalizeStatus($status)
    {
        $statusMap = [
            'نشط' => 'active',
            'غير نشط' => 'inactive',
            'معلق' => 'suspended',
            'محظور' => 'blocked',
            'active' => 'active',
            'inactive' => 'inactive',
            'suspended' => 'suspended',
            'blocked' => 'blocked'
        ];

        return $statusMap[$status] ?? 'active';
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
     * Parse rating value
     */
    private function parseRating($value)
    {
        if (empty($value) || !is_numeric($value)) {
            return null;
        }
        $rating = (float) $value;
        return ($rating >= 1 && $rating <= 5) ? $rating : null;
    }

    /**
     * Parse boolean value
     */
    private function parseBoolean($value)
    {
        $value = trim(strtolower($value));
        return in_array($value, ['نعم', 'yes', '1', 'true']);
    }
}
