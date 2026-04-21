<?php

namespace App\Http\Controllers;

use App\Jobs\SyncImportBatchJob;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ImportBatchController extends Controller
{
    /**
     * Dispatch the bulk import orchestration job.
     * Use this endpoint to trigger a sync for specific systems or all active ones.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'system_ids' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'end_date'   => ['nullable', 'date'],
        ]);

        // Handle comma-separated IDs from the request
        $systemIds = !empty($validated['system_ids']) 
            ? array_filter(explode(',', $validated['system_ids'])) 
            : null;

        // Dispatch the master job that handles the system querying and batching
        SyncImportBatchJob::dispatch(
            $systemIds,
            $validated['start_date'] ?? null,
            $validated['end_date'] ?? null
        );

        return redirect()->back()->with('status', 'Data import batch has been queued.');
    }
}