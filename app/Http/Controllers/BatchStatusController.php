<?php

namespace App\Http\Controllers;

use App\Jobs\TestQueueJob;
use App\Models\System;
use Illuminate\Bus\Batch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Redis;
use Illuminate\View\View;

class BatchStatusController extends Controller
{
    public function index(): View
    {
        // Fetch all keys matching the pattern from the default Redis connection.
        $keys = Redis::keys('batch:lastest:*');
        $data = [];

        // Get the configured prefix to correctly strip it from the keys returned by Redis::keys().
        // Laravel's Redis facade will re-apply the prefix when we call Redis::get(), so we need the "clean" key.
        $prefix = config('database.redis.options.prefix');

        foreach ($keys as $fullKey) {
            $key = $fullKey;
            
            // If the key returned includes the prefix, strip it.
            if ($prefix && str_starts_with($fullKey, $prefix)) {
                $key = substr($fullKey, strlen($prefix));
            }

            $data[$key] = Redis::get($key);
        }

        ksort($data);

        return view('batch-status', compact('data'));
    }

    public function store(Request $request): RedirectResponse
    {
        $inputIds = $request->input('system_ids', '9054,12407');
        $ids = array_filter(explode(',', $inputIds));

        $systems = System::query()
            ->where('is_synced', 1)
            ->whereIn('id', $ids)
            ->get();

        if ($systems->isEmpty()) {
            return redirect()->back()->with('error', 'No valid systems found for the provided IDs.');
        }

        $jobs = $systems
            ->map(fn (System $system) => new TestQueueJob((int) $system->id))
            ->all();

        $batch = Bus::batch($jobs)
            ->name('Sync systems batch (Web Trigger)')
            ->then(function (Batch $batch) {
                $total = Redis::get('batch:lastest:result');
                \Log::info("BatchStatusController: Batch {$batch->id} finished. Total aggregate count: {$total}");
            })
            ->dispatch();

        return redirect()->back()->with('status', sprintf(
            'Batch %s dispatched with %d jobs.',
            $batch->id,
            count($jobs)
        ));
    }
}