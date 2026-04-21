<?php

namespace App\Jobs;

use App\Models\System;
use Illuminate\Bus\Batch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

class SyncImportBatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected ?array $systemIds;
    protected ?string $startDate;
    protected ?string $endDate;

    /**
     * Create a new orchestration job instance.
     *
     * @param array|null $systemIds List of IDs to sync, or null for all synced systems.
     * @param string|null $startDate Start date (Y-m-d).
     * @param string|null $endDate End date (Y-m-d).
     */
    public function __construct(?array $systemIds = null, ?string $startDate = null, ?string $endDate = null)
    {
        $this->systemIds = $systemIds;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Fallback to legacy import.php defaults if not provided
        $start = $this->startDate ?? date('Y-m-d', strtotime('-8 months'));
        $end = $this->endDate ?? date('Y-m-d', strtotime('last day of this month'));

        $systems = System::query()
            ->where('company_type', 1)
            ->where('is_active', 1)
            ->where('is_synced', 1)
            ->when($this->systemIds, fn($query) => $query->whereIn('id', $this->systemIds))
            ->orderBy('prefix', 'asc')
            ->limit(200)
            ->get();

        if ($systems->isEmpty()) {
            Log::info('SyncImportBatchJob: No eligible systems found to sync.');
            return;
        }

        // Create job instances for each system
        $jobs = $systems->map(fn($system) => new ImportSystemDataJob(
            'cddb',             // The master connection name from config/database.php
            'systemConnection', // The dynamic connection name
            (int)$system->id, 
            $start, 
            $end
        ))->all();

        Bus::batch($jobs)
            ->name('Sync System Data Batch')
            ->then(fn(Batch $batch) => Log::info("SyncImportBatchJob: Batch {$batch->id} completed successfully."))
            ->catch(fn(Batch $batch, \Throwable $e) => Log::error("SyncImportBatchJob: Batch {$batch->id} failed: " . $e->getMessage()))
            ->dispatch();

        Log::info("SyncImportBatchJob: Dispatched batch for " . count($jobs) . " systems.");
    }
}