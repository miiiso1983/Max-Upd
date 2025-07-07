<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SuppliersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create suppliers
        $suppliers = [
            [
                'name' => 'Baghdad Pharmaceuticals',
                'name_ar' => 'شركة بغداد للأدوية',
                'type' => 'manufacturer',
                'code' => 'MAN-000001',
                'email' => 'info@baghdad-pharma.com',
                'phone' => '+964-1-777-8888',
                'mobile' => '+964-770-777-8888',
                'address' => 'Industrial Zone, Al-Waziriya, Baghdad',
                'city' => 'Baghdad',
                'governorate' => 'Baghdad',
                'contact_person' => 'Dr. Ahmed Al-Baghdadi',
                'contact_phone' => '+964-790-777-8888',
                'contact_email' => 'ahmed@baghdad-pharma.com',
                'payment_terms' => 45,
                'credit_limit' => 100000,
                'rating' => 9.2,
                'is_preferred' => true,
                'created_by' => 1,
            ],
            [
                'name' => 'Middle East Medical Supplies',
                'name_ar' => 'شركة الشرق الأوسط للمستلزمات الطبية',
                'type' => 'distributor',
                'code' => 'DIS-000001',
                'email' => 'orders@mems.com',
                'phone' => '+964-1-555-6666',
                'mobile' => '+964-770-555-6666',
                'address' => 'Medical City, Bab Al-Muadham, Baghdad',
                'city' => 'Baghdad',
                'governorate' => 'Baghdad',
                'contact_person' => 'Fatima Al-Zahra',
                'contact_phone' => '+964-790-555-6666',
                'contact_email' => 'fatima@mems.com',
                'payment_terms' => 30,
                'credit_limit' => 75000,
                'rating' => 8.7,
                'is_preferred' => true,
                'created_by' => 1,
            ],
            [
                'name' => 'Kurdistan Medical Import',
                'name_ar' => 'شركة كردستان لاستيراد الأدوية',
                'type' => 'importer',
                'code' => 'IMP-000001',
                'email' => 'import@kmi.com',
                'phone' => '+964-66-123-4567',
                'mobile' => '+964-750-123-4567',
                'address' => 'Erbil Medical Complex, Erbil',
                'city' => 'Erbil',
                'governorate' => 'Erbil',
                'contact_person' => 'Karwan Ahmed',
                'contact_phone' => '+964-750-123-4567',
                'contact_email' => 'karwan@kmi.com',
                'payment_terms' => 60,
                'credit_limit' => 150000,
                'rating' => 8.9,
                'is_preferred' => false,
                'created_by' => 1,
            ],
            [
                'name' => 'Basra Wholesale Pharmacy',
                'name_ar' => 'صيدلية البصرة للجملة',
                'type' => 'wholesaler',
                'code' => 'WHO-000001',
                'email' => 'wholesale@basra-pharmacy.com',
                'phone' => '+964-40-987-6543',
                'mobile' => '+964-780-987-6543',
                'address' => 'Port Area, Basra',
                'city' => 'Basra',
                'governorate' => 'Basra',
                'contact_person' => 'Hassan Al-Basri',
                'contact_phone' => '+964-780-987-6543',
                'contact_email' => 'hassan@basra-pharmacy.com',
                'payment_terms' => 21,
                'credit_limit' => 50000,
                'rating' => 7.8,
                'is_preferred' => false,
                'created_by' => 1,
            ],
            [
                'name' => 'Al-Najaf Local Suppliers',
                'name_ar' => 'موردو النجف المحليون',
                'type' => 'local',
                'code' => 'LOC-000001',
                'email' => 'supply@najaf-local.com',
                'phone' => '+964-33-456-7890',
                'mobile' => '+964-760-456-7890',
                'address' => 'Old City, Najaf',
                'city' => 'Najaf',
                'governorate' => 'Najaf',
                'contact_person' => 'Ali Al-Najafi',
                'contact_phone' => '+964-760-456-7890',
                'contact_email' => 'ali@najaf-local.com',
                'payment_terms' => 14,
                'credit_limit' => 25000,
                'rating' => 7.2,
                'is_preferred' => false,
                'created_by' => 1,
            ],
        ];

        foreach ($suppliers as $supplierData) {
            \App\Modules\Suppliers\Models\Supplier::create($supplierData);
        }

        // Create supplier-product relationships
        $this->createSupplierProducts();
    }

    /**
     * Create supplier-product relationships
     */
    private function createSupplierProducts()
    {
        $products = \App\Modules\Inventory\Models\Product::all();
        $suppliers = \App\Modules\Suppliers\Models\Supplier::all();

        foreach ($products as $product) {
            // Each product will have 2-3 suppliers
            $productSuppliers = $suppliers->random(rand(2, 3));

            foreach ($productSuppliers as $index => $supplier) {
                $isPreferred = $index === 0; // First supplier is preferred
                $unitCost = $product->purchase_price * (1 + ($index * 0.1)); // Increase cost for non-preferred

                \App\Modules\Suppliers\Models\SupplierProduct::create([
                    'supplier_id' => $supplier->id,
                    'product_id' => $product->id,
                    'supplier_sku' => $supplier->code . '-' . $product->sku,
                    'unit_cost' => $unitCost,
                    'minimum_order_quantity' => rand(10, 100),
                    'lead_time_days' => rand(3, 21),
                    'is_preferred' => $isPreferred,
                    'created_by' => 1,
                ]);
            }
        }
    }
}
