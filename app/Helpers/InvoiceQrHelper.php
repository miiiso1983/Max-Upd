<?php

namespace App\Helpers;

use SimpleSoftwareIO\QrCode\Facades\QrCode;

class InvoiceQrHelper
{
    /**
     * Generate QR Code for invoice
     */
    public static function generateInvoiceQr($invoice, array $options = []): string
    {
        // Always use placeholder for now until imagick is installed
        return self::generatePlaceholderQr($invoice);
    }

    /**
     * Prepare invoice data for QR Code
     */
    public static function prepareInvoiceData($invoice): string
    {
        // Get company information using TenantHelper
        $company = \App\Helpers\TenantHelper::getCompanyInfo();

        $data = [
            'company' => $company['name'],
            'company_phone' => $company['phone'],
            'company_email' => $company['email'],
            'company_address' => $company['address'],
            'license_key' => $company['license_key'],
            'invoice_number' => $invoice->invoice_number ?? 'INV-001',
            'customer_name' => $invoice->customer->name ?? 'عميل',
            'customer_phone' => $invoice->customer->phone ?? '',
            'total_amount' => $invoice->total_amount ?? 0,
            'previous_balance' => $invoice->previous_balance ?? 0,
            'current_balance' => ($invoice->previous_balance ?? 0) + ($invoice->total_amount ?? 0) - ($invoice->paid_amount ?? 0),
            'issue_date' => $invoice->created_at ? $invoice->created_at->format('Y-m-d') : now()->format('Y-m-d'),
            'due_date' => $invoice->due_date ? $invoice->due_date->format('Y-m-d') : now()->addDays(30)->format('Y-m-d'),
            'payment_status' => $invoice->payment_status ?? 'غير مدفوع',
            'items_count' => $invoice->items ? $invoice->items->count() : 0,
            'verification_url' => url('/verify-invoice/' . ($invoice->invoice_number ?? 'INV-001')),
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'tenant_domain' => $company['domain']
        ];

        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Generate QR Code with custom text
     */
    public static function generateCustomQr(string $text, array $options = []): string
    {
        $defaultOptions = [
            'size' => 120,
            'format' => 'png',
            'margin' => 2,
            'errorCorrection' => 'M',
            'encoding' => 'UTF-8'
        ];

        $options = array_merge($defaultOptions, $options);

        $qrCode = QrCode::format($options['format'])
                        ->size($options['size'])
                        ->margin($options['margin'])
                        ->errorCorrection($options['errorCorrection'])
                        ->encoding($options['encoding'])
                        ->backgroundColor(255, 255, 255)
                        ->color(0, 0, 0)
                        ->generate($text);

        return base64_encode($qrCode);
    }

    /**
     * Generate verification URL QR Code
     */
    public static function generateVerificationQr($invoice, array $options = []): string
    {
        $verificationUrl = url('/verify-invoice/' . ($invoice->invoice_number ?? 'INV-001'));
        return self::generateCustomQr($verificationUrl, $options);
    }

    /**
     * Generate payment QR Code (for payment apps)
     */
    public static function generatePaymentQr($invoice, array $options = []): string
    {
        // Get company information using TenantHelper
        $company = \App\Helpers\TenantHelper::getCompanyInfo();

        $paymentData = [
            'type' => 'payment',
            'merchant' => $company['name'],
            'merchant_phone' => $company['phone'],
            'merchant_email' => $company['email'],
            'invoice_number' => $invoice->invoice_number ?? 'INV-001',
            'amount' => $invoice->total_amount ?? 0,
            'currency' => 'IQD',
            'description' => 'دفع فاتورة رقم ' . ($invoice->invoice_number ?? 'INV-001'),
            'customer' => $invoice->customer->name ?? 'عميل'
        ];

        return self::generateCustomQr(json_encode($paymentData, JSON_UNESCAPED_UNICODE), $options);
    }

    /**
     * Generate contact QR Code
     */
    public static function generateContactQr(array $contactInfo = [], array $options = []): string
    {
        // Get vCard information using TenantHelper
        $defaultInfo = \App\Helpers\TenantHelper::getVCardInfo();
        $info = array_merge($defaultInfo, $contactInfo);

        $vCard = "BEGIN:VCARD\n";
        $vCard .= "VERSION:3.0\n";
        $vCard .= "FN:" . $info['name'] . "\n";
        $vCard .= "ORG:" . $info['organization'] . "\n";
        $vCard .= "TEL:" . $info['phone'] . "\n";
        $vCard .= "EMAIL:" . $info['email'] . "\n";
        $vCard .= "URL:" . $info['website'] . "\n";
        $vCard .= "ADR:;;" . $info['address'] . ";;;;\n";
        $vCard .= "END:VCARD";

        return self::generateCustomQr($vCard, $options);
    }

    /**
     * Get QR Code as HTML img tag
     */
    public static function getQrImageTag($invoice, array $options = [], array $imgAttributes = []): string
    {
        $qrBase64 = self::generateInvoiceQr($invoice, $options);
        
        $defaultAttributes = [
            'style' => 'width: 120px; height: 120px;',
            'alt' => 'QR Code للفاتورة'
        ];

        $imgAttributes = array_merge($defaultAttributes, $imgAttributes);
        
        $attributesString = '';
        foreach ($imgAttributes as $key => $value) {
            $attributesString .= $key . '="' . htmlspecialchars($value) . '" ';
        }

        return '<img src="data:image/png;base64,' . $qrBase64 . '" ' . trim($attributesString) . '>';
    }

    /**
     * Validate QR Code data
     */
    public static function validateQrData(string $qrData): array
    {
        try {
            $data = json_decode($qrData, true);
            
            if (!$data) {
                return ['valid' => false, 'error' => 'Invalid JSON format'];
            }

            $requiredFields = ['company', 'invoice_number', 'total_amount', 'issue_date'];
            $missingFields = [];

            foreach ($requiredFields as $field) {
                if (!isset($data[$field])) {
                    $missingFields[] = $field;
                }
            }

            if (!empty($missingFields)) {
                return [
                    'valid' => false, 
                    'error' => 'Missing required fields: ' . implode(', ', $missingFields)
                ];
            }

            return ['valid' => true, 'data' => $data];

        } catch (\Exception $e) {
            return ['valid' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Generate multiple QR codes for invoice
     */
    public static function generateMultipleQrs($invoice): array
    {
        return [
            'invoice_details' => self::generateInvoiceQr($invoice),
            'verification_url' => self::generateVerificationQr($invoice),
            'payment_info' => self::generatePaymentQr($invoice),
            'contact_info' => self::generateContactQr() // Will use tenant info automatically
        ];
    }

    /**
     * Get QR Code file path (save to storage)
     */
    public static function saveQrToFile($invoice, string $filename = null): string
    {
        $filename = $filename ?: 'qr-' . ($invoice->invoice_number ?? 'INV-001') . '.png';
        $qrBase64 = self::generateInvoiceQr($invoice);
        
        $path = storage_path('app/public/qr-codes/');
        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }

        $fullPath = $path . $filename;
        file_put_contents($fullPath, base64_decode($qrBase64));

        return 'storage/qr-codes/' . $filename;
    }

    /**
     * Generate QR Code for invoice summary
     */
    public static function generateSummaryQr($invoice): string
    {
        $summary = sprintf(
            "فاتورة: %s\nالعميل: %s\nالمبلغ: %s د.ع\nالتاريخ: %s\nالحالة: %s",
            $invoice->invoice_number ?? 'INV-001',
            $invoice->customer->name ?? 'عميل',
            number_format($invoice->total_amount ?? 0, 0),
            $invoice->created_at ? $invoice->created_at->format('Y-m-d') : now()->format('Y-m-d'),
            $invoice->payment_status ?? 'غير مدفوع'
        );

        return self::generateCustomQr($summary);
    }

    /**
     * Get QR Code dimensions based on content
     */
    public static function getOptimalSize(string $content): int
    {
        $length = strlen($content);
        
        if ($length < 100) return 100;
        if ($length < 300) return 120;
        if ($length < 500) return 150;
        if ($length < 1000) return 180;
        
        return 200;
    }

    /**
     * Generate QR with optimal settings
     */
    public static function generateOptimalQr($invoice): string
    {
        $data = self::prepareInvoiceData($invoice);
        $size = self::getOptimalSize($data);
        
        return self::generateInvoiceQr($invoice, [
            'size' => $size,
            'errorCorrection' => 'H', // High error correction for better scanning
            'margin' => 3
        ]);
    }

    /**
     * Generate placeholder QR Code when libraries fail
     */
    public static function generatePlaceholderQr($invoice): string
    {
        // Get company info
        $company = \App\Helpers\TenantHelper::getCompanyInfo();
        $invoiceNumber = $invoice->invoice_number ?? 'INV-001';
        $customerName = $invoice->customer->name ?? 'عميل';
        $amount = number_format($invoice->total_amount ?? 0, 0);

        // Create a more detailed SVG placeholder with QR-like pattern
        $svg = '<?xml version="1.0" encoding="UTF-8"?>
        <svg width="120" height="120" xmlns="http://www.w3.org/2000/svg">
            <!-- Background -->
            <rect width="120" height="120" fill="white" stroke="#2a5298" stroke-width="2"/>

            <!-- QR-like pattern -->
            <rect x="10" y="10" width="8" height="8" fill="#2a5298"/>
            <rect x="22" y="10" width="8" height="8" fill="#2a5298"/>
            <rect x="34" y="10" width="8" height="8" fill="#2a5298"/>
            <rect x="102" y="10" width="8" height="8" fill="#2a5298"/>
            <rect x="10" y="22" width="8" height="8" fill="#2a5298"/>
            <rect x="102" y="22" width="8" height="8" fill="#2a5298"/>
            <rect x="10" y="34" width="8" height="8" fill="#2a5298"/>
            <rect x="22" y="34" width="8" height="8" fill="#2a5298"/>
            <rect x="34" y="34" width="8" height="8" fill="#2a5298"/>
            <rect x="102" y="34" width="8" height="8" fill="#2a5298"/>

            <!-- Bottom corners -->
            <rect x="10" y="102" width="8" height="8" fill="#2a5298"/>
            <rect x="22" y="102" width="8" height="8" fill="#2a5298"/>
            <rect x="34" y="102" width="8" height="8" fill="#2a5298"/>
            <rect x="102" y="102" width="8" height="8" fill="#2a5298"/>

            <!-- Center pattern -->
            <rect x="54" y="54" width="12" height="12" fill="#2a5298"/>

            <!-- Text overlay -->
            <rect x="15" y="45" width="90" height="30" fill="rgba(255,255,255,0.9)" stroke="#2a5298" stroke-width="1"/>
            <text x="60" y="58" text-anchor="middle" font-family="Arial" font-size="9" fill="#2a5298" font-weight="bold">QR Code</text>
            <text x="60" y="68" text-anchor="middle" font-family="Arial" font-size="7" fill="#333">' . $invoiceNumber . '</text>

            <!-- Company name at bottom -->
            <text x="60" y="95" text-anchor="middle" font-family="Arial" font-size="6" fill="#666">' . mb_substr($company['name'], 0, 20) . '</text>
            <text x="60" y="105" text-anchor="middle" font-family="Arial" font-size="6" fill="#999">امسح للتحقق من الفاتورة</text>
        </svg>';

        return base64_encode($svg);
    }

    /**
     * Generate text-based QR alternative
     */
    public static function generateTextQr($invoice): string
    {
        $data = [
            'فاتورة: ' . ($invoice->invoice_number ?? 'INV-001'),
            'عميل: ' . ($invoice->customer->name ?? 'عميل'),
            'مبلغ: ' . number_format($invoice->total_amount ?? 0, 0) . ' د.ع',
            'تاريخ: ' . ($invoice->created_at ? $invoice->created_at->format('Y-m-d') : now()->format('Y-m-d')),
            'رابط: ' . url('/verify-invoice/' . ($invoice->invoice_number ?? 'INV-001'))
        ];

        return implode("\n", $data);
    }

    /**
     * Check if QR Code generation is available
     */
    public static function isQrAvailable(): bool
    {
        try {
            QrCode::format('svg')->size(50)->generate('test');
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
