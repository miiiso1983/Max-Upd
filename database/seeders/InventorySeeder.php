<?php

namespace Database\Seeders;

use App\Modules\Inventory\Models\Category;
use App\Modules\Inventory\Models\Manufacturer;
use App\Modules\Inventory\Models\Warehouse;
use App\Models\User;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdmin = User::where('is_super_admin', true)->first();

        if (!$superAdmin) {
            $this->command->error('Super admin not found. Please run SuperAdminSeeder first.');
            return;
        }

        // Create Categories
        $categories = [
            [
                'name' => 'Antibiotics',
                'name_ar' => 'المضادات الحيوية',
                'description' => 'Antibiotic medications',
                'description_ar' => 'الأدوية المضادة للبكتيريا',
                'code' => 'CAT-ANTIBIOTICS',
                'created_by' => $superAdmin->id,
            ],
            [
                'name' => 'Pain Relief',
                'name_ar' => 'مسكنات الألم',
                'description' => 'Pain relief medications',
                'description_ar' => 'أدوية تسكين الألم',
                'code' => 'CAT-PAINRELIEF',
                'created_by' => $superAdmin->id,
            ],
            [
                'name' => 'Vitamins & Supplements',
                'name_ar' => 'الفيتامينات والمكملات',
                'description' => 'Vitamins and dietary supplements',
                'description_ar' => 'الفيتامينات والمكملات الغذائية',
                'code' => 'CAT-VITAMINS',
                'created_by' => $superAdmin->id,
            ],
            [
                'name' => 'Cardiovascular',
                'name_ar' => 'أدوية القلب والأوعية الدموية',
                'description' => 'Heart and cardiovascular medications',
                'description_ar' => 'أدوية القلب والدورة الدموية',
                'code' => 'CAT-CARDIO',
                'created_by' => $superAdmin->id,
            ],
            [
                'name' => 'Diabetes Care',
                'name_ar' => 'أدوية السكري',
                'description' => 'Diabetes management medications',
                'description_ar' => 'أدوية علاج مرض السكري',
                'code' => 'CAT-DIABETES',
                'created_by' => $superAdmin->id,
            ],
        ];

        foreach ($categories as $categoryData) {
            Category::create($categoryData);
        }

        // Create Manufacturers
        $manufacturers = [
            [
                'name' => 'Pfizer',
                'name_ar' => 'فايزر',
                'code' => 'MFG-PFIZER',
                'country' => 'USA',
                'city' => 'New York',
                'created_by' => $superAdmin->id,
            ],
            [
                'name' => 'Novartis',
                'name_ar' => 'نوفارتيس',
                'code' => 'MFG-NOVARTIS',
                'country' => 'Switzerland',
                'city' => 'Basel',
                'created_by' => $superAdmin->id,
            ],
            [
                'name' => 'Roche',
                'name_ar' => 'روش',
                'code' => 'MFG-ROCHE',
                'country' => 'Switzerland',
                'city' => 'Basel',
                'created_by' => $superAdmin->id,
            ],
            [
                'name' => 'Johnson & Johnson',
                'name_ar' => 'جونسون آند جونسون',
                'code' => 'MFG-JNJ',
                'country' => 'USA',
                'city' => 'New Brunswick',
                'created_by' => $superAdmin->id,
            ],
            [
                'name' => 'Sanofi',
                'name_ar' => 'سانوفي',
                'code' => 'MFG-SANOFI',
                'country' => 'France',
                'city' => 'Paris',
                'created_by' => $superAdmin->id,
            ],
        ];

        foreach ($manufacturers as $manufacturerData) {
            Manufacturer::create($manufacturerData);
        }

        // Create Warehouses
        $warehouses = [
            [
                'name' => 'Main Warehouse',
                'name_ar' => 'المستودع الرئيسي',
                'code' => 'WH-MAIN',
                'description' => 'Main storage facility',
                'description_ar' => 'مرفق التخزين الرئيسي',
                'address' => 'Industrial Zone, Block A',
                'city' => 'Baghdad',
                'governorate' => 'Baghdad',
                'is_main' => true,
                'capacity' => 10000.00,
                'created_by' => $superAdmin->id,
            ],
            [
                'name' => 'Cold Storage',
                'name_ar' => 'التخزين البارد',
                'code' => 'WH-COLD',
                'description' => 'Temperature controlled storage',
                'description_ar' => 'تخزين مُتحكم بدرجة الحرارة',
                'address' => 'Industrial Zone, Block B',
                'city' => 'Baghdad',
                'governorate' => 'Baghdad',
                'temperature_controlled' => true,
                'min_temperature' => 2.0,
                'max_temperature' => 8.0,
                'capacity' => 2000.00,
                'created_by' => $superAdmin->id,
            ],
        ];

        foreach ($warehouses as $warehouseData) {
            Warehouse::create($warehouseData);
        }

        $this->command->info('Inventory base data seeded successfully!');
    }
}
