<?php

namespace App\Helpers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class PdfHelper
{
    /**
     * Default PDF options for Arabic support
     */
    public static function getDefaultOptions(): array
    {
        return [
            'defaultFont' => 'DejaVu Sans',
            'isHtml5ParserEnabled' => true,
            'isPhpEnabled' => false,
            'isRemoteEnabled' => false,
            'isFontSubsettingEnabled' => true,
            'defaultPaperSize' => 'a4',
            'defaultPaperOrientation' => 'portrait',
            'isJavascriptEnabled' => false,
            'debugPng' => false,
            'debugKeepTemp' => false,
            'debugCss' => false,
            'debugLayout' => false,
            'debugLayoutLines' => false,
            'debugLayoutBlocks' => false,
            'debugLayoutInline' => false,
            'debugLayoutPaddingBox' => false,
            'tempDir' => storage_path('app/temp'),
            'chroot' => base_path(),
            'logOutputFile' => storage_path('logs/dompdf.log'),
            'enable_font_subsetting' => true,
            'pdf_backend' => 'CPDF',
        ];
    }

    /**
     * Create PDF from view with Arabic support
     */
    public static function createFromView(string $view, array $data = [], array $options = []): \Barryvdh\DomPDF\PDF
    {
        $pdfOptions = array_merge(self::getDefaultOptions(), $options);
        
        return Pdf::loadView($view, $data)
                  ->setPaper('a4', 'portrait')
                  ->setOptions($pdfOptions);
    }

    /**
     * Create PDF from HTML with Arabic support
     */
    public static function createFromHtml(string $html, array $options = []): \Barryvdh\DomPDF\PDF
    {
        $pdfOptions = array_merge(self::getDefaultOptions(), $options);
        
        return Pdf::loadHTML($html)
                  ->setPaper('a4', 'portrait')
                  ->setOptions($pdfOptions);
    }

    /**
     * Generate invoice PDF
     */
    public static function generateInvoice($invoice, array $options = []): \Barryvdh\DomPDF\PDF
    {
        return self::createFromView('sales.invoices.pdf', compact('invoice'), $options);
    }

    /**
     * Generate report PDF
     */
    public static function generateReport(string $title, array $data, string $template = 'reports.pdf', array $options = []): \Barryvdh\DomPDF\PDF
    {
        $reportData = array_merge($data, ['title' => $title]);
        return self::createFromView($template, $reportData, $options);
    }

    /**
     * Save PDF to storage
     */
    public static function savePdf(\Barryvdh\DomPDF\PDF $pdf, string $filename, string $disk = 'local'): string
    {
        $content = $pdf->output();
        $path = 'pdfs/' . $filename;
        
        Storage::disk($disk)->put($path, $content);
        
        return $path;
    }

    /**
     * Get Arabic-friendly CSS for PDF
     */
    public static function getArabicCss(): string
    {
        return '
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            
            body {
                font-family: "DejaVu Sans", Arial, sans-serif;
                font-size: 14px;
                line-height: 1.6;
                color: #333;
                direction: rtl;
                text-align: right;
                unicode-bidi: bidi-override;
            }
            
            .arabic-text {
                font-family: "DejaVu Sans", Arial, sans-serif;
                direction: rtl;
                text-align: right;
                unicode-bidi: embed;
            }
            
            .header {
                background: #667eea;
                color: white;
                padding: 20px;
                text-align: center;
                direction: rtl;
                margin-bottom: 20px;
            }
            
            .content {
                padding: 20px;
                direction: rtl;
                text-align: right;
            }
            
            .table {
                width: 100%;
                border-collapse: collapse;
                margin: 20px 0;
                direction: rtl;
            }
            
            .table th, .table td {
                border: 1px solid #ddd;
                padding: 8px;
                text-align: center;
                direction: rtl;
            }
            
            .table th {
                background: #f2f2f2;
                font-weight: bold;
            }
            
            .text-center { text-align: center; }
            .text-right { text-align: right; }
            .text-left { text-align: left; }
            
            .mb-1 { margin-bottom: 5px; }
            .mb-2 { margin-bottom: 10px; }
            .mb-3 { margin-bottom: 15px; }
            .mb-4 { margin-bottom: 20px; }
            
            .mt-1 { margin-top: 5px; }
            .mt-2 { margin-top: 10px; }
            .mt-3 { margin-top: 15px; }
            .mt-4 { margin-top: 20px; }
            
            .p-1 { padding: 5px; }
            .p-2 { padding: 10px; }
            .p-3 { padding: 15px; }
            .p-4 { padding: 20px; }
            
            .font-bold { font-weight: bold; }
            .font-normal { font-weight: normal; }
            
            .text-sm { font-size: 12px; }
            .text-lg { font-size: 16px; }
            .text-xl { font-size: 18px; }
            .text-2xl { font-size: 24px; }
            
            .bg-gray-100 { background-color: #f7fafc; }
            .bg-gray-200 { background-color: #edf2f7; }
            .bg-blue-100 { background-color: #ebf8ff; }
            .bg-green-100 { background-color: #f0fff4; }
            .bg-red-100 { background-color: #fed7d7; }
            .bg-yellow-100 { background-color: #fffff0; }
            
            .text-gray-600 { color: #718096; }
            .text-gray-800 { color: #2d3748; }
            .text-blue-600 { color: #3182ce; }
            .text-green-600 { color: #38a169; }
            .text-red-600 { color: #e53e3e; }
            .text-yellow-600 { color: #d69e2e; }
            
            .border { border: 1px solid #e2e8f0; }
            .border-t { border-top: 1px solid #e2e8f0; }
            .border-b { border-bottom: 1px solid #e2e8f0; }
            .border-l { border-left: 1px solid #e2e8f0; }
            .border-r { border-right: 1px solid #e2e8f0; }
            
            .rounded { border-radius: 4px; }
            .rounded-lg { border-radius: 8px; }
            
            .shadow { box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); }
            .shadow-lg { box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1); }
            
            @page {
                margin: 2cm;
                size: A4 portrait;
            }
            
            @media print {
                body { font-size: 12px; }
                .header { background: #667eea !important; }
            }
        </style>';
    }

    /**
     * Create simple Arabic PDF with predefined styling
     */
    public static function createSimpleArabicPdf(string $title, string $content, array $options = []): \Barryvdh\DomPDF\PDF
    {
        $html = '
        <!DOCTYPE html>
        <html dir="rtl" lang="ar">
        <head>
            <meta charset="UTF-8">
            <title>' . htmlspecialchars($title) . '</title>
            ' . self::getArabicCss() . '
        </head>
        <body>
            <div class="header">
                <h1>' . htmlspecialchars($title) . '</h1>
            </div>
            <div class="content">
                ' . $content . '
            </div>
        </body>
        </html>';

        return self::createFromHtml($html, $options);
    }

    /**
     * Ensure temp directory exists
     */
    public static function ensureTempDirectory(): void
    {
        $tempDir = storage_path('app/temp');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
    }

    /**
     * Clean old PDF files from temp directory
     */
    public static function cleanTempFiles(int $olderThanHours = 24): void
    {
        $tempDir = storage_path('app/temp');
        if (!file_exists($tempDir)) {
            return;
        }

        $files = glob($tempDir . '/*');
        $cutoff = time() - ($olderThanHours * 3600);

        foreach ($files as $file) {
            if (is_file($file) && filemtime($file) < $cutoff) {
                unlink($file);
            }
        }
    }

    /**
     * Get PDF file size in human readable format
     */
    public static function getFileSize(\Barryvdh\DomPDF\PDF $pdf): string
    {
        $bytes = strlen($pdf->output());
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
