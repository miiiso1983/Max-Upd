<?php

namespace App\Modules\Reports\Services;

use App\Modules\Accounting\Models\Account;
use App\Modules\Accounting\Models\Transaction;
use App\Modules\Accounting\Models\JournalEntry;
use Carbon\Carbon;

class FinancialReportService
{
    /**
     * Generate Balance Sheet
     */
    public function generateBalanceSheet($asOfDate = null)
    {
        $asOfDate = $asOfDate ? Carbon::parse($asOfDate) : now();
        
        // Get all accounts with their balances
        $assets = $this->getAccountsByType('asset', $asOfDate);
        $liabilities = $this->getAccountsByType('liability', $asOfDate);
        $equity = $this->getAccountsByType('equity', $asOfDate);
        
        // Calculate totals
        $totalAssets = $assets->sum('balance');
        $totalLiabilities = $liabilities->sum('balance');
        $totalEquity = $equity->sum('balance');
        
        return [
            'report_type' => 'Balance Sheet',
            'report_type_ar' => 'الميزانية العمومية',
            'as_of_date' => $asOfDate->format('Y-m-d'),
            'currency' => 'IQD',
            'assets' => [
                'current_assets' => $assets->where('subtype', 'current_asset')->values(),
                'non_current_assets' => $assets->where('subtype', 'non_current_asset')->values(),
                'total' => $totalAssets,
            ],
            'liabilities' => [
                'current_liabilities' => $liabilities->where('subtype', 'current_liability')->values(),
                'non_current_liabilities' => $liabilities->where('subtype', 'non_current_liability')->values(),
                'total' => $totalLiabilities,
            ],
            'equity' => [
                'accounts' => $equity->values(),
                'total' => $totalEquity,
            ],
            'totals' => [
                'total_assets' => $totalAssets,
                'total_liabilities_and_equity' => $totalLiabilities + $totalEquity,
                'is_balanced' => abs($totalAssets - ($totalLiabilities + $totalEquity)) < 0.01,
            ],
        ];
    }

