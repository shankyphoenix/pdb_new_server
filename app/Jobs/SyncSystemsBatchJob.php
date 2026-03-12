<?php

namespace App\Jobs;

use App\Models\System;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Redis;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Bus\Batch;

class SyncSystemsBatchJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     * Fetches all systems where is_synced = 1 and dispatches TestQueueJob as one Laravel batch.
     */
    public function handle(): void
    {
        $systems = System::query()
            ->where('is_synced', 1)
            ->whereIn('id', [9054, 12407])
            ->get();

        if ($systems->isEmpty()) {
            \Log::info('SyncSystemsBatchJob: No systems to sync.');

            return;
        }

        $jobs = $systems
            ->map(fn (System $system) => new TestQueueJob((int) $system->id))
            ->all();

        $batch = Bus::batch($jobs)
            ->name('Sync systems batch')
            ->then(function (Batch $batch) {
                $total = Redis::get('batch:lastest:result');
                \Log::info("SyncSystemsBatchJob: Batch {$batch->id} finished. Total aggregate count: {$total}");
                // Optional: Clean up the key after processing
                //Redis::del('batch:'.$batch->id.':aggregate');
            })
            ->dispatch();

        \Log::info('SyncSystemsBatchJob: Batch dispatched.', [
            'batch_id' => $batch->id,
            'total_jobs' => count($jobs),
        ]);
    }
}
