<?php

namespace Database\Seeders;

use App\Modules\Sales\Models\Customer;
use App\Models\User;
use Illuminate\Database\Seeder;

class SalesSeeder extends Seeder
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

        // Create sample customers
        $customers = [
            [
                'name' => 'Al-Noor Pharmacy',
                'name_ar' => 'صيدلية النور',
                'type' => 'pharmacy',
                'code' => 'PHA-000001',
                'email' => 'info@alnoor-pharmacy.com',
                'phone' => '+964-770-111-1111',
                'mobile' => '+964-790-111-1111',
                'address' => 'Al-Mansour District, Street 14, Building 25',
                'city' => 'Baghdad',
                'governorate' => 'Baghdad',
                'contact_person' => 'Ahmed Al-Mansouri',
                'contact_phone' => '+964-790-111-1111',
                'contact_email' => 'ahmed@alnoor-pharmacy.com',
                'credit_limit' => 50000.00,
                'payment_terms' => 30,
                'discount_percentage' => 5.00,
                'created_by' => $superAdmin->id,
            ],
            [
                'name' => 'Babylon Medical Center',
                'name_ar' => 'مركز بابل الطبي',
                'type' => 'clinic',
                'code' => 'CLI-000001',
                'email' => 'admin@babylon-medical.com',
                'phone' => '+964-780-222-2222',
                'mobile' => '+964-790-222-2222',
                'address' => 'Karrada District, Medical Complex',
                'city' => 'Baghdad',
                'governorate' => 'Baghdad',
                'contact_person' => 'Dr. Sara Al-Baghdadi',
                'contact_phone' => '+964-790-222-2222',
                'contact_email' => 'dr.sara@babylon-medical.com',
                'credit_limit' => 100000.00,
                'payment_terms' => 15,
                'discount_percentage' => 10.00,
                'created_by' => $superAdmin->id,
            ],
            [
                'name' => 'Basra General Hospital',
                'name_ar' => 'مستشفى البصرة العام',
                'type' => 'hospital',
                'code' => 'HOS-000001',
                'email' => 'procurement@basra-hospital.gov.iq',
                'phone' => '+964-770-333-3333',
                'address' => 'Medical City, Block A',
                'city' => 'Basra',
                'governorate' => 'Basra',
                'contact_person' => 'Dr. Omar Al-Basri',
                'contact_phone' => '+964-790-333-3333',
                'contact_email' => 'dr.omar@basra-hospital.gov.iq',
                'credit_limit' => 500000.00,
                'payment_terms' => 45,
                'discount_percentage' => 15.00,
                'created_by' => $superAdmin->id,
            ],
            [
                'name' => 'Kurdistan Medical Distribution',
                'name_ar' => 'توزيع كردستان الطبي',
                'type' => 'distributor',
                'code' => 'DIS-000001',
                'email' => 'sales@kurdistan-medical.com',
                'phone' => '+964-750-444-4444',
                'address' => 'Industrial Zone, Warehouse 15',
                'city' => 'Erbil',
                'governorate' => 'Erbil',
                'contact_person' => 'Karwan Ahmed',
                'contact_phone' => '+964-790-444-4444',
                'contact_email' => 'karwan@kurdistan-medical.com',
                'credit_limit' => 200000.00,
                'payment_terms' => 60,
                'discount_percentage' => 20.00,
                'created_by' => $superAdmin->id,
            ],
            [
                'name' => 'Ahmed Al-Khafaji',
                'name_ar' => 'أحمد الخفاجي',
                'type' => 'individual',
                'code' => 'IND-000001',
                'phone' => '+964-770-555-5555',
                'address' => 'Najaf City, Al-Kufa District',
                'city' => 'Najaf',
                'governorate' => 'Najaf',
                'credit_limit' => 5000.00,
                'payment_terms' => 7,
                'discount_percentage' => 0.00,
                'created_by' => $superAdmin->id,
            ],
            [
                'name' => 'Ministry of Health - Anbar',
                'name_ar' => 'وزارة الصحة - الأنبار',
                'type' => 'government',
                'code' => 'GOV-000001',
                'email' => 'health@anbar.gov.iq',
                'phone' => '+964-770-666-6666',
                'address' => 'Government Complex, Health Department',
                'city' => 'Ramadi',
                'governorate' => 'Anbar',
                'contact_person' => 'Dr. Mahmoud Al-Anbari',
                'contact_phone' => '+964-790-666-6666',
                'contact_email' => 'dr.mahmoud@anbar.gov.iq',
                'credit_limit' => 1000000.00,
                'payment_terms' => 90,
                'discount_percentage' => 25.00,
                'created_by' => $superAdmin->id,
            ],
        ];

        foreach ($customers as $customerData) {
            Customer::create($customerData);
        }

        $this->command->info('Sales sample data seeded successfully!');
    }
}