    /**
     * Generate Income Statement (Profit & Loss)
     */
    public function generateIncomeStatement($startDate = null, $endDate = null)
    {
        $startDate = $startDate ? Carbon::parse($startDate) : now()->startOfMonth();
        $endDate = $endDate ? Carbon::parse($endDate) : now()->endOfMonth();
        
        // Get revenue and expense accounts with their balances for the period
        $revenues = $this->getAccountsByType('revenue', $endDate, $startDate);
        $expenses = $this->getAccountsByType('expense', $endDate, $startDate);
        
        // Calculate totals
        $totalRevenue = $revenues->sum('balance');
        $totalExpenses = $expenses->sum('balance');
        $netIncome = $totalRevenue - $totalExpenses;
        
        return [
            'report_type' => 'Income Statement',
            'report_type_ar' => 'قائمة الدخل',
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ],
            'currency' => 'IQD',
            'revenue' => [
                'operating_revenue' => $revenues->where('subtype', 'operating_revenue')->values(),
                'non_operating_revenue' => $revenues->where('subtype', 'non_operating_revenue')->values(),
                'total' => $totalRevenue,
            ],
            'expenses' => [
                'operating_expenses' => $expenses->where('subtype', 'operating_expense')->values(),
                'non_operating_expenses' => $expenses->where('subtype', 'non_operating_expense')->values(),
                'total' => $totalExpenses,
            ],
            'net_income' => $netIncome,
            'profit_margin' => $totalRevenue > 0 ? ($netIncome / $totalRevenue) * 100 : 0,
        ];
    }

    /**
     * Generate Cash Flow Statement
     */
    public function generateCashFlowStatement($startDate = null, $endDate = null)
    {
        $startDate = $startDate ? Carbon::parse($startDate) : now()->startOfMonth();
        $endDate = $endDate ? Carbon::parse($endDate) : now()->endOfMonth();
        
        // Get cash accounts
        $cashAccounts = Account::where('type', 'asset')
                              ->where(function ($query) {
                                  $query->where('code', 'like', '11%') // Cash and bank accounts
                                        ->orWhere('name', 'like', '%cash%')
                                        ->orWhere('name', 'like', '%bank%');
                              })
                              ->get();
        
        $cashFlow = [];
        $totalCashFlow = 0;
        
        foreach ($cashAccounts as $account) {
            $startBalance = $account->calculateBalance(null, $startDate->copy()->subDay());
            $endBalance = $account->calculateBalance(null, $endDate);
            $netChange = $endBalance - $startBalance;
            
            $cashFlow[] = [
                'account_id' => $account->id,
                'account_code' => $account->code,
                'account_name' => $account->name,
                'account_name_ar' => $account->name_ar,
                'start_balance' => $startBalance,
                'end_balance' => $endBalance,
                'net_change' => $netChange,
            ];
            
            $totalCashFlow += $netChange;
        }
        
        return [
            'report_type' => 'Cash Flow Statement',
            'report_type_ar' => 'قائمة التدفق النقدي',
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ],
            'currency' => 'IQD',
            'cash_accounts' => $cashFlow,
            'total_cash_flow' => $totalCashFlow,
        ];
    }

    /**
     * Generate Trial Balance
     */
    public function generateTrialBalance($asOfDate = null)
    {
        $asOfDate = $asOfDate ? Carbon::parse($asOfDate) : now();
        
        $accounts = Account::active()
                          ->orderBy('code')
                          ->get()
                          ->map(function ($account) use ($asOfDate) {
                              $balance = $account->calculateBalance(null, $asOfDate);
                              
                              return [
                                  'account_id' => $account->id,
                                  'account_code' => $account->code,
                                  'account_name' => $account->name,
                                  'account_name_ar' => $account->name_ar,
                                  'account_type' => $account->type,
                                  'debit_balance' => $account->isDebitNormal() && $balance > 0 ? $balance : 0,
                                  'credit_balance' => $account->isCreditNormal() && $balance > 0 ? $balance : 0,
                                  'balance' => $balance,
                              ];
                          });
        
        $totalDebits = $accounts->sum('debit_balance');
        $totalCredits = $accounts->sum('credit_balance');
        
        return [
            'report_type' => 'Trial Balance',
            'report_type_ar' => 'ميزان المراجعة',
            'as_of_date' => $asOfDate->format('Y-m-d'),
            'currency' => 'IQD',
            'accounts' => $accounts->values(),
            'totals' => [
                'total_debits' => $totalDebits,
                'total_credits' => $totalCredits,
                'is_balanced' => abs($totalDebits - $totalCredits) < 0.01,
            ],
        ];
    }

    /**
     * Generate General Ledger for specific account
     */
    public function generateGeneralLedger($accountId, $startDate = null, $endDate = null)
    {
        $account = Account::findOrFail($accountId);
        $startDate = $startDate ? Carbon::parse($startDate) : now()->startOfMonth();
        $endDate = $endDate ? Carbon::parse($endDate) : now()->endOfMonth();
        
        // Get opening balance
        $openingBalance = $account->calculateBalance(null, $startDate->copy()->subDay());
        
        // Get journal entries for the period
        $entries = JournalEntry::where('account_id', $accountId)
                              ->whereHas('transaction', function ($query) use ($startDate, $endDate) {
                                  $query->whereBetween('transaction_date', [$startDate, $endDate])
                                        ->where('status', 'posted');
                              })
                              ->with(['transaction'])
                              ->orderBy('created_at')
                              ->get();
        
        $runningBalance = $openingBalance;
        $ledgerEntries = [];
        
        foreach ($entries as $entry) {
            if ($account->isDebitNormal()) {
                $runningBalance += $entry->type === 'debit' ? $entry->amount : -$entry->amount;
            } else {
                $runningBalance += $entry->type === 'credit' ? $entry->amount : -$entry->amount;
            }
            
            $ledgerEntries[] = [
                'date' => $entry->transaction->transaction_date,
                'transaction_number' => $entry->transaction->transaction_number,
                'description' => $entry->description,
                'debit' => $entry->type === 'debit' ? $entry->amount : 0,
                'credit' => $entry->type === 'credit' ? $entry->amount : 0,
                'balance' => $runningBalance,
            ];
        }
        
        return [
            'report_type' => 'General Ledger',
            'report_type_ar' => 'دفتر الأستاذ العام',
            'account' => [
                'id' => $account->id,
                'code' => $account->code,
                'name' => $account->name,
                'name_ar' => $account->name_ar,
                'type' => $account->type,
            ],
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ],
            'opening_balance' => $openingBalance,
            'closing_balance' => $runningBalance,
            'entries' => $ledgerEntries,
            'summary' => [
                'total_debits' => $entries->where('type', 'debit')->sum('amount'),
                'total_credits' => $entries->where('type', 'credit')->sum('amount'),
                'entry_count' => $entries->count(),
            ],
        ];
    }

    /**
     * Get accounts by type with calculated balances
     */
    private function getAccountsByType($type, $asOfDate, $startDate = null)
    {
        return Account::where('type', $type)
                     ->active()
                     ->orderBy('code')
                     ->get()
                     ->map(function ($account) use ($asOfDate, $startDate) {
                         $balance = $account->calculateBalance($startDate, $asOfDate);
                         
                         // For revenue and expense accounts, we want the absolute value
                         if (in_array($account->type, ['revenue', 'expense'])) {
                             $balance = abs($balance);
                         }
                         
                         return [
                             'account_id' => $account->id,
                             'account_code' => $account->code,
                             'account_name' => $account->name,
                             'account_name_ar' => $account->name_ar,
                             'account_type' => $account->type,
                             'account_subtype' => $account->subtype,
                             'balance' => $balance,
                         ];
                     })
                     ->filter(function ($account) {
                         return $account['balance'] != 0; // Only show accounts with balances
                     });
    }
}
