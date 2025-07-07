<?php

namespace App\Modules\Analytics\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display analytics dashboard
     */
    public function dashboard(Request $request)
    {
        $stats = [
            'total_revenue' => 125000000, // IQD
            'growth_rate' => 15.5,
            'customer_satisfaction' => 92.3,
            'market_share' => 18.7,
            'predictions_accuracy' => 87.2,
            'ai_recommendations' => 12,
        ];

        $salesTrend = [
            'labels' => ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو'],
            'actual' => [8500000, 9200000, 10100000, 9800000, 11200000, 12500000],
            'predicted' => [8800000, 9500000, 10300000, 10100000, 11500000, 12800000],
        ];

        $customerSegments = [
            ['segment' => 'صيدليات', 'percentage' => 45, 'revenue' => 56250000],
            ['segment' => 'مستشفيات', 'percentage' => 30, 'revenue' => 37500000],
            ['segment' => 'عيادات', 'percentage' => 20, 'revenue' => 25000000],
            ['segment' => 'أخرى', 'percentage' => 5, 'revenue' => 6250000],
        ];

        $aiInsights = [
            [
                'type' => 'opportunity',
                'title' => 'فرصة نمو في قطاع المستشفيات',
                'description' => 'تشير البيانات إلى إمكانية زيادة المبيعات بنسبة 25% في قطاع المستشفيات',
                'confidence' => 89,
                'impact' => 'high'
            ],
            [
                'type' => 'warning',
                'title' => 'انخفاض في رضا العملاء',
                'description' => 'ملاحظة انخفاض طفيف في رضا العملاء في منطقة الكرخ',
                'confidence' => 76,
                'impact' => 'medium'
            ],
            [
                'type' => 'recommendation',
                'title' => 'توصية بزيادة المخزون',
                'description' => 'يُنصح بزيادة مخزون المنتجات الطبية بنسبة 15% للشهر القادم',
                'confidence' => 92,
                'impact' => 'high'
            ],
        ];

        if ($request->expectsJson()) {
            return response()->json([
                'stats' => $stats,
                'sales_trend' => $salesTrend,
                'customer_segments' => $customerSegments,
                'ai_insights' => $aiInsights,
            ]);
        }

        return view('analytics.dashboard', compact('stats', 'salesTrend', 'customerSegments', 'aiInsights'));
    }

    /**
     * Sales prediction
     */
    public function salesPrediction(Request $request)
    {
        $predictions = [
            'next_month' => 13500000,
            'next_quarter' => 42000000,
            'growth_rate' => 18.5,
            'accuracy' => 87,
            'detailed' => [
                'next_month' => [
                    'predicted_revenue' => 13500000,
                    'confidence' => 87,
                    'factors' => [
                        'seasonal_trend' => 'positive',
                        'market_conditions' => 'stable',
                        'historical_pattern' => 'growing'
                    ]
                ],
                'next_quarter' => [
                    'predicted_revenue' => 42000000,
                    'confidence' => 82,
                    'growth_rate' => 18.5
                ],
                'yearly_forecast' => [
                    'predicted_revenue' => 165000000,
                    'confidence' => 78,
                    'growth_rate' => 22.3
                ]
            ]
        ];

        $riskFactors = [
            ['factor' => 'تقلبات السوق', 'impact' => 'medium', 'probability' => 35],
            ['factor' => 'المنافسة', 'impact' => 'high', 'probability' => 25],
            ['factor' => 'التغييرات التنظيمية', 'impact' => 'low', 'probability' => 15],
        ];

        if ($request->expectsJson()) {
            return response()->json([
                'predictions' => $predictions,
                'risk_factors' => $riskFactors,
            ]);
        }

        return view('analytics.sales-prediction', compact('predictions', 'riskFactors'));
    }

    /**
     * Business intelligence
     */
    public function businessIntelligence(Request $request)
    {
        $kpis = [
            'revenue_growth' => ['value' => 15.5, 'trend' => 'up', 'target' => 18.0],
            'customer_retention' => ['value' => 89.2, 'trend' => 'up', 'target' => 90.0],
            'market_penetration' => ['value' => 18.7, 'trend' => 'stable', 'target' => 22.0],
            'operational_efficiency' => ['value' => 76.3, 'trend' => 'up', 'target' => 80.0],
        ];

        $competitorAnalysis = [
            ['competitor' => 'الشركة أ', 'market_share' => 25.3, 'growth_rate' => 12.1],
            ['competitor' => 'الشركة ب', 'market_share' => 22.8, 'growth_rate' => 8.7],
            ['competitor' => 'MaxCon', 'market_share' => 18.7, 'growth_rate' => 15.5],
            ['competitor' => 'الشركة ج', 'market_share' => 16.2, 'growth_rate' => 6.3],
        ];

        $recommendations = [
            [
                'category' => 'المبيعات',
                'title' => 'تحسين استراتيجية التسعير',
                'description' => 'مراجعة أسعار المنتجات الأساسية لزيادة القدرة التنافسية',
                'priority' => 'high',
                'estimated_impact' => '12% زيادة في الإيرادات'
            ],
            [
                'category' => 'العمليات',
                'title' => 'تحسين سلسلة التوريد',
                'description' => 'تقليل أوقات التسليم وتحسين إدارة المخزون',
                'priority' => 'medium',
                'estimated_impact' => '8% تقليل في التكاليف'
            ],
        ];

        if ($request->expectsJson()) {
            return response()->json([
                'kpis' => $kpis,
                'competitor_analysis' => $competitorAnalysis,
                'recommendations' => $recommendations,
            ]);
        }

        return view('analytics.business-intelligence', compact('kpis', 'competitorAnalysis', 'recommendations'));
    }
}
