<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ExcelTemplateController extends Controller
{
    /**
     * Generate Excel template for data import
     */
    public function generateImportTemplate()
    {
        $spreadsheet = new Spreadsheet();
        
        // Remove default worksheet
        $spreadsheet->removeSheetByIndex(0);
        
        // Create worksheets
        $this->createCustomersSheet($spreadsheet);
        $this->createProductsSheet($spreadsheet);
        $this->createUsersSheet($spreadsheet);
        $this->createSuppliersSheet($spreadsheet);
        
        // Set active sheet to first one
        $spreadsheet->setActiveSheetIndex(0);
        
        // Create writer and save
        $writer = new Xlsx($spreadsheet);
        
        $filename = 'قالب_استيراد_بيانات_ERP_' . date('Y-m-d') . '.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), 'excel_template');
        
        $writer->save($tempFile);
        
        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }
    
    /**
     * Create Customers sheet
     */
    private function createCustomersSheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
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
        $sheet->setCellValue('A2', 'صيدلية الشفاء');
        $sheet->setCellValue('B2', 'صيدلية الشفاء');
        $sheet->setCellValue('C2', 'pharmacy');
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
        
        $sheet->setCellValue('A3', 'مستشفى بغداد التخصصي');
        $sheet->setCellValue('B3', 'مستشفى بغداد التخصصي');
        $sheet->setCellValue('C3', 'hospital');
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
        
        // Add data validation for dropdowns
        $this->addCustomerValidations($sheet);
        
        // Auto-size columns
        $this->autoSizeColumns($sheet, 'A:U');
    }
    
    /**
     * Create Products sheet
     */
    private function createProductsSheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('المنتجات');
        $sheet->setRightToLeft(true);
        
        // Headers
        $headers = [
            'A1' => 'اسم المنتج*',
            'B1' => 'الاسم بالعربية',
            'C1' => 'الوصف',
            'D1' => 'الوصف بالعربية',
            'E1' => 'رمز المنتج (SKU)*',
            'F1' => 'الباركود',
            'G1' => 'الفئة',
            'H1' => 'الشركة المصنعة',
            'I1' => 'وحدة القياس*',
            'J1' => 'سعر الشراء*',
            'K1' => 'سعر البيع*',
            'L1' => 'الحد الأدنى للمخزون',
            'M1' => 'الحد الأقصى للمخزون',
            'N1' => 'نقطة إعادة الطلب',
            'O1' => 'الحالة*',
            'P1' => 'يتطلب وصفة طبية',
            'Q1' => 'مادة خاضعة للرقابة',
            'R1' => 'تتبع تاريخ الانتهاء',
            'S1' => 'تتبع الدفعات',
            'T1' => 'ملاحظات'
        ];
        
        // Set headers
        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }
        
        // Style headers
        $this->styleHeaders($sheet, 'A1:T1');
        
        // Mark required fields
        $requiredCells = ['A1', 'E1', 'I1', 'J1', 'K1', 'O1'];
        foreach ($requiredCells as $cell) {
            $sheet->getStyle($cell)->getFill()
                  ->setFillType(Fill::FILL_SOLID)
                  ->setStartColor(new Color('FFFF00'));
        }
        
        // Add sample data
        $sheet->setCellValue('A2', 'Paracetamol 500mg');
        $sheet->setCellValue('B2', 'باراسيتامول 500 ملغ');
        $sheet->setCellValue('C2', 'Pain reliever and fever reducer');
        $sheet->setCellValue('D2', 'مسكن للألم وخافض للحرارة');
        $sheet->setCellValue('E2', 'PARA-500-001');
        $sheet->setCellValue('F2', '1234567890123');
        $sheet->setCellValue('G2', 'أدوية الألم');
        $sheet->setCellValue('H2', 'شركة الأدوية العراقية');
        $sheet->setCellValue('I2', 'قرص');
        $sheet->setCellValue('J2', '250');
        $sheet->setCellValue('K2', '500');
        $sheet->setCellValue('L2', '100');
        $sheet->setCellValue('M2', '1000');
        $sheet->setCellValue('N2', '200');
        $sheet->setCellValue('O2', 'نشط');
        $sheet->setCellValue('P2', 'لا');
        $sheet->setCellValue('Q2', 'لا');
        $sheet->setCellValue('R2', 'نعم');
        $sheet->setCellValue('S2', 'نعم');
        $sheet->setCellValue('T2', 'دواء شائع الاستخدام');
        
        $sheet->setCellValue('A3', 'Amoxicillin 250mg');
        $sheet->setCellValue('B3', 'أموكسيسيلين 250 ملغ');
        $sheet->setCellValue('C3', 'Antibiotic medication');
        $sheet->setCellValue('D3', 'مضاد حيوي');
        $sheet->setCellValue('E3', 'AMOX-250-001');
        $sheet->setCellValue('F3', '2345678901234');
        $sheet->setCellValue('G3', 'المضادات الحيوية');
        $sheet->setCellValue('H3', 'شركة الأدوية العالمية');
        $sheet->setCellValue('I3', 'كبسولة');
        $sheet->setCellValue('J3', '500');
        $sheet->setCellValue('K3', '1000');
        $sheet->setCellValue('L3', '50');
        $sheet->setCellValue('M3', '500');
        $sheet->setCellValue('N3', '100');
        $sheet->setCellValue('O3', 'نشط');
        $sheet->setCellValue('P3', 'نعم');
        $sheet->setCellValue('Q3', 'لا');
        $sheet->setCellValue('R3', 'نعم');
        $sheet->setCellValue('S3', 'نعم');
        $sheet->setCellValue('T3', 'يتطلب وصفة طبية');
        
        // Add data validation
        $this->addProductValidations($sheet);
        
        // Auto-size columns
        $this->autoSizeColumns($sheet, 'A:T');
    }
    
    /**
     * Create Users sheet
     */
    private function createUsersSheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('المستخدمين');
        $sheet->setRightToLeft(true);

        // Headers
        $headers = [
            'A1' => 'الاسم الكامل*',
            'B1' => 'البريد الإلكتروني*',
            'C1' => 'كلمة المرور*',
            'D1' => 'رقم الهاتف',
            'E1' => 'رقم الموظف',
            'F1' => 'القسم',
            'G1' => 'المنصب',
            'H1' => 'الحالة*',
            'I1' => 'الدور*',
            'J1' => 'تفعيل المصادقة الثنائية',
            'K1' => 'هاتف المصادقة الثنائية',
            'L1' => 'إعدادات الإشعارات'
        ];

        // Set headers and style
        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }
        $this->styleHeaders($sheet, 'A1:L1');

        // Mark required fields
        $requiredCells = ['A1', 'B1', 'C1', 'H1', 'I1'];
        foreach ($requiredCells as $cell) {
            $sheet->getStyle($cell)->getFill()
                  ->setFillType(Fill::FILL_SOLID)
                  ->setStartColor(new Color('FFFF00'));
        }

        // Sample data
        $sheet->setCellValue('A2', 'أحمد محمد علي');
        $sheet->setCellValue('B2', 'ahmed.mohammed@pharmacy.com');
        $sheet->setCellValue('C2', 'password123');
        $sheet->setCellValue('D2', '07901234567');
        $sheet->setCellValue('E2', 'EMP-001');
        $sheet->setCellValue('F2', 'الصيدلة');
        $sheet->setCellValue('G2', 'صيدلاني أول');
        $sheet->setCellValue('H2', 'نشط');
        $sheet->setCellValue('I2', 'pharmacist');
        $sheet->setCellValue('J2', 'لا');
        $sheet->setCellValue('K2', '07901234567');
        $sheet->setCellValue('L2', 'email,sms');

        $sheet->setCellValue('A3', 'فاطمة حسن');
        $sheet->setCellValue('B3', 'fatima.hassan@pharmacy.com');
        $sheet->setCellValue('C3', 'password456');
        $sheet->setCellValue('D3', '07902345678');
        $sheet->setCellValue('E3', 'EMP-002');
        $sheet->setCellValue('F3', 'المبيعات');
        $sheet->setCellValue('G3', 'مسؤولة مبيعات');
        $sheet->setCellValue('H3', 'نشط');
        $sheet->setCellValue('I3', 'sales');
        $sheet->setCellValue('J3', 'نعم');
        $sheet->setCellValue('K3', '07902345678');
        $sheet->setCellValue('L3', 'email');

        $this->addUserValidations($sheet);

        $this->autoSizeColumns($sheet, 'A:L');
    }

    /**
     * Create Suppliers sheet
     */
    private function createSuppliersSheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('الشركات');
        $sheet->setRightToLeft(true);

        // Headers
        $headers = [
            'A1' => 'اسم الشركة*',
            'B1' => 'الاسم بالعربية',
            'C1' => 'نوع الشركة*',
            'D1' => 'رمز الشركة',
            'E1' => 'الحالة*',
            'F1' => 'البريد الإلكتروني',
            'G1' => 'رقم الهاتف',
            'H1' => 'رقم الموبايل',
            'I1' => 'الفاكس',
            'J1' => 'الموقع الإلكتروني',
            'K1' => 'العنوان',
            'L1' => 'المدينة',
            'M1' => 'المحافظة',
            'N1' => 'البلد',
            'O1' => 'الرمز البريدي',
            'P1' => 'الرقم الضريبي',
            'Q1' => 'رقم الترخيص',
            'R1' => 'الشخص المسؤول',
            'S1' => 'هاتف المسؤول',
            'T1' => 'بريد المسؤول',
            'U1' => 'شروط الدفع (أيام)',
            'V1' => 'الحد الائتماني',
            'W1' => 'العملة',
            'X1' => 'اسم البنك',
            'Y1' => 'رقم الحساب',
            'Z1' => 'IBAN',
            'AA1' => 'SWIFT Code',
            'AB1' => 'التقييم (1-5)',
            'AC1' => 'مورد مفضل',
            'AD1' => 'ملاحظات'
        ];

        // Set headers and style
        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }
        $this->styleHeaders($sheet, 'A1:AD1');

        // Mark required fields
        $requiredCells = ['A1', 'C1', 'E1'];
        foreach ($requiredCells as $cell) {
            $sheet->getStyle($cell)->getFill()
                  ->setFillType(Fill::FILL_SOLID)
                  ->setStartColor(new Color('FFFF00'));
        }

        // Sample data
        $sheet->setCellValue('A2', 'شركة الأدوية العراقية');
        $sheet->setCellValue('B2', 'شركة الأدوية العراقية');
        $sheet->setCellValue('C2', 'manufacturer');
        $sheet->setCellValue('D2', 'SUP-001');
        $sheet->setCellValue('E2', 'active');
        $sheet->setCellValue('F2', 'info@iraqi-pharma.com');
        $sheet->setCellValue('G2', '07901234567');
        $sheet->setCellValue('H2', '07701234567');
        $sheet->setCellValue('I2', '07801234567');
        $sheet->setCellValue('J2', 'www.iraqi-pharma.com');
        $sheet->setCellValue('K2', 'المنطقة الصناعية، بغداد');
        $sheet->setCellValue('L2', 'بغداد');
        $sheet->setCellValue('M2', 'بغداد');
        $sheet->setCellValue('N2', 'العراق');
        $sheet->setCellValue('O2', '10001');
        $sheet->setCellValue('P2', 'TAX123456');
        $sheet->setCellValue('Q2', 'LIC789012');
        $sheet->setCellValue('R2', 'محمد أحمد');
        $sheet->setCellValue('S2', '07901234567');
        $sheet->setCellValue('T2', 'mohammed@iraqi-pharma.com');
        $sheet->setCellValue('U2', '30');
        $sheet->setCellValue('V2', '50000000');
        $sheet->setCellValue('W2', 'IQD');
        $sheet->setCellValue('X2', 'بنك بغداد');
        $sheet->setCellValue('Y2', '123456789');
        $sheet->setCellValue('Z2', 'IQ12BBAG1234567890123456');
        $sheet->setCellValue('AA2', 'BBAGIQBA');
        $sheet->setCellValue('AB2', '4.5');
        $sheet->setCellValue('AC2', 'نعم');
        $sheet->setCellValue('AD2', 'مورد موثوق');

        // Add second sample row for suppliers
        $sheet->setCellValue('A3', 'شركة الدواء الدولية');
        $sheet->setCellValue('B3', 'شركة الدواء الدولية');
        $sheet->setCellValue('C3', 'distributor');
        $sheet->setCellValue('D3', 'SUP-002');
        $sheet->setCellValue('E3', 'active');
        $sheet->setCellValue('F3', 'info@international-pharma.com');
        $sheet->setCellValue('G3', '07903456789');
        $sheet->setCellValue('H3', '07703456789');
        $sheet->setCellValue('I3', '07803456789');
        $sheet->setCellValue('J3', 'www.international-pharma.com');
        $sheet->setCellValue('K3', 'شارع الجامعة، بغداد');
        $sheet->setCellValue('L3', 'بغداد');
        $sheet->setCellValue('M3', 'بغداد');
        $sheet->setCellValue('N3', 'العراق');
        $sheet->setCellValue('O3', '10002');
        $sheet->setCellValue('P3', 'TAX234567');
        $sheet->setCellValue('Q3', 'LIC890123');
        $sheet->setCellValue('R3', 'سارة أحمد');
        $sheet->setCellValue('S3', '07903456789');
        $sheet->setCellValue('T3', 'sara@international-pharma.com');
        $sheet->setCellValue('U3', '45');
        $sheet->setCellValue('V3', '25000000');
        $sheet->setCellValue('W3', 'USD');
        $sheet->setCellValue('X3', 'البنك التجاري العراقي');
        $sheet->setCellValue('Y3', '987654321');
        $sheet->setCellValue('Z3', 'IQ34TCBI9876543210987654');
        $sheet->setCellValue('AA3', 'TCBIIQBA');
        $sheet->setCellValue('AB3', '4.0');
        $sheet->setCellValue('AC3', 'لا');
        $sheet->setCellValue('AD3', 'موزع دولي');

        $this->addSupplierValidations($sheet);

        // Auto-size columns A-Z and AA-AD
        $this->autoSizeColumns($sheet, 'A:Z');
        $this->autoSizeColumns($sheet, ['AA', 'AB', 'AC', 'AD']);
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

        // Set row height
        $sheet->getRowDimension('1')->setRowHeight(25);
    }

    /**
     * Add data validations for customers sheet
     */
    private function addCustomerValidations($sheet)
    {
        // Customer type validation
        $validation = $sheet->getCell('C2')->getDataValidation();
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
        $validation->setAllowBlank(false);
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setShowDropDown(true);
        $validation->setErrorTitle('خطأ في النوع');
        $validation->setError('يرجى اختيار نوع صحيح من القائمة');
        $validation->setPromptTitle('نوع العميل');
        $validation->setPrompt('اختر نوع العميل من القائمة');
        $validation->setFormula1('"individual,pharmacy,clinic,hospital,distributor,government"');

        // Copy validation to other rows
        $sheet->setDataValidation('C3:C1000', clone $validation);

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
     * Add data validations for products sheet
     */
    private function addProductValidations($sheet)
    {
        // Unit of measure validation
        $unitValidation = $sheet->getCell('I2')->getDataValidation();
        $unitValidation->setType(DataValidation::TYPE_LIST);
        $unitValidation->setErrorStyle(DataValidation::STYLE_INFORMATION);
        $unitValidation->setAllowBlank(false);
        $unitValidation->setShowInputMessage(true);
        $unitValidation->setShowErrorMessage(true);
        $unitValidation->setShowDropDown(true);
        $unitValidation->setErrorTitle('خطأ في وحدة القياس');
        $unitValidation->setError('يرجى اختيار وحدة قياس صحيحة من القائمة');
        $unitValidation->setPromptTitle('وحدة القياس');
        $unitValidation->setPrompt('اختر وحدة القياس من القائمة');
        $unitValidation->setFormula1('"قرص,كبسولة,شراب,حقنة,مرهم,كريم,قطرة,بخاخ,لصقة,علبة,زجاجة,أنبوب,عبوة,كيس,حبة,ملعقة,جرام,كيلوجرام,مليلتر,لتر"');

        $sheet->setDataValidation('I3:I1000', clone $unitValidation);

        // Status validation
        $statusValidation = $sheet->getCell('O2')->getDataValidation();
        $statusValidation->setType(DataValidation::TYPE_LIST);
        $statusValidation->setErrorStyle(DataValidation::STYLE_INFORMATION);
        $statusValidation->setAllowBlank(false);
        $statusValidation->setShowInputMessage(true);
        $statusValidation->setShowErrorMessage(true);
        $statusValidation->setShowDropDown(true);
        $statusValidation->setErrorTitle('خطأ في الحالة');
        $statusValidation->setError('يرجى اختيار حالة صحيحة من القائمة');
        $statusValidation->setPromptTitle('حالة المنتج');
        $statusValidation->setPrompt('اختر حالة المنتج من القائمة');
        $statusValidation->setFormula1('"نشط,غير نشط"');

        $sheet->setDataValidation('O3:O1000', clone $statusValidation);

        // Yes/No validations
        $yesNoValidation = $sheet->getCell('P2')->getDataValidation();
        $yesNoValidation->setType(DataValidation::TYPE_LIST);
        $yesNoValidation->setErrorStyle(DataValidation::STYLE_INFORMATION);
        $yesNoValidation->setAllowBlank(false);
        $yesNoValidation->setShowInputMessage(true);
        $yesNoValidation->setShowErrorMessage(true);
        $yesNoValidation->setShowDropDown(true);
        $yesNoValidation->setErrorTitle('خطأ في الاختيار');
        $yesNoValidation->setError('يرجى اختيار نعم أو لا');
        $yesNoValidation->setPromptTitle('اختيار');
        $yesNoValidation->setPrompt('اختر نعم أو لا');
        $yesNoValidation->setFormula1('"نعم,لا"');

        // Apply to all yes/no columns
        $sheet->setDataValidation('P3:P1000', clone $yesNoValidation);
        $sheet->setDataValidation('Q2:Q1000', clone $yesNoValidation);
        $sheet->setDataValidation('R2:R1000', clone $yesNoValidation);
        $sheet->setDataValidation('S2:S1000', clone $yesNoValidation);
    }

    /**
     * Add data validations for users sheet
     */
    private function addUserValidations($sheet)
    {
        // Status validation
        $statusValidation = $sheet->getCell('H2')->getDataValidation();
        $statusValidation->setType(DataValidation::TYPE_LIST);
        $statusValidation->setErrorStyle(DataValidation::STYLE_INFORMATION);
        $statusValidation->setAllowBlank(false);
        $statusValidation->setShowInputMessage(true);
        $statusValidation->setShowErrorMessage(true);
        $statusValidation->setShowDropDown(true);
        $statusValidation->setErrorTitle('خطأ في الحالة');
        $statusValidation->setError('يرجى اختيار حالة صحيحة من القائمة');
        $statusValidation->setPromptTitle('حالة المستخدم');
        $statusValidation->setPrompt('اختر حالة المستخدم من القائمة');
        $statusValidation->setFormula1('"نشط,غير نشط"');

        $sheet->setDataValidation('H3:H1000', clone $statusValidation);

        // Role validation
        $roleValidation = $sheet->getCell('I2')->getDataValidation();
        $roleValidation->setType(DataValidation::TYPE_LIST);
        $roleValidation->setErrorStyle(DataValidation::STYLE_INFORMATION);
        $roleValidation->setAllowBlank(false);
        $roleValidation->setShowInputMessage(true);
        $roleValidation->setShowErrorMessage(true);
        $roleValidation->setShowDropDown(true);
        $roleValidation->setErrorTitle('خطأ في الدور');
        $roleValidation->setError('يرجى اختيار دور صحيح من القائمة');
        $roleValidation->setPromptTitle('دور المستخدم');
        $roleValidation->setPrompt('اختر دور المستخدم من القائمة');
        $roleValidation->setFormula1('"admin,manager,pharmacist,sales,cashier,inventory,accountant"');

        $sheet->setDataValidation('I3:I1000', clone $roleValidation);

        // Yes/No validation for 2FA
        $yesNoValidation = $sheet->getCell('J2')->getDataValidation();
        $yesNoValidation->setType(DataValidation::TYPE_LIST);
        $yesNoValidation->setErrorStyle(DataValidation::STYLE_INFORMATION);
        $yesNoValidation->setAllowBlank(false);
        $yesNoValidation->setShowInputMessage(true);
        $yesNoValidation->setShowErrorMessage(true);
        $yesNoValidation->setShowDropDown(true);
        $yesNoValidation->setErrorTitle('خطأ في الاختيار');
        $yesNoValidation->setError('يرجى اختيار نعم أو لا');
        $yesNoValidation->setPromptTitle('تفعيل المصادقة الثنائية');
        $yesNoValidation->setPrompt('اختر نعم أو لا');
        $yesNoValidation->setFormula1('"نعم,لا"');

        $sheet->setDataValidation('J3:J1000', clone $yesNoValidation);
    }

    /**
     * Add data validations for suppliers sheet
     */
    private function addSupplierValidations($sheet)
    {
        // Supplier type validation
        $typeValidation = $sheet->getCell('C2')->getDataValidation();
        $typeValidation->setType(DataValidation::TYPE_LIST);
        $typeValidation->setErrorStyle(DataValidation::STYLE_INFORMATION);
        $typeValidation->setAllowBlank(false);
        $typeValidation->setShowInputMessage(true);
        $typeValidation->setShowErrorMessage(true);
        $typeValidation->setShowDropDown(true);
        $typeValidation->setErrorTitle('خطأ في النوع');
        $typeValidation->setError('يرجى اختيار نوع صحيح من القائمة');
        $typeValidation->setPromptTitle('نوع الشركة');
        $typeValidation->setPrompt('اختر نوع الشركة من القائمة');
        $typeValidation->setFormula1('"manufacturer,distributor,wholesaler,importer,local"');

        $sheet->setDataValidation('C3:C1000', clone $typeValidation);

        // Status validation
        $statusValidation = $sheet->getCell('E2')->getDataValidation();
        $statusValidation->setType(DataValidation::TYPE_LIST);
        $statusValidation->setErrorStyle(DataValidation::STYLE_INFORMATION);
        $statusValidation->setAllowBlank(false);
        $statusValidation->setShowInputMessage(true);
        $statusValidation->setShowErrorMessage(true);
        $statusValidation->setShowDropDown(true);
        $statusValidation->setErrorTitle('خطأ في الحالة');
        $statusValidation->setError('يرجى اختيار حالة صحيحة من القائمة');
        $statusValidation->setPromptTitle('حالة الشركة');
        $statusValidation->setPrompt('اختر حالة الشركة من القائمة');
        $statusValidation->setFormula1('"active,inactive,suspended,blacklisted"');

        $sheet->setDataValidation('E3:E1000', clone $statusValidation);

        // Currency validation
        $currencyValidation = $sheet->getCell('W2')->getDataValidation();
        $currencyValidation->setType(DataValidation::TYPE_LIST);
        $currencyValidation->setErrorStyle(DataValidation::STYLE_INFORMATION);
        $currencyValidation->setAllowBlank(false);
        $currencyValidation->setShowInputMessage(true);
        $currencyValidation->setShowErrorMessage(true);
        $currencyValidation->setShowDropDown(true);
        $currencyValidation->setErrorTitle('خطأ في العملة');
        $currencyValidation->setError('يرجى اختيار عملة صحيحة من القائمة');
        $currencyValidation->setPromptTitle('العملة');
        $currencyValidation->setPrompt('اختر العملة من القائمة');
        $currencyValidation->setFormula1('"IQD,USD,EUR,GBP,SAR,AED,JOD,KWD,TRY"');

        $sheet->setDataValidation('W3:W1000', clone $currencyValidation);

        // Yes/No validation for preferred supplier
        $yesNoValidation = $sheet->getCell('AC2')->getDataValidation();
        $yesNoValidation->setType(DataValidation::TYPE_LIST);
        $yesNoValidation->setErrorStyle(DataValidation::STYLE_INFORMATION);
        $yesNoValidation->setAllowBlank(false);
        $yesNoValidation->setShowInputMessage(true);
        $yesNoValidation->setShowErrorMessage(true);
        $yesNoValidation->setShowDropDown(true);
        $yesNoValidation->setErrorTitle('خطأ في الاختيار');
        $yesNoValidation->setError('يرجى اختيار نعم أو لا');
        $yesNoValidation->setPromptTitle('مورد مفضل');
        $yesNoValidation->setPrompt('اختر نعم أو لا');
        $yesNoValidation->setFormula1('"نعم,لا"');

        $sheet->setDataValidation('AC3:AC1000', clone $yesNoValidation);

        // Governorate validation
        $govValidation = $sheet->getCell('M2')->getDataValidation();
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

        $sheet->setDataValidation('M3:M1000', clone $govValidation);
    }

    /**
     * Auto-size columns for a sheet
     */
    private function autoSizeColumns($sheet, $columns)
    {
        if (is_array($columns)) {
            foreach ($columns as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
        } else {
            // Handle single range like 'A:Z'
            $parts = explode(':', $columns);
            if (count($parts) == 2) {
                foreach (range($parts[0], $parts[1]) as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            }
        }
    }
}
