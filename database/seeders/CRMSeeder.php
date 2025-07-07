<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\CRM\Models\Lead;
use App\Modules\CRM\Models\Opportunity;
use App\Modules\CRM\Models\Communication;
use App\Modules\CRM\Models\LeadActivity;
use App\Modules\CRM\Models\OpportunityActivity;
use App\Modules\Sales\Models\Customer;

class CRMSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample leads
        $leads = [
            [
                'company_name' => 'Al-Rasheed Pharmacy',
                'company_name_ar' => 'صيدلية الرشيد',
                'contact_person' => 'Ahmed Al-Rasheed',
                'contact_person_ar' => 'أحمد الرشيد',
                'email' => 'ahmed@alrasheed-pharmacy.com',
                'phone' => '+964-1-7123456',
                'mobile' => '+964-770-1234567',
                'address' => 'Al-Karrada District, Baghdad',
                'address_ar' => 'منطقة الكرادة، بغداد',
                'city' => 'Baghdad',
                'city_ar' => 'بغداد',
                'country' => 'Iraq',
                'country_ar' => 'العراق',
                'industry' => 'Pharmacy',
                'industry_ar' => 'صيدلية',
                'source' => Lead::SOURCE_REFERRAL,
                'status' => Lead::STATUS_QUALIFIED,
                'priority' => Lead::PRIORITY_HIGH,
                'estimated_value' => 2500000, // 2.5M IQD
                'probability' => 75,
                'expected_close_date' => now()->addDays(15),
                'description' => 'Large pharmacy chain interested in pharmaceutical supplies',
                'description_ar' => 'سلسلة صيدليات كبيرة مهتمة بالإمدادات الصيدلانية',
                'assigned_to' => 1,
                'created_by' => 1,
            ],
            [
                'company_name' => 'Babylon Medical Center',
                'company_name_ar' => 'مركز بابل الطبي',
                'contact_person' => 'Dr. Fatima Al-Zahra',
                'contact_person_ar' => 'د. فاطمة الزهراء',
                'email' => 'fatima@babylon-medical.com',
                'phone' => '+964-30-5123456',
                'mobile' => '+964-780-2345678',
                'address' => 'Hillah City Center',
                'address_ar' => 'مركز مدينة الحلة',
                'city' => 'Hillah',
                'city_ar' => 'الحلة',
                'country' => 'Iraq',
                'country_ar' => 'العراق',
                'industry' => 'Healthcare',
                'industry_ar' => 'الرعاية الصحية',
                'source' => Lead::SOURCE_WEBSITE,
                'status' => Lead::STATUS_PROPOSAL,
                'priority' => Lead::PRIORITY_URGENT,
                'estimated_value' => 5000000, // 5M IQD
                'probability' => 85,
                'expected_close_date' => now()->addDays(10),
                'description' => 'Medical center requiring comprehensive pharmaceutical supplies',
                'description_ar' => 'مركز طبي يحتاج إلى إمدادات صيدلانية شاملة',
                'assigned_to' => 1,
                'created_by' => 1,
            ],
            [
                'company_name' => 'Najaf Health Clinic',
                'company_name_ar' => 'عيادة النجف الصحية',
                'contact_person' => 'Dr. Hassan Al-Najafi',
                'contact_person_ar' => 'د. حسن النجفي',
                'email' => 'hassan@najaf-clinic.com',
                'phone' => '+964-33-4123456',
                'mobile' => '+964-790-3456789',
                'address' => 'Old City, Najaf',
                'address_ar' => 'المدينة القديمة، النجف',
                'city' => 'Najaf',
                'city_ar' => 'النجف',
                'country' => 'Iraq',
                'country_ar' => 'العراق',
                'industry' => 'Healthcare',
                'industry_ar' => 'الرعاية الصحية',
                'source' => Lead::SOURCE_COLD_CALL,
                'status' => Lead::STATUS_NEW,
                'priority' => Lead::PRIORITY_MEDIUM,
                'estimated_value' => 1500000, // 1.5M IQD
                'probability' => 25,
                'expected_close_date' => now()->addDays(30),
                'description' => 'Small clinic interested in basic pharmaceutical supplies',
                'description_ar' => 'عيادة صغيرة مهتمة بالإمدادات الصيدلانية الأساسية',
                'assigned_to' => 1,
                'created_by' => 1,
            ],
        ];

        foreach ($leads as $leadData) {
            $lead = Lead::create($leadData);
            
            // Add some activities for each lead
            $lead->logActivity(LeadActivity::TYPE_CREATED, "Lead created for {$lead->company_name}");
            
            if ($lead->status !== Lead::STATUS_NEW) {
                $lead->logActivity(LeadActivity::TYPE_CONTACTED, "Initial contact made with {$lead->contact_person}");
            }
            
            if (in_array($lead->status, [Lead::STATUS_QUALIFIED, Lead::STATUS_PROPOSAL])) {
                $lead->logActivity(LeadActivity::TYPE_MEETING_HELD, "Qualification meeting held");
            }
        }

        // Create opportunities from some leads
        $qualifiedLeads = Lead::whereIn('status', [Lead::STATUS_QUALIFIED, Lead::STATUS_PROPOSAL])->get();
        
        foreach ($qualifiedLeads as $lead) {
            $opportunity = Opportunity::create([
                'name' => "Pharmaceutical Supply Contract - {$lead->company_name}",
                'name_ar' => "عقد توريد الأدوية - {$lead->company_name_ar}",
                'description' => "Supply contract opportunity for {$lead->company_name}",
                'description_ar' => "فرصة عقد توريد لـ {$lead->company_name_ar}",
                'lead_id' => $lead->id,
                'stage' => $lead->status === Lead::STATUS_QUALIFIED ? 
                    Opportunity::STAGE_QUALIFICATION : Opportunity::STAGE_PROPOSAL,
                'probability' => $lead->probability,
                'amount' => $lead->estimated_value,
                'currency' => 'IQD',
                'expected_close_date' => $lead->expected_close_date,
                'source' => $lead->source,
                'type' => Opportunity::TYPE_NEW_BUSINESS,
                'priority' => $lead->priority,
                'assigned_to' => $lead->assigned_to,
                'created_by' => $lead->created_by,
            ]);

            // Add opportunity activities
            $opportunity->logActivity(
                OpportunityActivity::TYPE_CREATED, 
                "Opportunity created from lead {$lead->lead_number}"
            );
        }

        // Create some communications
        $communications = [
            [
                'related_type' => Lead::class,
                'related_id' => 1,
                'type' => Communication::TYPE_EMAIL,
                'direction' => Communication::DIRECTION_OUTBOUND,
                'subject' => 'Introduction to MaxCon Pharmaceutical Supplies',
                'subject_ar' => 'مقدمة عن إمدادات ماكس كون الصيدلانية',
                'content' => 'Dear Ahmed, Thank you for your interest in our pharmaceutical supplies...',
                'content_ar' => 'عزيزي أحمد، شكراً لاهتمامك بإمداداتنا الصيدلانية...',
                'from_email' => 'sales@maxcon.com',
                'to_email' => 'ahmed@alrasheed-pharmacy.com',
                'status' => Communication::STATUS_SENT,
                'priority' => Communication::PRIORITY_HIGH,
                'created_by' => 1,
            ],
            [
                'related_type' => Lead::class,
                'related_id' => 2,
                'type' => Communication::TYPE_PHONE,
                'direction' => Communication::DIRECTION_OUTBOUND,
                'subject' => 'Follow-up call regarding pharmaceutical supplies',
                'subject_ar' => 'مكالمة متابعة بخصوص الإمدادات الصيدلانية',
                'content' => 'Called Dr. Fatima to discuss their pharmaceutical supply needs',
                'content_ar' => 'اتصلت بالدكتورة فاطمة لمناقشة احتياجاتهم من الإمدادات الصيدلانية',
                'phone_number' => '+964-780-2345678',
                'duration_minutes' => 25,
                'status' => Communication::STATUS_COMPLETED,
                'priority' => Communication::PRIORITY_URGENT,
                'completed_at' => now()->subHours(2),
                'created_by' => 1,
            ],
            [
                'related_type' => Opportunity::class,
                'related_id' => 1,
                'type' => Communication::TYPE_MEETING,
                'direction' => Communication::DIRECTION_OUTBOUND,
                'subject' => 'Product demonstration meeting',
                'subject_ar' => 'اجتماع عرض المنتجات',
                'content' => 'Scheduled product demonstration meeting with Al-Rasheed Pharmacy',
                'content_ar' => 'اجتماع مجدول لعرض المنتجات مع صيدلية الرشيد',
                'status' => Communication::STATUS_SCHEDULED,
                'priority' => Communication::PRIORITY_HIGH,
                'scheduled_at' => now()->addDays(3),
                'created_by' => 1,
            ],
        ];

        foreach ($communications as $commData) {
            Communication::create($commData);
        }

        $this->command->info('CRM sample data created successfully!');
        $this->command->info('Created:');
        $this->command->info('- ' . Lead::count() . ' leads');
        $this->command->info('- ' . Opportunity::count() . ' opportunities');
        $this->command->info('- ' . Communication::count() . ' communications');
    }
}
