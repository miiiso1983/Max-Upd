<?php

namespace App\Modules\Accounting\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Accounting\Models\Transaction;
use App\Modules\Accounting\Models\JournalEntry;
use App\Modules\Accounting\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    /**
     * Display a listing of transactions
     */
    public function index(Request $request)
    {
        $query = Transaction::with(['journalEntries.account']);

        // Search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('transaction_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('description_ar', 'like', "%{$search}%");
            });
        }

        // Filter by type
        if ($request->has('type')) {
            $query->byType($request->get('type'));
        }

        // Filter by status
        if ($request->has('status')) {
            $query->byStatus($request->get('status'));
        }

        // Filter by date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->dateRange($request->get('start_date'), $request->get('end_date'));
        } else {
            // Default to current month
            $query->dateRange(now()->startOfMonth(), now()->endOfMonth());
        }

        $transactions = $query->orderBy('transaction_date', 'desc')
                             ->orderBy('created_at', 'desc')
                             ->paginate(20);

        // Add calculated fields
        $transactions->getCollection()->transform(function ($transaction) {
            $transaction->total_debits = $transaction->getTotalDebits();
            $transaction->total_credits = $transaction->getTotalCredits();
            $transaction->is_balanced = $transaction->isBalanced();
            return $transaction;
        });

        $filters = [
            'types' => Transaction::getTypes(),
            'types_ar' => Transaction::getTypesAr(),
            'statuses' => Transaction::getStatuses(),
            'statuses_ar' => Transaction::getStatusesAr(),
        ];

        // Return JSON for API requests
        if ($request->expectsJson()) {
            return response()->json([
                'transactions' => $transactions,
                'filters' => $filters
            ]);
        }

        // Return view for web requests
        return view('accounting.journal-entries.index', compact('transactions', 'filters'));
    }

    /**
     * Show the form for creating a new transaction
     */
    public function create()
    {
        $accounts = Account::active()
                          ->orderBy('code')
                          ->orderBy('name')
                          ->get(['id', 'code', 'name', 'name_ar', 'type']);

        $data = [
            'accounts' => $accounts,
            'types' => Transaction::getTypes(),
            'types_ar' => Transaction::getTypesAr(),
            'currencies' => ['IQD' => 'دينار عراقي', 'USD' => 'دولار أمريكي', 'EUR' => 'يورو'],
        ];

        // Return JSON for API requests
        if (request()->expectsJson()) {
            return response()->json($data);
        }

        // Return view for web requests
        return view('accounting.journal-entries.create', $data);
    }

    /**
     * Store a newly created transaction
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:' . implode(',', array_keys(Transaction::getTypes())),
            'transaction_date' => 'required|date',
            'description' => 'required|string',
            'description_ar' => 'nullable|string',
            'currency' => 'nullable|string|max:3',
            'exchange_rate' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'journal_entries' => 'required|array|min:2',
            'journal_entries.*.account_id' => 'required|exists:accounts,id',
            'journal_entries.*.type' => 'required|in:debit,credit',
            'journal_entries.*.amount' => 'required|numeric|min:0.01',
            'journal_entries.*.description' => 'required|string',
            'journal_entries.*.description_ar' => 'nullable|string',
        ]);

        // Validate that debits equal credits
        $totalDebits = collect($validated['journal_entries'])
            ->where('type', 'debit')
            ->sum('amount');
        
        $totalCredits = collect($validated['journal_entries'])
            ->where('type', 'credit')
            ->sum('amount');

        if (abs($totalDebits - $totalCredits) > 0.01) {
            return response()->json([
                'message' => 'Transaction is not balanced. Total debits must equal total credits.',
                'total_debits' => $totalDebits,
                'total_credits' => $totalCredits,
            ], 422);
        }

        DB::beginTransaction();
        try {
            $validated['created_by'] = auth()->id();
            $validated['total_amount'] = $totalDebits;

            $transaction = Transaction::create($validated);

            // Create journal entries
            foreach ($validated['journal_entries'] as $entryData) {
                $entryData['transaction_id'] = $transaction->id;
                $entryData['created_by'] = auth()->id();
                JournalEntry::create($entryData);
            }

            DB::commit();

            return response()->json([
                'message' => 'Transaction created successfully',
                'transaction' => $transaction->load('journalEntries.account')
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Failed to create transaction',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified transaction
     */
    public function show(Transaction $transaction)
    {
        $transaction->load([
            'journalEntries.account',
            'poster',
            'reverser',
            'creator',
            'updater'
        ]);

        // Add calculated fields
        $transaction->total_debits = $transaction->getTotalDebits();
        $transaction->total_credits = $transaction->getTotalCredits();
        $transaction->is_balanced = $transaction->isBalanced();

        // Return JSON for API requests
        if (request()->expectsJson()) {
            return response()->json([
                'transaction' => $transaction
            ]);
        }

        // Return view for web requests
        return view('accounting.journal-entries.show', compact('transaction'));
    }

    /**
     * Show the form for editing the specified transaction
     */
    public function edit(Transaction $transaction)
    {
        if (!$transaction->canBeEdited()) {
            abort(403, 'Only draft transactions can be edited');
        }

        $transaction->load(['journalEntries.account']);

        $accounts = Account::active()
                          ->orderBy('code')
                          ->orderBy('name')
                          ->get(['id', 'code', 'name', 'name_ar', 'type']);

        $data = [
            'transaction' => $transaction,
            'accounts' => $accounts,
            'types' => Transaction::getTypes(),
            'types_ar' => Transaction::getTypesAr(),
            'currencies' => ['IQD' => 'دينار عراقي', 'USD' => 'دولار أمريكي', 'EUR' => 'يورو'],
        ];

        // Return JSON for API requests
        if (request()->expectsJson()) {
            return response()->json($data);
        }

        // Return view for web requests
        return view('accounting.journal-entries.edit', $data);
    }

    /**
     * Update the specified transaction
     */
    public function update(Request $request, Transaction $transaction)
    {
        if (!$transaction->canBeEdited()) {
            return response()->json([
                'message' => 'Only draft transactions can be edited'
            ], 422);
        }

        $validated = $request->validate([
            'type' => 'required|in:' . implode(',', array_keys(Transaction::getTypes())),
            'transaction_date' => 'required|date',
            'description' => 'required|string',
            'description_ar' => 'nullable|string',
            'currency' => 'nullable|string|max:3',
            'exchange_rate' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'journal_entries' => 'required|array|min:2',
            'journal_entries.*.account_id' => 'required|exists:accounts,id',
            'journal_entries.*.type' => 'required|in:debit,credit',
            'journal_entries.*.amount' => 'required|numeric|min:0.01',
            'journal_entries.*.description' => 'required|string',
            'journal_entries.*.description_ar' => 'nullable|string',
        ]);

        // Validate that debits equal credits
        $totalDebits = collect($validated['journal_entries'])
            ->where('type', 'debit')
            ->sum('amount');
        
        $totalCredits = collect($validated['journal_entries'])
            ->where('type', 'credit')
            ->sum('amount');

        if (abs($totalDebits - $totalCredits) > 0.01) {
            return response()->json([
                'message' => 'Transaction is not balanced. Total debits must equal total credits.',
                'total_debits' => $totalDebits,
                'total_credits' => $totalCredits,
            ], 422);
        }

        DB::beginTransaction();
        try {
            $validated['updated_by'] = auth()->id();
            $validated['total_amount'] = $totalDebits;

            $transaction->update($validated);

            // Delete existing journal entries
            $transaction->journalEntries()->delete();

            // Create new journal entries
            foreach ($validated['journal_entries'] as $entryData) {
                $entryData['transaction_id'] = $transaction->id;
                $entryData['created_by'] = auth()->id();
                JournalEntry::create($entryData);
            }

            DB::commit();

            return response()->json([
                'message' => 'Transaction updated successfully',
                'transaction' => $transaction->fresh()->load('journalEntries.account')
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Failed to update transaction',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified transaction
     */
    public function destroy(Transaction $transaction)
    {
        if (!$transaction->canBeEdited()) {
            return response()->json([
                'message' => 'Only draft transactions can be deleted'
            ], 422);
        }

        $transaction->delete();

        return response()->json([
            'message' => 'Transaction deleted successfully'
        ]);
    }

    /**
     * Post the transaction
     */
    public function post(Transaction $transaction)
    {
        if (!$transaction->canBePosted()) {
            return response()->json([
                'message' => 'Transaction cannot be posted. It must be in draft status and balanced.'
            ], 422);
        }

        try {
            $transaction->post(auth()->id());

            return response()->json([
                'message' => 'Transaction posted successfully',
                'transaction' => $transaction->fresh()->load('journalEntries.account')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to post transaction',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reverse the transaction
     */
    public function reverse(Request $request, Transaction $transaction)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        if (!$transaction->canBeReversed()) {
            return response()->json([
                'message' => 'Only posted transactions can be reversed'
            ], 422);
        }

        try {
            $transaction->reverse($validated['reason'], auth()->id());

            return response()->json([
                'message' => 'Transaction reversed successfully',
                'transaction' => $transaction->fresh()->load('journalEntries.account')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to reverse transaction',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get transaction statistics
     */
    public function statistics(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());

        $stats = [
            'total_transactions' => Transaction::dateRange($startDate, $endDate)->count(),
            'by_status' => Transaction::dateRange($startDate, $endDate)
                                    ->selectRaw('status, COUNT(*) as count')
                                    ->groupBy('status')
                                    ->get(),
            'by_type' => Transaction::dateRange($startDate, $endDate)
                                  ->selectRaw('type, COUNT(*) as count')
                                  ->groupBy('type')
                                  ->get(),
            'total_amount' => Transaction::dateRange($startDate, $endDate)
                                       ->posted()
                                       ->sum('total_amount'),
            'monthly_trend' => $this->getMonthlyTransactionTrend($startDate, $endDate),
        ];

        return response()->json($stats);
    }

    /**
     * Get monthly transaction trend
     */
    private function getMonthlyTransactionTrend($startDate, $endDate)
    {
        return Transaction::selectRaw('YEAR(transaction_date) as year, MONTH(transaction_date) as month, COUNT(*) as count, SUM(total_amount) as total')
                         ->dateRange($startDate, $endDate)
                         ->posted()
                         ->groupByRaw('YEAR(transaction_date), MONTH(transaction_date)')
                         ->orderByRaw('YEAR(transaction_date), MONTH(transaction_date)')
                         ->get();
    }
}
