<?php

namespace App\Modules\Accounting\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Accounting\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    /**
     * Display a listing of accounts
     */
    public function index(Request $request)
    {
        $query = Account::with(['parent', 'children']);

        // Search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        // Filter by type
        if ($request->has('type')) {
            $query->ofType($request->get('type'));
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }

        // Filter by parent account
        if ($request->has('parent_id')) {
            $query->where('parent_id', $request->get('parent_id'));
        }

        // Show only root accounts if requested
        if ($request->get('root_only') === 'true') {
            $query->root();
        }

        $accounts = $query->orderBy('code')->paginate(20);

        // Add calculated fields
        $accounts->getCollection()->transform(function ($account) {
            $account->calculated_balance = $account->calculateBalance();
            return $account;
        });

        $filters = [
            'types' => Account::getTypes(),
            'types_ar' => Account::getTypesAr(),
            'subtypes' => Account::getSubtypes(),
            'subtypes_ar' => Account::getSubtypesAr(),
            'parent_accounts' => Account::active()->root()->get(['id', 'code', 'name', 'name_ar']),
        ];

        // Return JSON for API requests
        if ($request->expectsJson()) {
            return response()->json([
                'accounts' => $accounts,
                'filters' => $filters
            ]);
        }

        // Return view for web requests
        return view('accounting.index', compact('accounts', 'filters'));
    }

    /**
     * Show the form for creating a new account
     */
    public function create(Request $request)
    {
        $parentAccounts = Account::active()->root()->get(['id', 'code', 'name', 'name_ar']);

        $data = [
            'types' => Account::getTypes(),
            'types_ar' => Account::getTypesAr(),
            'subtypes' => Account::getSubtypes(),
            'subtypes_ar' => Account::getSubtypesAr(),
            'parent_accounts' => $parentAccounts,
            'selected_parent_id' => $request->get('parent_id'),
        ];

        // Return JSON for API requests
        if (request()->expectsJson()) {
            return response()->json($data);
        }

        // Return view for web requests
        return view('accounting.create', $data);
    }

    /**
     * Store a newly created account
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'nullable|string|max:20|unique:accounts,code',
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'type' => 'required|in:' . implode(',', array_keys(Account::getTypes())),
            'subtype' => 'nullable|in:' . implode(',', array_keys(Account::getSubtypes())),
            'parent_id' => 'nullable|exists:accounts,id',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'status' => 'nullable|in:active,inactive',
            'opening_balance' => 'nullable|numeric',
            'currency' => 'nullable|string|max:3',
            'tax_account' => 'nullable|boolean',
        ]);

        $validated['created_by'] = auth()->id() ?? 1;

        $account = Account::create($validated);

        return response()->json([
            'message' => 'Account created successfully',
            'account' => $account->load('parent')
        ], 201);
    }

    /**
     * Display the specified account
     */
    public function show(Account $account)
    {
        $account->load([
            'parent',
            'children',
            'journalEntries' => function ($query) {
                $query->with(['transaction'])
                      ->latest()
                      ->take(50);
            }
        ]);

        // Add calculated fields
        $account->calculated_balance = $account->calculateBalance();

        // Get account statistics
        $stats = [
            'total_debits' => $account->debitEntries()->sum('amount'),
            'total_credits' => $account->creditEntries()->sum('amount'),
            'entry_count' => $account->journalEntries()->count(),
            'last_transaction_date' => $account->journalEntries()
                                             ->with('transaction')
                                             ->latest()
                                             ->first()
                                             ?->transaction
                                             ?->transaction_date,
            'balance_by_month' => $this->getMonthlyBalances($account),
        ];

        // Return JSON for API requests
        if (request()->expectsJson()) {
            return response()->json([
                'account' => $account,
                'statistics' => $stats,
            ]);
        }

        // Return view for web requests
        return view('accounting.show', compact('account', 'stats'));
    }

    /**
     * Show the form for editing the specified account
     */
    public function edit(Account $account)
    {
        $account->load('parent');

        $parentAccounts = Account::active()
                                ->where('id', '!=', $account->id)
                                ->whereNotIn('id', $account->descendants()->pluck('id'))
                                ->root()
                                ->get(['id', 'code', 'name', 'name_ar']);

        $data = [
            'account' => $account,
            'types' => Account::getTypes(),
            'types_ar' => Account::getTypesAr(),
            'subtypes' => Account::getSubtypes(),
            'subtypes_ar' => Account::getSubtypesAr(),
            'parent_accounts' => $parentAccounts,
        ];

        // Return JSON for API requests
        if (request()->expectsJson()) {
            return response()->json($data);
        }

        // Return view for web requests
        return view('accounting.edit', $data);
    }

    /**
     * Update the specified account
     */
    public function update(Request $request, Account $account)
    {
        $validated = $request->validate([
            'code' => ['nullable', 'string', 'max:20', Rule::unique('accounts')->ignore($account->id)],
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'type' => 'required|in:' . implode(',', array_keys(Account::getTypes())),
            'subtype' => 'nullable|in:' . implode(',', array_keys(Account::getSubtypes())),
            'parent_id' => [
                'nullable',
                'exists:accounts,id',
                function ($_, $value, $fail) use ($account) {
                    // Prevent circular reference
                    if ($value == $account->id) {
                        $fail('Account cannot be its own parent.');
                    }
                    
                    // Check if the new parent is a descendant
                    if ($value && $account->descendants()->pluck('id')->contains($value)) {
                        $fail('Cannot set a descendant account as parent.');
                    }
                },
            ],
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'status' => 'nullable|in:active,inactive',
            'opening_balance' => 'nullable|numeric',
            'currency' => 'nullable|string|max:3',
            'tax_account' => 'nullable|boolean',
        ]);

        $validated['updated_by'] = auth()->id() ?? 1;

        $account->update($validated);

        return response()->json([
            'message' => 'Account updated successfully',
            'account' => $account->fresh()->load('parent')
        ]);
    }

    /**
     * Remove the specified account
     */
    public function destroy(Account $account)
    {
        // Check if account has journal entries
        if ($account->journalEntries()->exists()) {
            return response()->json([
                'message' => 'Cannot delete account with journal entries'
            ], 422);
        }

        // Check if account has child accounts
        if ($account->children()->exists()) {
            return response()->json([
                'message' => 'Cannot delete account with sub-accounts. Please reassign or delete sub-accounts first.'
            ], 422);
        }

        // Check if it's a system account
        if ($account->is_system_account) {
            return response()->json([
                'message' => 'Cannot delete system account'
            ], 422);
        }

        $account->delete();

        return response()->json([
            'message' => 'Account deleted successfully'
        ]);
    }

    /**
     * Get chart of accounts hierarchy
     */
    public function chartOfAccounts()
    {
        $accounts = Account::with(['children.children.children'])
                          ->root()
                          ->active()
                          ->orderBy('code')
                          ->get();

        $chart = $accounts->map(function ($account) {
            return $this->buildAccountHierarchy($account);
        });

        return response()->json([
            'chart_of_accounts' => $chart
        ]);
    }

    /**
     * Build account hierarchy recursively
     */
    private function buildAccountHierarchy($account)
    {
        return [
            'id' => $account->id,
            'code' => $account->code,
            'name' => $account->name,
            'name_ar' => $account->name_ar,
            'type' => $account->type,
            'subtype' => $account->subtype,
            'current_balance' => $account->current_balance,
            'calculated_balance' => $account->calculateBalance(),
            'is_debit_normal' => $account->isDebitNormal(),
            'children' => $account->children->map(function ($child) {
                return $this->buildAccountHierarchy($child);
            }),
        ];
    }

    /**
     * Get account balance history
     */
    public function balanceHistory(Account $account, Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfYear());
        $endDate = $request->get('end_date', now());

        $balances = [];
        $currentDate = \Carbon\Carbon::parse($startDate)->startOfMonth();
        $endDate = \Carbon\Carbon::parse($endDate);

        while ($currentDate->lte($endDate)) {
            $monthEnd = $currentDate->copy()->endOfMonth();
            $balance = $account->calculateBalance(null, $monthEnd);
            
            $balances[] = [
                'date' => $currentDate->format('Y-m-d'),
                'month' => $currentDate->format('Y-m'),
                'balance' => $balance,
            ];
            
            $currentDate->addMonth();
        }

        return response()->json([
            'account' => $account,
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'balance_history' => $balances,
        ]);
    }

    /**
     * Get monthly balances for statistics
     */
    private function getMonthlyBalances($account)
    {
        $balances = [];
        $startDate = now()->startOfYear();
        $currentDate = $startDate->copy();

        for ($i = 0; $i < 12; $i++) {
            $monthEnd = $currentDate->copy()->endOfMonth();
            $balance = $account->calculateBalance(null, $monthEnd);
            
            $balances[] = [
                'month' => $currentDate->format('M Y'),
                'balance' => $balance,
            ];
            
            $currentDate->addMonth();
        }

        return $balances;
    }

    /**
     * Update account balances
     */
    public function updateBalances()
    {
        $accounts = Account::all();
        
        foreach ($accounts as $account) {
            $account->updateBalance();
        }

        return response()->json([
            'message' => 'Account balances updated successfully',
            'updated_count' => $accounts->count(),
        ]);
    }

    /**
     * Get accounts by type
     */
    public function byType($type)
    {
        $accounts = Account::ofType($type)
                          ->active()
                          ->orderBy('code')
                          ->get();

        return response()->json([
            'type' => $type,
            'accounts' => $accounts,
        ]);
    }

    /**
     * Export accounts to Excel
     */
    public function export()
    {
        // This would implement Excel export using Maatwebsite/Excel
        return response()->json([
            'message' => 'Excel export will be implemented with Maatwebsite/Excel',
            'download_url' => '/api/tenant/accounting/accounts/export'
        ]);
    }

    /**
     * Import accounts from Excel
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv'
        ]);

        // This would implement Excel import using Maatwebsite/Excel
        return response()->json([
            'message' => 'Excel import will be implemented with Maatwebsite/Excel'
        ]);
    }

    /**
     * Generate account report
     */
    public function accountReport(Account $account, Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());

        $account->load([
            'parent',
            'children',
            'journalEntries' => function ($query) use ($startDate, $endDate) {
                $query->with(['transaction'])
                      ->whereHas('transaction', function ($q) use ($startDate, $endDate) {
                          $q->where('status', 'posted')
                            ->whereBetween('transaction_date', [$startDate, $endDate]);
                      })
                      ->orderBy('created_at');
            }
        ]);

        // Calculate balances
        $openingBalance = $account->calculateBalance(null, $startDate);
        $closingBalance = $account->calculateBalance(null, $endDate);
        $periodDebits = $account->debitEntries()
                              ->whereHas('transaction', function ($q) use ($startDate, $endDate) {
                                  $q->whereBetween('transaction_date', [$startDate, $endDate]);
                              })
                              ->sum('amount');
        $periodCredits = $account->creditEntries()
                               ->whereHas('transaction', function ($q) use ($startDate, $endDate) {
                                   $q->whereBetween('transaction_date', [$startDate, $endDate]);
                               })
                               ->sum('amount');

        $reportData = [
            'account' => $account,
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'balances' => [
                'opening_balance' => $openingBalance,
                'closing_balance' => $closingBalance,
                'period_debits' => $periodDebits,
                'period_credits' => $periodCredits,
                'net_change' => $periodDebits - $periodCredits,
            ],
            'transactions' => $account->journalEntries,
        ];

        // Return JSON for API requests
        if (request()->expectsJson()) {
            return response()->json($reportData);
        }

        // Return view for web requests
        return view('accounting.account-report', $reportData);
    }

    /**
     * Get account transactions
     */
    public function accountTransactions(Account $account, Request $request)
    {
        $query = $account->journalEntries()->with(['transaction']);

        // Filter by date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereHas('transaction', function ($q) use ($request) {
                $q->whereBetween('transaction_date', [
                    $request->get('start_date'),
                    $request->get('end_date')
                ]);
            });
        }

        // Filter by transaction type
        if ($request->has('type')) {
            $query->where('type', $request->get('type'));
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(50);

        $data = [
            'account' => $account,
            'transactions' => $transactions,
            'filters' => $request->only(['start_date', 'end_date', 'type']),
        ];

        // Return JSON for API requests
        if (request()->expectsJson()) {
            return response()->json($data);
        }

        // Return view for web requests
        return view('accounting.account-transactions', $data);
    }
}
