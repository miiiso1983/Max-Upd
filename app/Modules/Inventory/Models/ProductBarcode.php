<?php

namespace App\Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class ProductBarcode extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'barcode',
        'barcode_type',
        'is_primary',
        'is_active',
        'unit_of_measure',
        'quantity_per_unit',
        'description',
        'description_ar',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'is_active' => 'boolean',
        'quantity_per_unit' => 'decimal:4',
    ];

    // Barcode type constants
    const TYPE_EAN13 = 'ean13';
    const TYPE_EAN8 = 'ean8';
    const TYPE_UPC = 'upc';
    const TYPE_CODE128 = 'code128';
    const TYPE_CODE39 = 'code39';
    const TYPE_QR = 'qr';
    const TYPE_DATAMATRIX = 'datamatrix';
    const TYPE_CUSTOM = 'custom';

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($barcode) {
            // Ensure only one primary barcode per product
            if ($barcode->is_primary) {
                static::where('product_id', $barcode->product_id)
                      ->where('is_primary', true)
                      ->update(['is_primary' => false]);
            }
        });

        static::updating(function ($barcode) {
            // Ensure only one primary barcode per product
            if ($barcode->is_primary && $barcode->isDirty('is_primary')) {
                static::where('product_id', $barcode->product_id)
                      ->where('id', '!=', $barcode->id)
                      ->where('is_primary', true)
                      ->update(['is_primary' => false]);
            }
        });
    }

    /**
     * Relationships
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('barcode_type', $type);
    }

    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Accessors
     */
    public function getBarcodeTypeLabelAttribute()
    {
        $labels = [
            self::TYPE_EAN13 => 'EAN-13',
            self::TYPE_EAN8 => 'EAN-8',
            self::TYPE_UPC => 'UPC',
            self::TYPE_CODE128 => 'Code 128',
            self::TYPE_CODE39 => 'Code 39',
            self::TYPE_QR => 'QR Code',
            self::TYPE_DATAMATRIX => 'Data Matrix',
            self::TYPE_CUSTOM => 'Custom',
        ];

        return $labels[$this->barcode_type] ?? 'Unknown';
    }

    public function getBarcodeTypeLabelArAttribute()
    {
        $labels = [
            self::TYPE_EAN13 => 'إي إيه إن-13',
            self::TYPE_EAN8 => 'إي إيه إن-8',
            self::TYPE_UPC => 'يو بي سي',
            self::TYPE_CODE128 => 'كود 128',
            self::TYPE_CODE39 => 'كود 39',
            self::TYPE_QR => 'رمز الاستجابة السريعة',
            self::TYPE_DATAMATRIX => 'مصفوفة البيانات',
            self::TYPE_CUSTOM => 'مخصص',
        ];

        return $labels[$this->barcode_type] ?? 'غير معروف';
    }

    /**
     * Methods
     */
    public function generateBarcode($type = self::TYPE_EAN13)
    {
        switch ($type) {
            case self::TYPE_EAN13:
                return $this->generateEAN13();
            case self::TYPE_EAN8:
                return $this->generateEAN8();
            case self::TYPE_UPC:
                return $this->generateUPC();
            case self::TYPE_CODE128:
                return $this->generateCode128();
            case self::TYPE_QR:
                return $this->generateQRCode();
            default:
                return $this->generateCustom();
        }
    }

    private function generateEAN13()
    {
        // Iraq country code: 629
        $countryCode = '629';
        $companyCode = '1234'; // Should be assigned by GS1
        $productCode = str_pad($this->product_id, 5, '0', STR_PAD_LEFT);
        
        $code = $countryCode . $companyCode . $productCode;
        $checkDigit = $this->calculateEAN13CheckDigit($code);
        
        return $code . $checkDigit;
    }

    private function generateEAN8()
    {
        $countryCode = '62'; // Shortened Iraq code
        $productCode = str_pad($this->product_id, 5, '0', STR_PAD_LEFT);
        
        $code = $countryCode . $productCode;
        $checkDigit = $this->calculateEAN8CheckDigit($code);
        
        return $code . $checkDigit;
    }

    private function generateUPC()
    {
        $companyCode = '123456'; // Should be assigned by GS1
        $productCode = str_pad($this->product_id, 5, '0', STR_PAD_LEFT);
        
        $code = $companyCode . $productCode;
        $checkDigit = $this->calculateUPCCheckDigit($code);
        
        return $code . $checkDigit;
    }

    private function generateCode128()
    {
        return 'PRD' . str_pad($this->product_id, 10, '0', STR_PAD_LEFT);
    }

    private function generateQRCode()
    {
        $data = [
            'product_id' => $this->product_id,
            'product_code' => $this->product->product_code ?? '',
            'name' => $this->product->name ?? '',
            'timestamp' => now()->timestamp,
        ];
        
        return json_encode($data);
    }

    private function generateCustom()
    {
        return 'CUSTOM-' . $this->product_id . '-' . time();
    }

    private function calculateEAN13CheckDigit($code)
    {
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += $code[$i] * (($i % 2 === 0) ? 1 : 3);
        }
        return (10 - ($sum % 10)) % 10;
    }

    private function calculateEAN8CheckDigit($code)
    {
        $sum = 0;
        for ($i = 0; $i < 7; $i++) {
            $sum += $code[$i] * (($i % 2 === 0) ? 3 : 1);
        }
        return (10 - ($sum % 10)) % 10;
    }

    private function calculateUPCCheckDigit($code)
    {
        $sum = 0;
        for ($i = 0; $i < 11; $i++) {
            $sum += $code[$i] * (($i % 2 === 0) ? 3 : 1);
        }
        return (10 - ($sum % 10)) % 10;
    }

    public function validateBarcode()
    {
        switch ($this->barcode_type) {
            case self::TYPE_EAN13:
                return $this->validateEAN13();
            case self::TYPE_EAN8:
                return $this->validateEAN8();
            case self::TYPE_UPC:
                return $this->validateUPC();
            default:
                return true; // Custom barcodes are always valid
        }
    }

    private function validateEAN13()
    {
        if (strlen($this->barcode) !== 13) {
            return false;
        }
        
        $code = substr($this->barcode, 0, 12);
        $checkDigit = substr($this->barcode, 12, 1);
        
        return $this->calculateEAN13CheckDigit($code) == $checkDigit;
    }

    private function validateEAN8()
    {
        if (strlen($this->barcode) !== 8) {
            return false;
        }
        
        $code = substr($this->barcode, 0, 7);
        $checkDigit = substr($this->barcode, 7, 1);
        
        return $this->calculateEAN8CheckDigit($code) == $checkDigit;
    }

    private function validateUPC()
    {
        if (strlen($this->barcode) !== 12) {
            return false;
        }
        
        $code = substr($this->barcode, 0, 11);
        $checkDigit = substr($this->barcode, 11, 1);
        
        return $this->calculateUPCCheckDigit($code) == $checkDigit;
    }

    public static function findByBarcode($barcode)
    {
        return static::where('barcode', $barcode)
                    ->where('is_active', true)
                    ->first();
    }

    public static function findProductByBarcode($barcode)
    {
        $productBarcode = static::findByBarcode($barcode);
        
        if ($productBarcode) {
            return $productBarcode->product;
        }
        
        // Also check if barcode is directly on product
        return Product::where('barcode', $barcode)->first();
    }

    public function makePrimary()
    {
        // Remove primary flag from other barcodes
        static::where('product_id', $this->product_id)
              ->where('id', '!=', $this->id)
              ->update(['is_primary' => false]);
        
        // Set this as primary
        $this->update(['is_primary' => true]);
        
        return $this;
    }
}
