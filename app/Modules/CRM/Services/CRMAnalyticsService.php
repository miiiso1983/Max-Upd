<?php

namespace App\Modules\CRM\Services;

use App\Modules\CRM\Models\Lead;
use App\Modules\CRM\Models\Opportunity;
use App\Modules\CRM\Models\Communication;
use App\Modules\Sales\Models\Customer;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CRMAnalyticsService
{
    /**
     * Generate CRM Dashboard
     */
    public function generateCRMDashboard($startDate = null, $endDate = null)
    {
        $startDate = $startDate ? Carbon::parse($startDate) : now()->startOfMonth();
        $endDate = $endDate ? Carbon::parse($endDate) : now()->endOfMonth();

        return [
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ],
            'lead_metrics' => $this->getLeadMetrics($startDate, $endDate),
            'opportunity_metrics' => $this->getOpportunityMetrics($startDate, $endDate),
            'communication_metrics' => $this->getCommunicationMetrics($startDate, $endDate),
            'sales_funnel' => $this->getSalesFunnel(),
            'performance_metrics' => $this->getPerformanceMetrics($startDate, $endDate),
        ];
    }

    /**
     * Get Lead Metrics
     */
    private function getLeadMetrics($startDate, $endDate)
    {
        $totalLeads = Lead::count();
        $newLeads = Lead::whereBetween('created_at', [$startDate, $endDate])->count();
        $convertedLeads = Lead::converted()
                             ->whereBetween('converted_at', [$startDate, $endDate])
                             ->count();
        $activeLeads = Lead::active()->count();
        $overdueLeads = Lead::overdue()->count();

        // Lead conversion rate
        $conversionRate = $totalLeads > 0 ? ($convertedLeads / $totalLeads) * 100 : 0;

        // Lead sources analysis
        $leadSources = Lead::selectRaw('source, COUNT(*) as count, AVG(probability) as avg_probability')
                          ->groupBy('source')
                          ->get();

        // Lead status distribution
        $statusDistribution = Lead::selectRaw('status, COUNT(*) as count')
                                 ->groupBy('status')
                                 ->pluck('count', 'status');

        return [
            'total_leads' => $totalLeads,
            'new_leads' => $newLeads,
            'converted_leads' => $convertedLeads,
            'active_leads' => $activeLeads,
            'overdue_leads' => $overdueLeads,
            'conversion_rate' => $conversionRate,
            'lead_sources' => $leadSources,
            'status_distribution' => $statusDistribution,
        ];
    }

    /**
     * Get Opportunity Metrics
     */
    private function getOpportunityMetrics($startDate, $endDate)
    {
        $totalOpportunities = Opportunity::count();
        $newOpportunities = Opportunity::whereBetween('created_at', [$startDate, $endDate])->count();
        $wonOpportunities = Opportunity::won()
                                      ->whereBetween('actual_close_date', [$startDate, $endDate])
                                      ->count();
        $lostOpportunities = Opportunity::lost()
                                       ->whereBetween('actual_close_date', [$startDate, $endDate])
                                       ->count();
        $openOpportunities = Opportunity::open()->count();
        $overdueOpportunities = Opportunity::overdue()->count();

        // Pipeline value
        $pipelineValue = Opportunity::open()->sum('amount');
        $weightedPipelineValue = Opportunity::open()->get()->sum('weighted_amount');

        // Win rate
        $closedOpportunities = $wonOpportunities + $lostOpportunities;
        $winRate = $closedOpportunities > 0 ? ($wonOpportunities / $closedOpportunities) * 100 : 0;

        // Average deal size
        $averageDealSize = Opportunity::won()->avg('amount') ?? 0;

        // Stage distribution
        $stageDistribution = Opportunity::selectRaw('stage, COUNT(*) as count, SUM(amount) as total_amount')
                                       ->groupBy('stage')
                                       ->get()
                                       ->keyBy('stage');

        return [
            'total_opportunities' => $totalOpportunities,
            'new_opportunities' => $newOpportunities,
            'won_opportunities' => $wonOpportunities,
            'lost_opportunities' => $lostOpportunities,
            'open_opportunities' => $openOpportunities,
            'overdue_opportunities' => $overdueOpportunities,
            'pipeline_value' => $pipelineValue,
            'weighted_pipeline_value' => $weightedPipelineValue,
            'win_rate' => $winRate,
            'average_deal_size' => $averageDealSize,
            'stage_distribution' => $stageDistribution,
        ];
    }

    /**
     * Get Communication Metrics
     */
    private function getCommunicationMetrics($startDate, $endDate)
    {
        $totalCommunications = Communication::count();
        $newCommunications = Communication::whereBetween('created_at', [$startDate, $endDate])->count();
        $completedCommunications = Communication::completed()
                                                ->whereBetween('completed_at', [$startDate, $endDate])
                                                ->count();
        $pendingCommunications = Communication::pending()->count();
        $overdueCommunications = Communication::overdue()->count();

        // Communication types
        $typeDistribution = Communication::selectRaw('type, COUNT(*) as count')
                                        ->groupBy('type')
                                        ->pluck('count', 'type');

        // Average response time (for inbound communications)
        $averageResponseTime = Communication::inbound()
                                           ->whereNotNull('completed_at')
                                           ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, completed_at)) as avg_hours')
                                           ->first()
                                           ->avg_hours ?? 0;

        return [
            'total_communications' => $totalCommunications,
            'new_communications' => $newCommunications,
            'completed_communications' => $completedCommunications,
            'pending_communications' => $pendingCommunications,
            'overdue_communications' => $overdueCommunications,
            'type_distribution' => $typeDistribution,
            'average_response_time_hours' => $averageResponseTime,
        ];
    }

    /**
     * Get Sales Funnel
     */
    private function getSalesFunnel()
    {
        $funnel = [];

        // Leads by status (top of funnel)
        $leadStatuses = [
            Lead::STATUS_NEW,
            Lead::STATUS_CONTACTED,
            Lead::STATUS_QUALIFIED,
            Lead::STATUS_PROPOSAL,
            Lead::STATUS_NEGOTIATION,
        ];

        foreach ($leadStatuses as $status) {
            $count = Lead::byStatus($status)->count();
            $funnel[] = [
                'stage' => 'lead_' . $status,
                'stage_label' => ucfirst(str_replace('_', ' ', $status)),
                'stage_label_ar' => $this->getLeadStatusLabelAr($status),
                'count' => $count,
                'type' => 'lead',
            ];
        }

        // Opportunities by stage (bottom of funnel)
        $opportunityStages = [
            Opportunity::STAGE_PROSPECTING,
            Opportunity::STAGE_QUALIFICATION,
            Opportunity::STAGE_NEEDS_ANALYSIS,
            Opportunity::STAGE_PROPOSAL,
            Opportunity::STAGE_NEGOTIATION,
            Opportunity::STAGE_CLOSED_WON,
        ];

        foreach ($opportunityStages as $stage) {
            $opportunities = Opportunity::byStage($stage)->get();
            $funnel[] = [
                'stage' => 'opportunity_' . $stage,
                'stage_label' => ucfirst(str_replace('_', ' ', $stage)),
                'stage_label_ar' => $this->getOpportunityStageLabelsAr($stage),
                'count' => $opportunities->count(),
                'total_amount' => $opportunities->sum('amount'),
                'type' => 'opportunity',
            ];
        }

        return $funnel;
    }

    /**
     * Get Performance Metrics
     */
    private function getPerformanceMetrics($startDate, $endDate)
    {
        // Lead response time
        $leadResponseTime = Lead::whereBetween('created_at', [$startDate, $endDate])
                               ->whereNotNull('last_contact_date')
                               ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, last_contact_date)) as avg_hours')
                               ->first()
                               ->avg_hours ?? 0;

        // Opportunity cycle time
        $opportunityCycleTime = Opportunity::won()
                                          ->whereBetween('actual_close_date', [$startDate, $endDate])
                                          ->selectRaw('AVG(TIMESTAMPDIFF(DAY, created_at, actual_close_date)) as avg_days')
                                          ->first()
                                          ->avg_days ?? 0;

        // Activity metrics
        $totalActivities = DB::table('lead_activities')
                            ->whereBetween('created_at', [$startDate, $endDate])
                            ->count() +
                          DB::table('opportunity_activities')
                            ->whereBetween('created_at', [$startDate, $endDate])
                            ->count();

        return [
            'lead_response_time_hours' => $leadResponseTime,
            'opportunity_cycle_time_days' => $opportunityCycleTime,
            'total_activities' => $totalActivities,
            'activities_per_day' => $startDate->diffInDays($endDate) > 0 ? 
                $totalActivities / $startDate->diffInDays($endDate) : 0,
        ];
    }

    /**
     * Generate Lead Source Analysis
     */
    public function generateLeadSourceAnalysis($startDate = null, $endDate = null)
    {
        $startDate = $startDate ? Carbon::parse($startDate) : now()->startOfYear();
        $endDate = $endDate ? Carbon::parse($endDate) : now();

        $sourceAnalysis = Lead::selectRaw('
                source,
                COUNT(*) as total_leads,
                COUNT(CASE WHEN status = ? THEN 1 END) as converted_leads,
                AVG(estimated_value) as avg_estimated_value,
                SUM(estimated_value) as total_estimated_value
            ', [Lead::STATUS_CONVERTED])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('source')
            ->get()
            ->map(function ($item) {
                $item->conversion_rate = $item->total_leads > 0 ? 
                    ($item->converted_leads / $item->total_leads) * 100 : 0;
                return $item;
            });

        return [
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ],
            'source_analysis' => $sourceAnalysis,
            'best_performing_source' => $sourceAnalysis->sortByDesc('conversion_rate')->first(),
            'highest_value_source' => $sourceAnalysis->sortByDesc('total_estimated_value')->first(),
        ];
    }

    /**
     * Generate Sales Pipeline Report
     */
    public function generateSalesPipelineReport()
    {
        $pipeline = [];
        $stages = [
            Opportunity::STAGE_PROSPECTING,
            Opportunity::STAGE_QUALIFICATION,
            Opportunity::STAGE_NEEDS_ANALYSIS,
            Opportunity::STAGE_PROPOSAL,
            Opportunity::STAGE_NEGOTIATION,
        ];

        foreach ($stages as $stage) {
            $opportunities = Opportunity::byStage($stage)
                                       ->with(['customer', 'assignedTo'])
                                       ->get();

            $pipeline[] = [
                'stage' => $stage,
                'stage_label' => ucfirst(str_replace('_', ' ', $stage)),
                'stage_label_ar' => $this->getOpportunityStageLabelsAr($stage),
                'count' => $opportunities->count(),
                'total_amount' => $opportunities->sum('amount'),
                'weighted_amount' => $opportunities->sum('weighted_amount'),
                'average_amount' => $opportunities->avg('amount') ?? 0,
                'opportunities' => $opportunities->map(function ($opp) {
                    return [
                        'id' => $opp->id,
                        'name' => $opp->name,
                        'customer_name' => $opp->customer->name ?? 'No Customer',
                        'amount' => $opp->amount,
                        'probability' => $opp->probability,
                        'weighted_amount' => $opp->weighted_amount,
                        'expected_close_date' => $opp->expected_close_date,
                        'assigned_to' => $opp->assignedTo->name ?? 'Unassigned',
                        'is_overdue' => $opp->is_overdue,
                    ];
                }),
            ];
        }

        return [
            'pipeline' => $pipeline,
            'total_pipeline_value' => collect($pipeline)->sum('total_amount'),
            'total_weighted_value' => collect($pipeline)->sum('weighted_amount'),
            'total_opportunities' => collect($pipeline)->sum('count'),
        ];
    }

    /**
     * Helper methods for Arabic labels
     */
    private function getLeadStatusLabelAr($status)
    {
        $labels = [
            Lead::STATUS_NEW => 'جديد',
            Lead::STATUS_CONTACTED => 'تم التواصل',
            Lead::STATUS_QUALIFIED => 'مؤهل',
            Lead::STATUS_PROPOSAL => 'تم إرسال العرض',
            Lead::STATUS_NEGOTIATION => 'في التفاوض',
        ];

        return $labels[$status] ?? 'غير معروف';
    }

    private function getOpportunityStageLabelsAr($stage)
    {
        $labels = [
            Opportunity::STAGE_PROSPECTING => 'البحث عن العملاء',
            Opportunity::STAGE_QUALIFICATION => 'التأهيل',
            Opportunity::STAGE_NEEDS_ANALYSIS => 'تحليل الاحتياجات',
            Opportunity::STAGE_PROPOSAL => 'العرض',
            Opportunity::STAGE_NEGOTIATION => 'التفاوض',
            Opportunity::STAGE_CLOSED_WON => 'مغلق - فوز',
        ];

        return $labels[$stage] ?? 'غير معروف';
    }
}
