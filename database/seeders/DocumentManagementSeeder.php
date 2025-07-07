<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\DocumentManagement\Models\Document;
use App\Modules\DocumentManagement\Models\DocumentSignature;
use App\Modules\DocumentManagement\Models\DocumentApproval;
use App\Modules\DocumentManagement\Models\DocumentAccessLog;
use Illuminate\Support\Facades\Storage;

class DocumentManagementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->createSampleDocuments();
        $this->createDocumentSignatures();
        $this->createDocumentApprovals();
        $this->createAccessLogs();

        $this->command->info('Document Management sample data created successfully!');
        $this->command->info('Created:');
        $this->command->info('- ' . Document::count() . ' documents');
        $this->command->info('- ' . DocumentSignature::count() . ' signatures');
        $this->command->info('- ' . DocumentApproval::count() . ' approvals');
        $this->command->info('- ' . DocumentAccessLog::count() . ' access logs');
    }

    private function createSampleDocuments()
    {
        // Ensure documents directory exists
        Storage::disk('private')->makeDirectory('documents');

        $documents = [
            [
                'title' => 'Standard Operating Procedure - Quality Control',
                'title_ar' => 'إجراء التشغيل المعياري - مراقبة الجودة',
                'description' => 'Comprehensive quality control procedures for pharmaceutical manufacturing',
                'description_ar' => 'إجراءات شاملة لمراقبة الجودة في التصنيع الدوائي',
                'document_number' => 'SOP-QC-001-2025',
                'file_name' => 'sop_quality_control_v1.pdf',
                'original_name' => 'Quality Control SOP.pdf',
                'file_path' => 'documents/sop_quality_control_v1.pdf',
                'file_size' => 2048576, // 2MB
                'mime_type' => 'application/pdf',
                'file_extension' => 'pdf',
                'status' => Document::STATUS_APPROVED,
                'visibility' => Document::VISIBILITY_INTERNAL,
                'is_template' => false,
                'version' => '1.0',
                'tags' => ['SOP', 'Quality Control', 'Manufacturing', 'Pharmaceutical'],
                'metadata' => [
                    'department' => 'Quality Assurance',
                    'effective_date' => '2025-01-01',
                    'review_cycle' => 'Annual',
                    'document_type' => 'Standard Operating Procedure',
                ],
                'checksum' => hash('md5', 'sample_sop_content'),
                'download_count' => 15,
                'expires_at' => now()->addYear(),
                'created_by' => 1,
                'approved_by' => 1,
                'approved_at' => now()->subDays(5),
            ],
            [
                'title' => 'Batch Manufacturing Record Template',
                'title_ar' => 'قالب سجل تصنيع الدفعة',
                'description' => 'Template for recording batch manufacturing processes and quality checks',
                'description_ar' => 'قالب لتسجيل عمليات تصنيع الدفعة وفحوصات الجودة',
                'document_number' => 'BMR-TEMP-001-2025',
                'file_name' => 'batch_manufacturing_record_template.xlsx',
                'original_name' => 'BMR Template.xlsx',
                'file_path' => 'documents/batch_manufacturing_record_template.xlsx',
                'file_size' => 512000, // 500KB
                'mime_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'file_extension' => 'xlsx',
                'status' => Document::STATUS_APPROVED,
                'visibility' => Document::VISIBILITY_INTERNAL,
                'is_template' => true,
                'template_type' => 'Batch Record',
                'version' => '2.1',
                'tags' => ['Template', 'Batch Record', 'Manufacturing', 'Quality'],
                'metadata' => [
                    'department' => 'Production',
                    'template_category' => 'Manufacturing',
                    'last_updated' => '2025-01-15',
                ],
                'checksum' => hash('md5', 'sample_bmr_template'),
                'download_count' => 42,
                'expires_at' => now()->addMonths(6),
                'created_by' => 1,
                'approved_by' => 1,
                'approved_at' => now()->subDays(10),
            ],
            [
                'title' => 'Regulatory Compliance Certificate',
                'title_ar' => 'شهادة الامتثال التنظيمي',
                'description' => 'Certificate of compliance with Iraqi pharmaceutical regulations',
                'description_ar' => 'شهادة الامتثال للوائح الدوائية العراقية',
                'document_number' => 'CERT-REG-001-2025',
                'file_name' => 'regulatory_compliance_certificate.pdf',
                'original_name' => 'Compliance Certificate.pdf',
                'file_path' => 'documents/regulatory_compliance_certificate.pdf',
                'file_size' => 1024000, // 1MB
                'mime_type' => 'application/pdf',
                'file_extension' => 'pdf',
                'status' => Document::STATUS_APPROVED,
                'visibility' => Document::VISIBILITY_RESTRICTED,
                'is_template' => false,
                'version' => '1.0',
                'tags' => ['Certificate', 'Regulatory', 'Compliance', 'Legal'],
                'metadata' => [
                    'issuing_authority' => 'Iraqi Ministry of Health',
                    'certificate_number' => 'MOH-2025-001',
                    'validity_period' => '2 years',
                    'classification' => 'Confidential',
                ],
                'checksum' => hash('md5', 'sample_certificate'),
                'download_count' => 8,
                'expires_at' => now()->addYears(2),
                'created_by' => 1,
                'approved_by' => 1,
                'approved_at' => now()->subDays(3),
            ],
            [
                'title' => 'Product Specification Document',
                'title_ar' => 'وثيقة مواصفات المنتج',
                'description' => 'Detailed specifications for Paracetamol 500mg tablets',
                'description_ar' => 'مواصفات مفصلة لأقراص الباراسيتامول 500 ملغ',
                'document_number' => 'SPEC-PAR-500-2025',
                'file_name' => 'paracetamol_500mg_specifications.docx',
                'original_name' => 'Paracetamol Specifications.docx',
                'file_path' => 'documents/paracetamol_500mg_specifications.docx',
                'file_size' => 768000, // 750KB
                'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'file_extension' => 'docx',
                'status' => Document::STATUS_PENDING_REVIEW,
                'visibility' => Document::VISIBILITY_INTERNAL,
                'is_template' => false,
                'version' => '1.2',
                'tags' => ['Specification', 'Product', 'Paracetamol', 'Pharmaceutical'],
                'metadata' => [
                    'product_code' => 'PAR-500',
                    'active_ingredient' => 'Paracetamol',
                    'strength' => '500mg',
                    'dosage_form' => 'Tablet',
                ],
                'checksum' => hash('md5', 'sample_specification'),
                'download_count' => 3,
                'expires_at' => now()->addMonths(18),
                'created_by' => 1,
            ],
            [
                'title' => 'Training Manual - Good Manufacturing Practices',
                'title_ar' => 'دليل التدريب - ممارسات التصنيع الجيدة',
                'description' => 'Comprehensive training manual for GMP compliance in pharmaceutical manufacturing',
                'description_ar' => 'دليل تدريب شامل للامتثال لممارسات التصنيع الجيدة في التصنيع الدوائي',
                'document_number' => 'TRN-GMP-001-2025',
                'file_name' => 'gmp_training_manual.pdf',
                'original_name' => 'GMP Training Manual.pdf',
                'file_path' => 'documents/gmp_training_manual.pdf',
                'file_size' => 5242880, // 5MB
                'mime_type' => 'application/pdf',
                'file_extension' => 'pdf',
                'status' => Document::STATUS_APPROVED,
                'visibility' => Document::VISIBILITY_INTERNAL,
                'is_template' => false,
                'version' => '3.0',
                'tags' => ['Training', 'GMP', 'Manufacturing', 'Compliance'],
                'metadata' => [
                    'training_type' => 'Mandatory',
                    'target_audience' => 'All Manufacturing Staff',
                    'duration' => '8 hours',
                    'certification_required' => true,
                ],
                'checksum' => hash('md5', 'sample_training_manual'),
                'download_count' => 67,
                'expires_at' => now()->addYear(),
                'created_by' => 1,
                'approved_by' => 1,
                'approved_at' => now()->subDays(15),
            ],
            [
                'title' => 'Deviation Report Form',
                'title_ar' => 'نموذج تقرير الانحراف',
                'description' => 'Form for reporting deviations from standard procedures',
                'description_ar' => 'نموذج للإبلاغ عن الانحرافات عن الإجراءات المعيارية',
                'document_number' => 'FORM-DEV-001-2025',
                'file_name' => 'deviation_report_form.pdf',
                'original_name' => 'Deviation Report Form.pdf',
                'file_path' => 'documents/deviation_report_form.pdf',
                'file_size' => 256000, // 250KB
                'mime_type' => 'application/pdf',
                'file_extension' => 'pdf',
                'status' => Document::STATUS_DRAFT,
                'visibility' => Document::VISIBILITY_INTERNAL,
                'is_template' => true,
                'template_type' => 'Form',
                'version' => '1.0',
                'tags' => ['Form', 'Deviation', 'Quality', 'Reporting'],
                'metadata' => [
                    'form_type' => 'Quality Control',
                    'approval_required' => true,
                    'retention_period' => '7 years',
                ],
                'checksum' => hash('md5', 'sample_deviation_form'),
                'download_count' => 0,
                'created_by' => 1,
            ],
        ];

        foreach ($documents as $documentData) {
            // Create a sample file for each document
            $this->createSampleFile($documentData['file_path'], $documentData['file_extension']);
            
            Document::create($documentData);
        }
    }

    private function createSampleFile($filePath, $extension)
    {
        $content = "Sample document content for testing purposes.\n";
        $content .= "Document created at: " . now()->toISOString() . "\n";
        $content .= "File extension: " . $extension . "\n";
        $content .= "This is a placeholder file for the Document Management System demo.\n";
        
        // Add some content based on file type
        switch ($extension) {
            case 'pdf':
                $content .= "\n--- PDF Document Content ---\n";
                $content .= "This would be a PDF document with formatted content.\n";
                break;
            case 'xlsx':
                $content .= "\n--- Excel Spreadsheet Content ---\n";
                $content .= "This would be an Excel file with data tables.\n";
                break;
            case 'docx':
                $content .= "\n--- Word Document Content ---\n";
                $content .= "This would be a Word document with formatted text.\n";
                break;
        }
        
        Storage::disk('private')->put($filePath, $content);
    }

    private function createDocumentSignatures()
    {
        $documents = Document::where('status', Document::STATUS_APPROVED)->get();
        
        foreach ($documents->take(3) as $document) {
            DocumentSignature::create([
                'document_id' => $document->id,
                'user_id' => 1,
                'signature_type' => DocumentSignature::TYPE_ELECTRONIC,
                'signature_data' => [
                    'signature_text' => 'Approved by System Administrator',
                    'signature_date' => now()->toISOString(),
                    'signature_hash' => hash('sha256', 'sample_signature_' . $document->id),
                ],
                'signed_at' => now(),
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Document Management System',
                'certificate_data' => [
                    'certificate_type' => 'system_generated',
                    'issuer' => 'Document Management System',
                ],
                'verification_code' => substr(hash('md5', 'verification_' . $document->id), 0, 32),
                'is_verified' => true,
                'notes' => 'Document approval signature',
            ]);
        }
    }

    private function createDocumentApprovals()
    {
        $pendingDocument = Document::where('status', Document::STATUS_PENDING_REVIEW)->first();
        
        if ($pendingDocument) {
            DocumentApproval::create([
                'document_id' => $pendingDocument->id,
                'step_number' => 1,
                'approver_id' => 1,
                'approver_type' => DocumentApproval::APPROVER_USER,
                'status' => DocumentApproval::STATUS_PENDING,
                'due_date' => now()->addDays(7),
                'priority' => DocumentApproval::PRIORITY_NORMAL,
                'notification_sent' => true,
            ]);
        }
    }

    private function createAccessLogs()
    {
        $documents = Document::all();
        
        foreach ($documents as $document) {
            // Create view logs
            for ($i = 0; $i < rand(3, 8); $i++) {
                DocumentAccessLog::create([
                    'document_id' => $document->id,
                    'user_id' => 1,
                    'action' => DocumentAccessLog::ACTION_VIEW,
                    'ip_address' => '127.0.0.1',
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'session_id' => 'session_' . uniqid(),
                    'access_method' => DocumentAccessLog::METHOD_WEB,
                    'duration' => rand(30, 300), // 30 seconds to 5 minutes
                    'pages_viewed' => rand(1, 5),
                    'security_level' => DocumentAccessLog::SECURITY_MEDIUM,
                    'created_at' => now()->subDays(rand(1, 30)),
                ]);
            }
            
            // Create download logs
            if ($document->download_count > 0) {
                for ($i = 0; $i < min($document->download_count, 5); $i++) {
                    DocumentAccessLog::create([
                        'document_id' => $document->id,
                        'user_id' => 1,
                        'action' => DocumentAccessLog::ACTION_DOWNLOAD,
                        'ip_address' => '127.0.0.1',
                        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                        'session_id' => 'session_' . uniqid(),
                        'access_method' => DocumentAccessLog::METHOD_WEB,
                        'download_attempted' => true,
                        'download_successful' => true,
                        'security_level' => DocumentAccessLog::SECURITY_HIGH,
                        'created_at' => now()->subDays(rand(1, 20)),
                    ]);
                }
            }
        }
    }
}
