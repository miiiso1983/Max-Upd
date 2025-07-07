<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Inventory\Models\Product;
use App\Modules\Inventory\Models\ProductBatch;
use App\Modules\Inventory\Models\StockLevel;
use App\Modules\Inventory\Models\StockMovement;
use App\Modules\Inventory\Models\ProductBarcode;
use App\Modules\Inventory\Models\Location;
use App\Modules\Inventory\Models\Warehouse;

class AdvancedInventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create locations for existing warehouses
        $warehouses = Warehouse::all();
        
        foreach ($warehouses as $warehouse) {
            // Only create locations if they don't exist
            if ($warehouse->locations()->count() === 0) {
                $this->createLocationsForWarehouse($warehouse);
            }
        }

        // Get existing products
        $products = Product::take(10)->get();
        
        foreach ($products as $product) {
            $this->createAdvancedInventoryData($product);
        }

        $this->command->info('Advanced inventory sample data created successfully!');
        $this->command->info('Created:');
        $this->command->info('- ' . Location::count() . ' warehouse locations');
        $this->command->info('- ' . ProductBatch::count() . ' product batches');
        $this->command->info('- ' . StockLevel::count() . ' stock levels');
        $this->command->info('- ' . ProductBarcode::count() . ' product barcodes');
        $this->command->info('- ' . StockMovement::count() . ' stock movements');
    }

    private function createLocationsForWarehouse($warehouse)
    {
        $locations = [
            [
                'warehouse_id' => $warehouse->id,
                'name' => 'Receiving Area',
                'name_ar' => 'منطقة الاستلام',
                'type' => Location::TYPE_RECEIVING,
                'zone' => 'A' . $warehouse->id,
                'aisle' => '01',
                'capacity' => 1000,
                'is_receivable' => true,
                'is_pickable' => false,
                'priority' => 1,
                'created_by' => 1,
            ],
            [
                'warehouse_id' => $warehouse->id,
                'name' => 'General Storage',
                'name_ar' => 'التخزين العام',
                'type' => Location::TYPE_STORAGE,
                'zone' => 'B' . $warehouse->id,
                'aisle' => '01',
                'rack' => '01',
                'shelf' => '01',
                'capacity' => 500,
                'is_receivable' => true,
                'is_pickable' => true,
                'priority' => 2,
                'created_by' => 1,
            ],
            [
                'warehouse_id' => $warehouse->id,
                'name' => 'Cold Storage',
                'name_ar' => 'التخزين البارد',
                'type' => Location::TYPE_STORAGE,
                'zone' => 'C' . $warehouse->id,
                'aisle' => '01',
                'rack' => '01',
                'shelf' => '01',
                'capacity' => 200,
                'temperature_controlled' => true,
                'temperature_min' => 2.0,
                'temperature_max' => 8.0,
                'is_receivable' => true,
                'is_pickable' => true,
                'priority' => 3,
                'created_by' => 1,
            ],
            [
                'warehouse_id' => $warehouse->id,
                'name' => 'Picking Area',
                'name_ar' => 'منطقة الانتقاء',
                'type' => Location::TYPE_PICKING,
                'zone' => 'D' . $warehouse->id,
                'aisle' => '01',
                'capacity' => 300,
                'is_receivable' => false,
                'is_pickable' => true,
                'priority' => 4,
                'created_by' => 1,
            ],
            [
                'warehouse_id' => $warehouse->id,
                'name' => 'Quarantine Area',
                'name_ar' => 'منطقة الحجر الصحي',
                'type' => Location::TYPE_QUARANTINE,
                'zone' => 'Q' . $warehouse->id,
                'aisle' => '01',
                'capacity' => 100,
                'is_receivable' => true,
                'is_pickable' => false,
                'priority' => 5,
                'created_by' => 1,
            ],
        ];

        foreach ($locations as $locationData) {
            Location::create($locationData);
        }
    }

    private function createAdvancedInventoryData($product)
    {
        $warehouses = Warehouse::all();

        foreach ($warehouses as $warehouse) {
            // Create stock level only if it doesn't exist
            $stockLevel = StockLevel::firstOrCreate(
                [
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouse->id,
                ],
                [
                    'current_stock' => rand(50, 500),
                'reserved_stock' => rand(0, 50),
                'minimum_stock' => rand(10, 50),
                'maximum_stock' => rand(500, 1000),
                'reorder_point' => rand(20, 100),
                'reorder_quantity' => rand(100, 300),
                'average_daily_usage' => rand(1, 10),
                'lead_time_days' => rand(7, 30),
                'safety_stock' => rand(10, 50),
                'last_counted_at' => now()->subDays(rand(1, 30)),
                    'last_movement_at' => now()->subHours(rand(1, 24)),
                    'created_by' => 1,
                ]
            );

            // Create product batches
            $batchCount = rand(2, 5);
            for ($i = 1; $i <= $batchCount; $i++) {
                $manufactureDate = now()->subDays(rand(30, 365));
                $expiryDate = $manufactureDate->copy()->addDays(rand(365, 1095)); // 1-3 years shelf life
                $quantityReceived = rand(50, 200);
                $quantityRemaining = rand(10, $quantityReceived);

                ProductBatch::create([
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouse->id,
                    'batch_number' => 'BATCH-' . $product->id . '-' . $warehouse->id . '-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                    'lot_number' => 'LOT-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
                    'quantity_received' => $quantityReceived,
                    'quantity_remaining' => $quantityRemaining,
                    'quantity_reserved' => rand(0, min(10, $quantityRemaining)),
                    'manufacture_date' => $manufactureDate,
                    'expiry_date' => $expiryDate,
                    'received_date' => $manufactureDate->copy()->addDays(rand(1, 30)),
                    'cost_price' => $product->cost_price ?? rand(1000, 10000),
                    'selling_price' => $product->selling_price ?? rand(1500, 15000),
                    'supplier_id' => 1, // Assuming supplier exists
                    'storage_location' => 'B-01-01-01',
                    'quality_status' => ProductBatch::QUALITY_APPROVED,
                    'created_by' => 1,
                ]);
            }

            // Create stock movements
            $movementCount = rand(5, 15);
            for ($i = 1; $i <= $movementCount; $i++) {
                $movementTypes = [
                    StockMovement::TYPE_IN,
                    StockMovement::TYPE_OUT,
                    StockMovement::TYPE_ADJUSTMENT_IN,
                    StockMovement::TYPE_ADJUSTMENT_OUT,
                ];

                StockMovement::create([
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouse->id,
                    'movement_type' => $movementTypes[array_rand($movementTypes)],
                    'type' => 'in', // Legacy field
                    'quantity' => rand(1, 50),
                    'unit_cost' => $product->cost_price ?? rand(1000, 10000),
                    'reference_type' => 'sample_data',
                    'notes' => 'Sample stock movement for testing',
                    'notes_ar' => 'حركة مخزون عينة للاختبار',
                    'created_by' => 1,
                    'created_at' => now()->subDays(rand(1, 30)),
                ]);
            }
        }

        // Create product barcodes
        $barcodeTypes = [
            ProductBarcode::TYPE_EAN13,
            ProductBarcode::TYPE_CODE128,
            ProductBarcode::TYPE_QR,
        ];

        foreach ($barcodeTypes as $index => $type) {
            // Generate barcode value first
            $barcodeValue = $this->generateSampleBarcode($product->id, $type);

            ProductBarcode::create([
                'product_id' => $product->id,
                'barcode' => $barcodeValue,
                'barcode_type' => $type,
                'is_primary' => $index === 0, // First barcode is primary
                'is_active' => true,
                'unit_of_measure' => $product->unit_of_measure ?? 'piece',
                'quantity_per_unit' => 1,
                'description' => ucfirst($type) . ' barcode for ' . $product->name,
                'description_ar' => 'رمز شريطي ' . $type . ' لـ ' . ($product->name_ar ?? $product->name),
                'created_by' => 1,
            ]);
        }
    }

    private function generateSampleBarcode($productId, $type)
    {
        switch ($type) {
            case ProductBarcode::TYPE_EAN13:
                // Generate EAN-13 barcode for Iraq (629)
                $countryCode = '629';
                $companyCode = '1234';
                $productCode = str_pad($productId, 5, '0', STR_PAD_LEFT);
                $code = $countryCode . $companyCode . $productCode;
                $checkDigit = $this->calculateEAN13CheckDigit($code);
                return $code . $checkDigit;

            case ProductBarcode::TYPE_CODE128:
                return 'PRD' . str_pad($productId, 10, '0', STR_PAD_LEFT);

            case ProductBarcode::TYPE_QR:
                return json_encode([
                    'product_id' => $productId,
                    'type' => 'product',
                    'timestamp' => time(),
                ]);

            default:
                return 'SAMPLE-' . $productId . '-' . time();
        }
    }

    private function calculateEAN13CheckDigit($code)
    {
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += $code[$i] * (($i % 2 === 0) ? 1 : 3);
        }
        return (10 - ($sum % 10)) % 10;
    }
}
