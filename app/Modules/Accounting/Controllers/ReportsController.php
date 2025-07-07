<?php

namespace App\Modules\Accounting\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Accounting\Models\Account;
use App\Modules\Accounting\Models\Transaction;
use App\Modules\Accounting\Models\JournalEntry;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportsController extends Controller
{
    /**
     * Display financial reports dashboard
     */
    public function index(Request $request)
    {
        // Get date range from request or default to current month
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));
        
        // Convert to Carbon instances
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        // Get basic statistics
        $stats = $this->getBasicStats($start, $end);
        
        // Get account balances
        $accountBalances = $this->getAccountBalances();
        
        // Get recent transactions
        $recentTransactions = Transaction::with(['journalEntries.account'])
            ->whereBetween('transaction_date', [$start, $end])
            ->orderBy('transaction_date', 'desc')
            ->limit(10)
            ->get();

        // Return JSON for API requests
        if ($request->expectsJson()) {
            return response()->json([
                'stats' => $stats,
                'account_balances' => $accountBalances,
                'recent_transactions' => $recentTransactions,
                'date_range' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ]
            ]);
        }

        // Return view for web requests
        return view('accounting.reports.index', compact(
            'stats', 
            'accountBalances', 
            'recentTransactions',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Get basic financial statistics
     */
    private function getBasicStats($start, $end)
    {
        $totalTransactions = Transaction::whereBetween('transaction_date', [$start, $end])->count();
        $totalAmount = Transaction::whereBetween('transaction_date', [$start, $end])->sum('total_amount');
        $postedTransactions = Transaction::whereBetween('transaction_date', [$start, $end])
            ->where('status', 'posted')->count();
        $draftTransactions = Transaction::whereBetween('transaction_date', [$start, $end])
            ->where('status', 'draft')->count();

        // Get totals by account type
        $assetTotal = $this->getAccountTypeTotal('asset');
        $liabilityTotal = $this->getAccountTypeTotal('liability');
        $equityTotal = $this->getAccountTypeTotal('equity');
        $revenueTotal = $this->getAccountTypeTotal('revenue', $start, $end);
        $expenseTotal = $this->getAccountTypeTotal('expense', $start, $end);

        return [
            'total_transactions' => $totalTransactions,
            'total_amount' => $totalAmount,
            'posted_transactions' => $postedTransactions,
            'draft_transactions' => $draftTransactions,
            'asset_total' => $assetTotal,
            'liability_total' => $liabilityTotal,
            'equity_total' => $equityTotal,
            'revenue_total' => $revenueTotal,
            'expense_total' => $expenseTotal,
            'net_income' => $revenueTotal - $expenseTotal,
        ];
    }

    /**
     * Get total for account type
     */
    private function getAccountTypeTotal($type, $start = null, $end = null)
    {
        $query = Account::where('type', $type);
        
        if ($start && $end) {
            // For revenue and expense, calculate based on journal entries in date range
            return JournalEntry::whereHas('account', function($q) use ($type) {
                $q->where('type', $type);
            })
            ->whereHas('transaction', function($q) use ($start, $end) {
                $q->whereBetween('transaction_date', [$start, $end]);
            })
            ->sum('amount');
        } else {
            // For assets, liabilities, equity - use current balance
            return $query->sum('current_balance');
        }
    }

    /**
     * Get account balances grouped by type
     */
    private function getAccountBalances()
    {
        return Account::select('type', 'name', 'name_ar', 'code', 'current_balance')
            ->where('status', 'active')
            ->orderBy('type')
            ->orderBy('code')
            ->get()
            ->groupBy('type');
    }

    /**
     * Generate Trial Balance report
     */
    public function trialBalance(Request $request)
    {
        $date = $request->get('date', now()->format('Y-m-d'));
        
        $accounts = Account::with(['journalEntries' => function($query) use ($date) {
            $query->whereHas('transaction', function($q) use ($date) {
                $q->where('transaction_date', '<=', $date)
                  ->where('status', 'posted');
            });
        }])
        ->where('status', 'active')
        ->orderBy('code')
        ->get();

        // Calculate balances
        $trialBalance = [];
        $totalDebits = 0;
        $totalCredits = 0;

        foreach ($accounts as $account) {
            $debits = $account->journalEntries->where('type', 'debit')->sum('amount');
            $credits = $account->journalEntries->where('type', 'credit')->sum('amount');
            
            $balance = $debits - $credits;
            
            if ($balance != 0) {
                $trialBalance[] = [
                    'account' => $account,
                    'debit_balance' => $balance > 0 ? $balance : 0,
                    'credit_balance' => $balance < 0 ? abs($balance) : 0,
                ];
                
                if ($balance > 0) {
                    $totalDebits += $balance;
                } else {
                    $totalCredits += abs($balance);
                }
            }
        }

        if ($request->expectsJson()) {
            return response()->json([
                'trial_balance' => $trialBalance,
                'total_debits' => $totalDebits,
                'total_credits' => $totalCredits,
                'date' => $date
            ]);
        }

        return view('accounting.reports.trial-balance', compact(
            'trialBalance', 
            'totalDebits', 
            'totalCredits', 
            'date'
        ));
    }

    /**
     * Generate Income Statement
     */
    public function incomeStatement(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));
        
        $revenues = $this->getAccountsByType('revenue', $startDate, $endDate);
        $expenses = $this->getAccountsByType('expense', $startDate, $endDate);
        
        $totalRevenue = $revenues->sum('period_total');
        $totalExpenses = $expenses->sum('period_total');
        $netIncome = $totalRevenue - $totalExpenses;

        if ($request->expectsJson()) {
            return response()->json([
                'revenues' => $revenues,
                'expenses' => $expenses,
                'total_revenue' => $totalRevenue,
                'total_expenses' => $totalExpenses,
                'net_income' => $netIncome,
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);
        }

        return view('accounting.reports.income-statement', compact(
            'revenues', 
            'expenses', 
            'totalRevenue', 
            'totalExpenses', 
            'netIncome',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Generate Balance Sheet
     */
    public function balanceSheet(Request $request)
    {
        $date = $request->get('date', now()->format('Y-m-d'));
        
        $assets = $this->getAccountsByType('asset', null, $date);
        $liabilities = $this->getAccountsByType('liability', null, $date);
        $equity = $this->getAccountsByType('equity', null, $date);
        
        $totalAssets = $assets->sum('balance');
        $totalLiabilities = $liabilities->sum('balance');
        $totalEquity = $equity->sum('balance');

        if ($request->expectsJson()) {
            return response()->json([
                'assets' => $assets,
                'liabilities' => $liabilities,
                'equity' => $equity,
                'total_assets' => $totalAssets,
                'total_liabilities' => $totalLiabilities,
                'total_equity' => $totalEquity,
                'date' => $date
            ]);
        }

        return view('accounting.reports.balance-sheet', compact(
            'assets', 
            'liabilities', 
            'equity', 
            'totalAssets', 
            'totalLiabilities', 
            'totalEquity',
            'date'
        ));
    }

    /**
     * Get accounts by type with calculated balances
     */
    private function getAccountsByType($type, $startDate = null, $endDate = null)
    {
        $query = Account::where('type', $type)->where('status', 'active');
        
        return $query->get()->map(function($account) use ($startDate, $endDate) {
            if ($startDate && $endDate) {
                // Calculate period total for revenue/expense accounts
                $periodTotal = JournalEntry::where('account_id', $account->id)
                    ->whereHas('transaction', function($q) use ($startDate, $endDate) {
                        $q->whereBetween('transaction_date', [$startDate, $endDate])
                          ->where('status', 'posted');
                    })
                    ->sum('amount');
                
                $account->period_total = $periodTotal;
            } else {
                // Use current balance for balance sheet accounts
                $account->balance = $account->current_balance;
            }
            
            return $account;
        });
    }
}
