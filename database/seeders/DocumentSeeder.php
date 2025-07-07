<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use App\Modules\Documents\Models\Document;
use App\Modules\Documents\Models\DocumentCategory;
use App\Modules\Documents\Models\DocumentFolder;
use App\Modules\Documents\Models\DocumentActivity;

class DocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create document categories
        $categories = [
            [
                'name' => 'Contracts',
                'name_ar' => 'العقود',
                'description' => 'Legal contracts and agreements',
                'description_ar' => 'العقود والاتفاقيات القانونية',
                'color' => '#3B82F6',
                'icon' => 'document-text',
                'sort_order' => 1,
                'created_by' => 1,
            ],
            [
                'name' => 'Invoices',
                'name_ar' => 'الفواتير',
                'description' => 'Sales and purchase invoices',
                'description_ar' => 'فواتير المبيعات والمشتريات',
                'color' => '#10B981',
                'icon' => 'receipt-tax',
                'sort_order' => 2,
                'created_by' => 1,
            ],
            [
                'name' => 'Reports',
                'name_ar' => 'التقارير',
                'description' => 'Business and financial reports',
                'description_ar' => 'التقارير التجارية والمالية',
                'color' => '#F59E0B',
                'icon' => 'chart-bar',
                'sort_order' => 3,
                'created_by' => 1,
            ],
            [
                'name' => 'Policies',
                'name_ar' => 'السياسات',
                'description' => 'Company policies and procedures',
                'description_ar' => 'سياسات وإجراءات الشركة',
                'color' => '#8B5CF6',
                'icon' => 'clipboard-list',
                'sort_order' => 4,
                'created_by' => 1,
            ],
            [
                'name' => 'Certificates',
                'name_ar' => 'الشهادات',
                'description' => 'Licenses and certifications',
                'description_ar' => 'التراخيص والشهادات',
                'color' => '#EF4444',
                'icon' => 'badge-check',
                'sort_order' => 5,
                'created_by' => 1,
            ],
        ];

        foreach ($categories as $categoryData) {
            DocumentCategory::create($categoryData);
        }

        // Create document folders
        $folders = [
            [
                'name' => 'Legal Documents',
                'name_ar' => 'الوثائق القانونية',
                'description' => 'All legal documents and contracts',
                'description_ar' => 'جميع الوثائق والعقود القانونية',
                'visibility' => DocumentFolder::VISIBILITY_INTERNAL,
                'color' => '#3B82F6',
                'icon' => 'folder',
                'sort_order' => 1,
                'created_by' => 1,
            ],
            [
                'name' => 'Financial Records',
                'name_ar' => 'السجلات المالية',
                'description' => 'Financial statements and records',
                'description_ar' => 'البيانات والسجلات المالية',
                'visibility' => DocumentFolder::VISIBILITY_RESTRICTED,
                'color' => '#10B981',
                'icon' => 'folder',
                'sort_order' => 2,
                'created_by' => 1,
            ],
            [
                'name' => 'HR Documents',
                'name_ar' => 'وثائق الموارد البشرية',
                'description' => 'Human resources documents',
                'description_ar' => 'وثائق الموارد البشرية',
                'visibility' => DocumentFolder::VISIBILITY_INTERNAL,
                'color' => '#F59E0B',
                'icon' => 'folder',
                'sort_order' => 3,
                'created_by' => 1,
            ],
            [
                'name' => 'Product Documentation',
                'name_ar' => 'وثائق المنتجات',
                'description' => 'Product specifications and documentation',
                'description_ar' => 'مواصفات ووثائق المنتجات',
                'visibility' => DocumentFolder::VISIBILITY_PUBLIC,
                'color' => '#8B5CF6',
                'icon' => 'folder',
                'sort_order' => 4,
                'created_by' => 1,
            ],
        ];

        foreach ($folders as $folderData) {
            DocumentFolder::create($folderData);
        }

        // Create sample documents
        $documents = [
            [
                'title' => 'Pharmaceutical Supply Agreement',
                'title_ar' => 'اتفاقية توريد الأدوية',
                'description' => 'Master supply agreement for pharmaceutical products',
                'description_ar' => 'اتفاقية التوريد الرئيسية للمنتجات الصيدلانية',
                'category_id' => 1, // Contracts
                'folder_id' => 1,   // Legal Documents
                'status' => Document::STATUS_APPROVED,
                'visibility' => Document::VISIBILITY_INTERNAL,
                'is_template' => true,
                'template_type' => Document::TEMPLATE_CONTRACT,
                'tags' => ['contract', 'pharmaceutical', 'supply'],
                'metadata' => [
                    'contract_value' => '5000000 IQD',
                    'duration' => '12 months',
                    'renewal_date' => '2025-12-31',
                ],
                'created_by' => 1,
                'approved_by' => 1,
                'approved_at' => now(),
            ],
            [
                'title' => 'Monthly Sales Report - June 2025',
                'title_ar' => 'تقرير المبيعات الشهري - يونيو 2025',
                'description' => 'Comprehensive sales report for June 2025',
                'description_ar' => 'تقرير مبيعات شامل لشهر يونيو 2025',
                'category_id' => 3, // Reports
                'folder_id' => 2,   // Financial Records
                'status' => Document::STATUS_APPROVED,
                'visibility' => Document::VISIBILITY_RESTRICTED,
                'tags' => ['sales', 'report', 'monthly', '2025'],
                'metadata' => [
                    'period' => 'June 2025',
                    'total_sales' => '15000000 IQD',
                    'growth_rate' => '12.5%',
                ],
                'created_by' => 1,
                'approved_by' => 1,
                'approved_at' => now(),
            ],
            [
                'title' => 'Employee Handbook 2025',
                'title_ar' => 'دليل الموظف 2025',
                'description' => 'Complete employee handbook with policies and procedures',
                'description_ar' => 'دليل الموظف الكامل مع السياسات والإجراءات',
                'category_id' => 4, // Policies
                'folder_id' => 3,   // HR Documents
                'status' => Document::STATUS_APPROVED,
                'visibility' => Document::VISIBILITY_INTERNAL,
                'is_template' => true,
                'template_type' => Document::TEMPLATE_FORM,
                'tags' => ['hr', 'handbook', 'policies', 'procedures'],
                'metadata' => [
                    'version' => '2025.1',
                    'effective_date' => '2025-01-01',
                    'review_date' => '2025-12-31',
                ],
                'created_by' => 1,
                'approved_by' => 1,
                'approved_at' => now(),
            ],
            [
                'title' => 'Product Catalog - Pharmaceutical Supplies',
                'title_ar' => 'كتالوج المنتجات - الإمدادات الصيدلانية',
                'description' => 'Complete catalog of pharmaceutical products and supplies',
                'description_ar' => 'كتالوج كامل للمنتجات والإمدادات الصيدلانية',
                'category_id' => 3, // Reports
                'folder_id' => 4,   // Product Documentation
                'status' => Document::STATUS_APPROVED,
                'visibility' => Document::VISIBILITY_PUBLIC,
                'tags' => ['catalog', 'products', 'pharmaceutical', 'supplies'],
                'metadata' => [
                    'product_count' => 250,
                    'categories' => 15,
                    'last_updated' => '2025-06-01',
                ],
                'created_by' => 1,
                'approved_by' => 1,
                'approved_at' => now(),
            ],
            [
                'title' => 'Quality Assurance Certificate',
                'title_ar' => 'شهادة ضمان الجودة',
                'description' => 'ISO 9001:2015 Quality Management System Certificate',
                'description_ar' => 'شهادة نظام إدارة الجودة ISO 9001:2015',
                'category_id' => 5, // Certificates
                'folder_id' => 1,   // Legal Documents
                'status' => Document::STATUS_APPROVED,
                'visibility' => Document::VISIBILITY_PUBLIC,
                'tags' => ['certificate', 'quality', 'iso', '9001'],
                'metadata' => [
                    'certificate_number' => 'QMS-2025-001',
                    'issued_date' => '2025-01-15',
                    'expiry_date' => '2028-01-15',
                    'issuing_authority' => 'Iraqi Standards Organization',
                ],
                'expires_at' => now()->addYears(3),
                'created_by' => 1,
                'approved_by' => 1,
                'approved_at' => now(),
            ],
        ];

        foreach ($documents as $docData) {
            // Create a dummy file for demonstration
            $fileName = \Str::slug($docData['title']) . '.pdf';
            $filePath = 'documents/' . now()->format('Y/m') . '/' . $fileName;
            
            // Create dummy file content
            $dummyContent = "This is a sample document: {$docData['title']}\n\nCreated for demonstration purposes.";
            Storage::disk('private')->put($filePath, $dummyContent);
            
            $document = Document::create(array_merge($docData, [
                'file_name' => $fileName,
                'original_name' => $fileName,
                'file_path' => $filePath,
                'file_size' => strlen($dummyContent),
                'mime_type' => 'application/pdf',
                'file_extension' => 'pdf',
                'checksum' => md5($dummyContent),
            ]));

            // Add some activities
            $document->logActivity(DocumentActivity::TYPE_CREATED, "Document created: {$document->title}");
            $document->logActivity(DocumentActivity::TYPE_UPLOADED, "Document file uploaded");
            
            if ($document->status === Document::STATUS_APPROVED) {
                $document->logActivity(DocumentActivity::TYPE_APPROVED, "Document approved for publication");
            }
        }

        $this->command->info('Document management sample data created successfully!');
        $this->command->info('Created:');
        $this->command->info('- ' . DocumentCategory::count() . ' document categories');
        $this->command->info('- ' . DocumentFolder::count() . ' document folders');
        $this->command->info('- ' . Document::count() . ' documents');
        $this->command->info('- ' . DocumentActivity::count() . ' document activities');
    }
}
