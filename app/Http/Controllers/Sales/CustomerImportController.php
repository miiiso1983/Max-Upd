<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Modules\Sales\Models\Customer;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CustomerImportController extends Controller
{
    /**
     * Show import form
     */
    public function showImportForm()
    {
        return view('sales.customers.import');
    }

    /**
     * Generate Excel template for customers import
     */
    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('العملاء');
        $sheet->setRightToLeft(true);
        
        // Headers
        $headers = [
            'A1' => 'اسم العميل*',
            'B1' => 'الاسم بالعربية',
            'C1' => 'نوع العميل*',
            'D1' => 'رمز العميل',
            'E1' => 'البريد الإلكتروني',
            'F1' => 'رقم الهاتف',
            'G1' => 'رقم الموبايل',
            'H1' => 'العنوان',
            'I1' => 'المدينة',
            'J1' => 'المحافظة',
            'K1' => 'الرمز البريدي',
            'L1' => 'الرقم الضريبي',
            'M1' => 'رقم الترخيص',
            'N1' => 'الشخص المسؤول',
            'O1' => 'هاتف المسؤول',
            'P1' => 'بريد المسؤول',
            'Q1' => 'الحد الائتماني',
            'R1' => 'شروط الدفع (أيام)',
            'S1' => 'نسبة الخصم (%)',
            'T1' => 'الحالة*',
            'U1' => 'ملاحظات'
        ];
        
        // Set headers
        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }
        
        // Style headers
        $this->styleHeaders($sheet, 'A1:U1');
        
        // Mark required fields with yellow background
        $requiredCells = ['A1', 'C1', 'T1'];
        foreach ($requiredCells as $cell) {
            $sheet->getStyle($cell)->getFill()
                  ->setFillType(Fill::FILL_SOLID)
                  ->setStartColor(new Color('FFFF00'));
        }
        
        // Add sample data
        $this->addSampleData($sheet);
        
        // Add data validation
        $this->addDataValidations($sheet);
        
        // Auto-size columns
        foreach (range('A', 'U') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Create writer and save
        $writer = new Xlsx($spreadsheet);
        
        $filename = 'قالب_استيراد_العملاء_' . date('Y-m-d') . '.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), 'customers_template');
        
        $writer->save($tempFile);
        
        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }

    /**
     * Import customers from Excel
     */
    public function import(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:10240', // 10MB max
        ]);

        try {
            $file = $request->file('excel_file');
            $spreadsheet = IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            // Remove header row
            array_shift($rows);

            $imported = 0;
            $errors = [];
            $duplicates = 0;

            DB::beginTransaction();

            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2; // +2 because we removed header and arrays are 0-indexed

                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }

                // Validate row data
                $validation = $this->validateRow($row, $rowNumber);
                if (!$validation['valid']) {
                    $errors = array_merge($errors, $validation['errors']);
                    continue;
                }

                // Check for duplicates
                $existingCustomer = Customer::where('email', $row[4])
                    ->orWhere('code', $row[3])
                    ->first();

                if ($existingCustomer) {
                    $duplicates++;
                    continue;
                }

                // Create customer
                try {
                    Customer::create([
                        'name' => $row[0],
                        'name_ar' => $row[1] ?: null,
                        'type' => $this->mapCustomerType($row[2]),
                        'code' => $row[3] ?: $this->generateCustomerCode(),
                        'email' => $row[4] ?: null,
                        'phone' => $row[5] ?: null,
                        'mobile' => $row[6] ?: null,
                        'address' => $row[7] ?: null,
                        'city' => $row[8] ?: null,
                        'governorate' => $row[9] ?: null,
                        'postal_code' => $row[10] ?: null,
                        'tax_number' => $row[11] ?: null,
                        'license_number' => $row[12] ?: null,
                        'contact_person' => $row[13] ?: null,
                        'contact_phone' => $row[14] ?: null,
                        'contact_email' => $row[15] ?: null,
                        'credit_limit' => is_numeric($row[16]) ? $row[16] : 0,
                        'payment_terms' => is_numeric($row[17]) ? $row[17] : 30,
                        'discount_percentage' => is_numeric($row[18]) ? $row[18] : 0,
                        'is_active' => $this->mapStatus($row[19]),
                        'notes' => $row[20] ?: null,
                        'created_by' => auth()->id(),
                    ]);

                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = "الصف {$rowNumber}: خطأ في إنشاء العميل - " . $e->getMessage();
                }
            }

            DB::commit();

            $message = "تم استيراد {$imported} عميل بنجاح.";
            if ($duplicates > 0) {
                $message .= " تم تجاهل {$duplicates} عميل مكرر.";
            }
            if (!empty($errors)) {
                $message .= " يوجد " . count($errors) . " خطأ.";
            }

            return redirect()->route('sales.customers.import')
                ->with('success', $message)
                ->with('errors', $errors)
                ->with('stats', [
                    'imported' => $imported,
                    'duplicates' => $duplicates,
                    'errors' => count($errors)
                ]);

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('sales.customers.import')
                ->with('error', 'خطأ في قراءة الملف: ' . $e->getMessage());
        }
    }

    /**
     * Style headers
     */
    private function styleHeaders($sheet, $range)
    {
        $sheet->getStyle($range)->applyFromArray([
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
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ]);
        
        $sheet->getRowDimension('1')->setRowHeight(25);
    }

    /**
     * Add sample data
     */
    private function addSampleData($sheet)
    {
        // Sample row 1
        $sheet->setCellValue('A2', 'صيدلية الشفاء');
        $sheet->setCellValue('B2', 'صيدلية الشفاء');
        $sheet->setCellValue('C2', 'صيدلية');
        $sheet->setCellValue('D2', 'CUST-001');
        $sheet->setCellValue('E2', 'info@alshifa-pharmacy.com');
        $sheet->setCellValue('F2', '07901234567');
        $sheet->setCellValue('G2', '07701234567');
        $sheet->setCellValue('H2', 'شارع الرشيد، بغداد');
        $sheet->setCellValue('I2', 'بغداد');
        $sheet->setCellValue('J2', 'بغداد');
        $sheet->setCellValue('K2', '10001');
        $sheet->setCellValue('L2', 'TAX123456');
        $sheet->setCellValue('M2', 'LIC789012');
        $sheet->setCellValue('N2', 'أحمد محمد');
        $sheet->setCellValue('O2', '07801234567');
        $sheet->setCellValue('P2', 'ahmed@alshifa-pharmacy.com');
        $sheet->setCellValue('Q2', '5000000');
        $sheet->setCellValue('R2', '30');
        $sheet->setCellValue('S2', '5.00');
        $sheet->setCellValue('T2', 'نشط');
        $sheet->setCellValue('U2', 'عميل مميز');

        // Sample row 2
        $sheet->setCellValue('A3', 'مستشفى بغداد التخصصي');
        $sheet->setCellValue('B3', 'مستشفى بغداد التخصصي');
        $sheet->setCellValue('C3', 'مستشفى');
        $sheet->setCellValue('D3', 'CUST-002');
        $sheet->setCellValue('E3', 'info@baghdad-hospital.com');
        $sheet->setCellValue('F3', '07902345678');
        $sheet->setCellValue('G3', '07702345678');
        $sheet->setCellValue('H3', 'منطقة الكرادة، بغداد');
        $sheet->setCellValue('I3', 'بغداد');
        $sheet->setCellValue('J3', 'بغداد');
        $sheet->setCellValue('K3', '10002');
        $sheet->setCellValue('L3', 'TAX234567');
        $sheet->setCellValue('M3', 'LIC890123');
        $sheet->setCellValue('N3', 'فاطمة علي');
        $sheet->setCellValue('O3', '07802345678');
        $sheet->setCellValue('P3', 'fatima@baghdad-hospital.com');
        $sheet->setCellValue('Q3', '10000000');
        $sheet->setCellValue('R3', '45');
        $sheet->setCellValue('S3', '3.00');
        $sheet->setCellValue('T3', 'نشط');
        $sheet->setCellValue('U3', 'مستشفى كبير');
    }

    /**
     * Add data validations
     */
    private function addDataValidations($sheet)
    {
        // Customer type validation
        $typeValidation = $sheet->getCell('C2')->getDataValidation();
        $typeValidation->setType(DataValidation::TYPE_LIST);
        $typeValidation->setErrorStyle(DataValidation::STYLE_INFORMATION);
        $typeValidation->setAllowBlank(false);
        $typeValidation->setShowInputMessage(true);
        $typeValidation->setShowErrorMessage(true);
        $typeValidation->setShowDropDown(true);
        $typeValidation->setErrorTitle('خطأ في النوع');
        $typeValidation->setError('يرجى اختيار نوع صحيح من القائمة');
        $typeValidation->setPromptTitle('نوع العميل');
        $typeValidation->setPrompt('اختر نوع العميل من القائمة');
        $typeValidation->setFormula1('"فرد,صيدلية,عيادة,مستشفى,موزع,حكومي"');

        $sheet->setDataValidation('C3:C1000', clone $typeValidation);

        // Status validation
        $statusValidation = $sheet->getCell('T2')->getDataValidation();
        $statusValidation->setType(DataValidation::TYPE_LIST);
        $statusValidation->setErrorStyle(DataValidation::STYLE_INFORMATION);
        $statusValidation->setAllowBlank(false);
        $statusValidation->setShowInputMessage(true);
        $statusValidation->setShowErrorMessage(true);
        $statusValidation->setShowDropDown(true);
        $statusValidation->setErrorTitle('خطأ في الحالة');
        $statusValidation->setError('يرجى اختيار حالة صحيحة من القائمة');
        $statusValidation->setPromptTitle('حالة العميل');
        $statusValidation->setPrompt('اختر حالة العميل من القائمة');
        $statusValidation->setFormula1('"نشط,غير نشط"');

        $sheet->setDataValidation('T3:T1000', clone $statusValidation);

        // Governorate validation
        $govValidation = $sheet->getCell('J2')->getDataValidation();
        $govValidation->setType(DataValidation::TYPE_LIST);
        $govValidation->setErrorStyle(DataValidation::STYLE_INFORMATION);
        $govValidation->setAllowBlank(true);
        $govValidation->setShowInputMessage(true);
        $govValidation->setShowErrorMessage(true);
        $govValidation->setShowDropDown(true);
        $govValidation->setErrorTitle('خطأ في المحافظة');
        $govValidation->setError('يرجى اختيار محافظة صحيحة من القائمة');
        $govValidation->setPromptTitle('المحافظة');
        $govValidation->setPrompt('اختر المحافظة من القائمة');
        $govValidation->setFormula1('"بغداد,البصرة,نينوى,أربيل,النجف,كربلاء,بابل,الأنبار,ديالى,ذي قار,المثنى,القادسية,ميسان,واسط,كركوك,صلاح الدين,دهوك,السليمانية"');

        $sheet->setDataValidation('J3:J1000', clone $govValidation);
    }

    /**
     * Validate row data
     */
    private function validateRow($row, $rowNumber)
    {
        $errors = [];

        // Required fields validation
        if (empty($row[0])) {
            $errors[] = "الصف {$rowNumber}: اسم العميل مطلوب";
        }

        if (empty($row[2])) {
            $errors[] = "الصف {$rowNumber}: نوع العميل مطلوب";
        } else {
            $validTypes = ['فرد', 'صيدلية', 'عيادة', 'مستشفى', 'موزع', 'حكومي'];
            if (!in_array($row[2], $validTypes)) {
                $errors[] = "الصف {$rowNumber}: نوع العميل غير صحيح";
            }
        }

        if (empty($row[19])) {
            $errors[] = "الصف {$rowNumber}: حالة العميل مطلوبة";
        } else {
            $validStatuses = ['نشط', 'غير نشط'];
            if (!in_array($row[19], $validStatuses)) {
                $errors[] = "الصف {$rowNumber}: حالة العميل غير صحيحة";
            }
        }

        // Email validation
        if (!empty($row[4]) && !filter_var($row[4], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "الصف {$rowNumber}: البريد الإلكتروني غير صحيح";
        }

        // Numeric fields validation
        if (!empty($row[16]) && !is_numeric($row[16])) {
            $errors[] = "الصف {$rowNumber}: الحد الائتماني يجب أن يكون رقم";
        }

        if (!empty($row[17]) && !is_numeric($row[17])) {
            $errors[] = "الصف {$rowNumber}: شروط الدفع يجب أن تكون رقم";
        }

        if (!empty($row[18]) && !is_numeric($row[18])) {
            $errors[] = "الصف {$rowNumber}: نسبة الخصم يجب أن تكون رقم";
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Map customer type from Arabic to English
     */
    private function mapCustomerType($arabicType)
    {
        $mapping = [
            'فرد' => 'individual',
            'صيدلية' => 'pharmacy',
            'عيادة' => 'clinic',
            'مستشفى' => 'hospital',
            'موزع' => 'distributor',
            'حكومي' => 'government'
        ];

        return $mapping[$arabicType] ?? 'individual';
    }

    /**
     * Map status from Arabic to boolean
     */
    private function mapStatus($arabicStatus)
    {
        return $arabicStatus === 'نشط';
    }

    /**
     * Generate unique customer code
     */
    private function generateCustomerCode()
    {
        $lastCustomer = Customer::orderBy('id', 'desc')->first();
        $nextId = $lastCustomer ? $lastCustomer->id + 1 : 1;
        return 'CUST-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
    }
}
