<?php

namespace App\Modules\MedicalReps\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MedicalRepController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display medical reps dashboard
     */
    public function index(Request $request)
    {
        $stats = [
            'total_reps' => 12,
            'active_reps' => 10,
            'territories' => 8,
            'visits_today' => 25,
            'monthly_visits' => 450,
            'commissions_pending' => 15000000, // IQD
        ];

        $recentVisits = collect([
            (object)[
                'id' => 1,
                'rep_name' => 'أحمد محمد',
                'customer_name' => 'صيدلية النور',
                'visit_date' => now()->subHours(2),
                'status' => 'completed',
                'notes' => 'زيارة ناجحة - تم تقديم منتجات جديدة'
            ],
            (object)[
                'id' => 2,
                'rep_name' => 'فاطمة علي',
                'customer_name' => 'مستشفى بغداد',
                'visit_date' => now()->subHours(4),
                'status' => 'completed',
                'notes' => 'مراجعة الطلبات الشهرية'
            ],
        ]);

        $topReps = collect([
            (object)[
                'id' => 1,
                'name' => 'أحمد محمد',
                'territory' => 'بغداد - الكرخ',
                'visits_count' => 45,
                'sales_amount' => 25000000,
                'commission' => 2500000
            ],
            (object)[
                'id' => 2,
                'name' => 'فاطمة علي',
                'territory' => 'بغداد - الرصافة',
                'visits_count' => 38,
                'sales_amount' => 22000000,
                'commission' => 2200000
            ],
        ]);

        // Return JSON for API requests
        if ($request->expectsJson()) {
            return response()->json([
                'stats' => $stats,
                'recent_visits' => $recentVisits,
                'top_reps' => $topReps,
            ]);
        }

        // Return view for web requests
        return view('medical-reps.dashboard', compact('stats', 'recentVisits', 'topReps'));
    }

    /**
     * Display territories
     */
    public function territories(Request $request)
    {
        $territories = collect([
            (object)[
                'id' => 1,
                'name' => 'بغداد - الكرخ',
                'rep_name' => 'أحمد محمد',
                'customers_count' => 25,
                'monthly_target' => 30000000,
                'achieved' => 25000000,
                'achievement_rate' => 83.33
            ],
            (object)[
                'id' => 2,
                'name' => 'بغداد - الرصافة',
                'rep_name' => 'فاطمة علي',
                'customers_count' => 22,
                'monthly_target' => 28000000,
                'achieved' => 22000000,
                'achievement_rate' => 78.57
            ],
        ]);

        if ($request->expectsJson()) {
            return response()->json(['territories' => $territories]);
        }

        return view('medical-reps.territories', compact('territories'));
    }

    /**
     * Display visits
     */
    public function visits(Request $request)
    {
        $visits = collect([
            (object)[
                'id' => 1,
                'rep_name' => 'أحمد محمد',
                'customer_name' => 'صيدلية النور',
                'visit_date' => now()->subDays(1),
                'status' => 'completed',
                'duration' => 45,
                'notes' => 'زيارة ناجحة'
            ],
            (object)[
                'id' => 2,
                'rep_name' => 'فاطمة علي',
                'customer_name' => 'مستشفى بغداد',
                'visit_date' => now()->subDays(2),
                'status' => 'completed',
                'duration' => 60,
                'notes' => 'مراجعة الطلبات'
            ],
        ]);

        if ($request->expectsJson()) {
            return response()->json(['visits' => $visits]);
        }

        return view('medical-reps.visits', compact('visits'));
    }

    /**
     * Display commissions
     */
    public function commissions(Request $request)
    {
        $commissions = collect([
            (object)[
                'id' => 1,
                'rep_name' => 'أحمد محمد',
                'month' => 'ديسمبر 2024',
                'sales_amount' => 25000000,
                'commission_rate' => 10,
                'commission_amount' => 2500000,
                'status' => 'pending',
                'payment_date' => null
            ],
            (object)[
                'id' => 2,
                'rep_name' => 'فاطمة علي',
                'month' => 'ديسمبر 2024',
                'sales_amount' => 22000000,
                'commission_rate' => 10,
                'commission_amount' => 2200000,
                'status' => 'paid',
                'payment_date' => now()->subDays(5)
            ],
        ]);

        if ($request->expectsJson()) {
            return response()->json(['commissions' => $commissions]);
        }

        return view('medical-reps.commissions', compact('commissions'));
    }
}
