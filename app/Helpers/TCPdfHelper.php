<?php

namespace App\Helpers;

use TCPDF;

class TCPdfHelper
{
    /**
     * Create TCPDF instance with Arabic support
     */
    public static function createInstance(array $config = []): TCPDF
    {
        $defaultConfig = [
            'orientation' => 'P',
            'unit' => 'mm',
            'format' => 'A4',
            'unicode' => true,
            'encoding' => 'UTF-8',
            'diskcache' => false,
            'pdfa' => false
        ];

        $config = array_merge($defaultConfig, $config);

        $pdf = new TCPDF(
            $config['orientation'],
            $config['unit'],
            $config['format'],
            $config['unicode'],
            $config['encoding'],
            $config['diskcache'],
            $config['pdfa']
        );

        // Set document information
        $pdf->SetCreator('MaxCon ERP');
        $pdf->SetAuthor('MaxCon ERP System');
        $pdf->SetTitle('PDF Document');
        $pdf->SetSubject('Arabic PDF Document');
        $pdf->SetKeywords('PDF, Arabic, RTL, MaxCon');

        // Set default header data
        $pdf->SetHeaderData('', 0, 'نظام MaxCon ERP', 'نظام إدارة الموارد المؤسسية');

        // Set header and footer fonts
        $pdf->setHeaderFont(['dejavusans', '', 14]);
        $pdf->setFooterFont(['dejavusans', '', 10]);

        // Set default monospaced font
        $pdf->SetDefaultMonospacedFont('dejavusansmono');

        // Set margins
        $pdf->SetMargins(15, 27, 15);
        $pdf->SetHeaderMargin(5);
        $pdf->SetFooterMargin(10);

        // Set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 25);

        // Set image scale factor
        $pdf->setImageScale(1.25);

        // Set font
        $pdf->SetFont('dejavusans', '', 12);

        // Set RTL direction
        $pdf->setRTL(true);

        return $pdf;
    }

    /**
     * Create PDF from HTML with Arabic support
     */
    public static function createFromHtml(string $html, array $config = []): TCPDF
    {
        $pdf = self::createInstance($config);
        
        // Add a page
        $pdf->AddPage();
        
        // Write HTML
        $pdf->writeHTML($html, true, false, true, false, '');
        
        return $pdf;
    }

    /**
     * Create simple Arabic PDF
     */
    public static function createSimpleArabicPdf(string $title, string $content, array $config = []): TCPDF
    {
        $html = '
        <style>
            body {
                font-family: dejavusans;
                direction: rtl;
                text-align: right;
                font-size: 12pt;
                line-height: 1.6;
            }
            .header {
                background-color: #3498db;
                color: white;
                padding: 20px;
                text-align: center;
                margin-bottom: 20px;
                border-radius: 8px;
            }
            .content {
                padding: 20px;
                direction: rtl;
                text-align: right;
            }
            h1, h2, h3, h4, h5, h6 {
                direction: rtl;
                text-align: right;
                margin-bottom: 10px;
                font-weight: bold;
            }
            h1 { font-size: 18pt; }
            h2 { font-size: 16pt; }
            h3 { font-size: 14pt; }
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
            table {
                width: 100%;
                border-collapse: collapse;
                margin: 20px 0;
                direction: rtl;
            }
            th, td {
                border: 1px solid #ddd;
                padding: 8px;
                text-align: center;
                direction: rtl;
            }
            th {
                background-color: #f2f2f2;
                font-weight: bold;
            }
            .text-center { text-align: center; }
            .text-right { text-align: right; }
            .font-bold { font-weight: bold; }
            .bg-blue { background-color: #e3f2fd; }
            .bg-green { background-color: #e8f5e8; }
            .bg-gray { background-color: #f5f5f5; }
        </style>
        
        <div class="header">
            <h1>' . htmlspecialchars($title) . '</h1>
        </div>
        <div class="content">
            ' . $content . '
        </div>';

        return self::createFromHtml($html, $config);
    }

    /**
     * Stream PDF to browser
     */
    public static function streamPdf(TCPDF $pdf, string $filename): \Illuminate\Http\Response
    {
        $content = $pdf->Output('', 'S');
        
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
    public static function downloadPdf(TCPDF $pdf, string $filename): \Illuminate\Http\Response
    {
        $content = $pdf->Output('', 'S');
        
        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ]);
    }

    /**
     * Save PDF to file
     */
    public static function savePdf(TCPDF $pdf, string $filename, string $path = null): string
    {
        $path = $path ?: storage_path('app/pdfs/');
        
        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }
        
        $fullPath = $path . $filename;
        $pdf->Output($fullPath, 'F');
        
        return $fullPath;
    }

    /**
     * Add Arabic text to PDF
     */
    public static function addArabicText(TCPDF $pdf, string $text, int $x = null, int $y = null, array $options = []): void
    {
        $defaultOptions = [
            'font' => 'dejavusans',
            'style' => '',
            'size' => 12,
            'align' => 'R',
            'width' => 0,
            'height' => 0,
            'border' => 0,
            'ln' => 1,
            'fill' => false
        ];

        $options = array_merge($defaultOptions, $options);

        // Set font
        $pdf->SetFont($options['font'], $options['style'], $options['size']);

        // Set position if provided
        if ($x !== null && $y !== null) {
            $pdf->SetXY($x, $y);
        }

        // Add text
        $pdf->Cell(
            $options['width'],
            $options['height'],
            $text,
            $options['border'],
            $options['ln'],
            $options['align'],
            $options['fill']
        );
    }

    /**
     * Add Arabic table to PDF
     */
    public static function addArabicTable(TCPDF $pdf, array $headers, array $data, array $options = []): void
    {
        $defaultOptions = [
            'header_font' => 'dejavusans',
            'header_style' => 'B',
            'header_size' => 12,
            'data_font' => 'dejavusans',
            'data_style' => '',
            'data_size' => 10,
            'cell_height' => 7,
            'border' => 1,
            'header_fill' => true,
            'header_bg_color' => [240, 240, 240],
            'data_fill' => false
        ];

        $options = array_merge($defaultOptions, $options);

        // Calculate column widths
        $pageWidth = $pdf->getPageWidth() - $pdf->getMargins()['left'] - $pdf->getMargins()['right'];
        $colWidth = $pageWidth / count($headers);

        // Set header font
        $pdf->SetFont($options['header_font'], $options['header_style'], $options['header_size']);

        // Set header background color
        if ($options['header_fill']) {
            $pdf->SetFillColor($options['header_bg_color'][0], $options['header_bg_color'][1], $options['header_bg_color'][2]);
        }

        // Add headers
        foreach ($headers as $header) {
            $pdf->Cell($colWidth, $options['cell_height'], $header, $options['border'], 0, 'C', $options['header_fill']);
        }
        $pdf->Ln();

        // Set data font
        $pdf->SetFont($options['data_font'], $options['data_style'], $options['data_size']);

        // Add data rows
        foreach ($data as $row) {
            foreach ($row as $cell) {
                $pdf->Cell($colWidth, $options['cell_height'], $cell, $options['border'], 0, 'C', $options['data_fill']);
            }
            $pdf->Ln();
        }
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
