<?php

namespace App\Helpers;

use Mpdf\Mpdf;

class MPdfHelper
{
    /**
     * Create mPDF instance with Arabic support
     */
    public static function createInstance(array $config = []): Mpdf
    {
        $defaultConfig = [
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P',
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 16,
            'margin_bottom' => 16,
            'margin_header' => 9,
            'margin_footer' => 9,
            'default_font_size' => 12,
            'default_font' => 'dejavusans',
            'direction' => 'rtl',
            'allow_charset_conversion' => true,
            'charset_in' => 'UTF-8',
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
            'autoVietnamese' => true,
            'autoArabic' => true,
            'baseScript' => 1,
            'tempDir' => storage_path('app/temp'),
        ];

        $config = array_merge($defaultConfig, $config);

        return new Mpdf($config);
    }

    /**
     * Create PDF from HTML with Arabic support
     */
    public static function createFromHtml(string $html, array $config = []): Mpdf
    {
        $mpdf = self::createInstance($config);
        
        // Set Arabic font and direction
        $mpdf->SetDirectionality('rtl');
        
        // Write HTML
        $mpdf->WriteHTML($html);
        
        return $mpdf;
    }

    /**
     * Create PDF from view with Arabic support
     */
    public static function createFromView(string $view, array $data = [], array $config = []): Mpdf
    {
        $html = view($view, $data)->render();
        return self::createFromHtml($html, $config);
    }

    /**
     * Get Arabic-optimized CSS
     */
    public static function getArabicCss(): string
    {
        return '
        <style>
            @page {
                margin: 2cm;
                size: A4 portrait;
            }
            
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            
            body {
                font-family: "DejaVu Sans", "Arial Unicode MS", Arial, sans-serif;
                font-size: 14px;
                line-height: 1.6;
                color: #333;
                direction: rtl;
                text-align: right;
            }
            
            .arabic-text {
                font-family: "DejaVu Sans", "Arial Unicode MS", Arial, sans-serif;
                direction: rtl;
                text-align: right;
            }
            
            .header {
                background: #667eea;
                color: white;
                padding: 20px;
                text-align: center;
                direction: rtl;
                margin-bottom: 20px;
                border-radius: 8px;
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
                font-family: "DejaVu Sans", Arial, sans-serif;
            }
            
            .table th, .table td {
                border: 1px solid #ddd;
                padding: 12px 8px;
                text-align: center;
                direction: rtl;
            }
            
            .table th {
                background: #f8f9fa;
                font-weight: bold;
                color: #495057;
            }
            
            .table tbody tr:nth-child(even) {
                background: #f8f9fa;
            }
            
            h1, h2, h3, h4, h5, h6 {
                font-family: "DejaVu Sans", Arial, sans-serif;
                direction: rtl;
                text-align: right;
                margin-bottom: 10px;
                font-weight: bold;
            }
            
            h1 { font-size: 24px; }
            h2 { font-size: 20px; }
            h3 { font-size: 18px; }
            h4 { font-size: 16px; }
            
            p {
                margin-bottom: 10px;
                direction: rtl;
                text-align: right;
            }
            
            ul, ol {
                margin: 10px 0;
                padding-right: 20px;
                direction: rtl;
            }
            
            li {
                margin-bottom: 5px;
                direction: rtl;
                text-align: right;
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
            
            .invoice-header {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 30px;
                text-align: center;
                margin-bottom: 30px;
                border-radius: 10px;
            }
            
            .company-name {
                font-size: 28px;
                font-weight: bold;
                margin-bottom: 10px;
            }
            
            .invoice-details {
                background: #f8f9fa;
                padding: 20px;
                border-radius: 8px;
                margin-bottom: 20px;
            }
            
            .customer-details {
                background: #e3f2fd;
                padding: 20px;
                border-radius: 8px;
                margin-bottom: 20px;
            }
            
            .total-section {
                background: #f1f8e9;
                padding: 20px;
                border-radius: 8px;
                margin-top: 20px;
                border: 2px solid #4caf50;
            }
            
            .footer {
                text-align: center;
                margin-top: 40px;
                padding-top: 20px;
                border-top: 2px solid #e0e0e0;
                color: #666;
                font-size: 12px;
            }
        </style>';
    }

    /**
     * Create simple Arabic PDF
     */
    public static function createSimpleArabicPdf(string $title, string $content, array $config = []): Mpdf
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

        return self::createFromHtml($html, $config);
    }

    /**
     * Generate invoice PDF with mPDF
     */
    public static function generateInvoice($invoice, array $config = []): Mpdf
    {
        return self::createFromView('sales.invoices.mpdf', compact('invoice'), $config);
    }

    /**
     * Save PDF to file
     */
    public static function savePdf(Mpdf $mpdf, string $filename, string $path = null): string
    {
        $path = $path ?: storage_path('app/pdfs/');
        
        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }
        
        $fullPath = $path . $filename;
        $mpdf->Output($fullPath, 'F');
        
        return $fullPath;
    }

    /**
     * Stream PDF to browser
     */
    public static function streamPdf(Mpdf $mpdf, string $filename): \Illuminate\Http\Response
    {
        $content = $mpdf->Output('', 'S');
        
        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ]);
    }

    /**
     * Download PDF
     */
    public static function downloadPdf(Mpdf $mpdf, string $filename): \Illuminate\Http\Response
    {
        $content = $mpdf->Output('', 'S');
        
        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ]);
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
}
