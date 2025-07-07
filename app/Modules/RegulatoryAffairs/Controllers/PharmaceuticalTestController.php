<?php

namespace App\Modules\RegulatoryAffairs\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\RegulatoryAffairs\Models\PharmaceuticalTest;
use App\Modules\RegulatoryAffairs\Models\PharmaceuticalBatch;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class PharmaceuticalTestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = PharmaceuticalTest::with(['batch.product.company']);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('test_name', 'like', "%{$search}%")
                  ->orWhere('test_type', 'like', "%{$search}%")
                  ->orWhere('test_method', 'like', "%{$search}%")
                  ->orWhere('test_parameter', 'like', "%{$search}%");
            });
        }

        if ($request->filled('batch_id')) {
            $query->where('pharmaceutical_batch_id', $request->get('batch_id'));
        }

        if ($request->filled('test_type')) {
            $query->where('test_type', $request->get('test_type'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->filled('test_result')) {
            $query->where('test_result', $request->get('test_result'));
        }

        $tests = $query->orderBy('test_date', 'desc')->paginate(15);

        // Statistics
        $stats = [
            'total' => PharmaceuticalTest::count(),
            'passed' => PharmaceuticalTest::where('test_result', 'pass')->count(),
            'failed' => PharmaceuticalTest::where('test_result', 'fail')->count(),
            'in_progress' => PharmaceuticalTest::where('test_result', 'pending')->count(),
            'pending' => PharmaceuticalTest::where('test_result', 'pending')->count(),
        ];

        // Get data for filters
        $batches = PharmaceuticalBatch::with('product')->get();
        $testTypes = PharmaceuticalTest::distinct()->pluck('test_type')->filter()->sort()->values();

        return view('regulatory-affairs.tests.index', compact(
            'tests',
            'stats',
            'batches',
            'testTypes'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View
    {
        $batches = PharmaceuticalBatch::with('product')->where('batch_status', 'released')->get();
        $selectedBatch = $request->get('batch_id');

        return view('regulatory-affairs.tests.create', compact('batches', 'selectedBatch'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'pharmaceutical_batch_id' => 'required|exists:pharmaceutical_batches,id',
            'test_name' => 'required|string',
            'test_type' => 'required|string',
            'test_method' => 'required|string',
            'test_parameter' => 'required|string',
            'acceptance_criteria' => 'required|string',
            'test_date' => 'required|date',
            'tested_by' => 'required|string',
            'laboratory' => 'required|string',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['status'] = 'active';
        $validated['test_result'] = 'pending';

        $test = PharmaceuticalTest::create($validated);

        return redirect()
            ->route('regulatory-affairs.tests.show', $test)
            ->with('success', 'تم إنشاء الفحص بنجاح');
    }

    /**
     * Display the specified resource.
     */
    public function show(PharmaceuticalTest $test): View
    {
        $test->load(['batch.product.company', 'creator']);

        return view('regulatory-affairs.tests.show', compact('test'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PharmaceuticalTest $test): View
    {
        $batches = PharmaceuticalBatch::with('product')->where('batch_status', 'released')->get();

        return view('regulatory-affairs.tests.edit', compact('test', 'batches'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PharmaceuticalTest $test): RedirectResponse
    {
        $validated = $request->validate([
            'pharmaceutical_batch_id' => 'required|exists:pharmaceutical_batches,id',
            'test_name' => 'required|string',
            'test_type' => 'required|string',
            'test_method' => 'required|string',
            'test_parameter' => 'required|string',
            'acceptance_criteria' => 'required|string',
            'test_date' => 'required|date',
            'tested_by' => 'required|string',
            'laboratory' => 'required|string',
            'status' => 'nullable|in:active,cancelled,superseded',
            'test_result' => 'nullable|in:pass,fail,pending,retest,out_of_specification',
            'actual_result' => 'nullable|string',
        ]);

        $test->update($validated);

        return redirect()
            ->route('regulatory-affairs.tests.show', $test)
            ->with('success', 'تم تحديث الفحص بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PharmaceuticalTest $test): RedirectResponse
    {
        $test->delete();

        return redirect()
            ->route('regulatory-affairs.tests.index')
            ->with('success', 'تم حذف الفحص بنجاح');
    }
}
