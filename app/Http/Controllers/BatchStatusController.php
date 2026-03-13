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
        $keys = Redis::keys('batch:latest:*');
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
        $validated = $request->validate([
            'system_ids' => ['required', 'string'],
            'payload' => ['required', 'string', 'json'],
        ]);

        $systemKeys = Redis::keys('batch:latest:systems:*');
        $prefix = config('database.redis.options.prefix');

        foreach ($systemKeys as $fullKey) {
            $key = $fullKey;

            if ($prefix && str_starts_with($fullKey, $prefix)) {
                $key = substr($fullKey, strlen($prefix));
            }

            Redis::del($key);
        }

        Redis::del('batch:latest:result');

        $payloadJson = $validated['payload'];


        if($validated['system_ids'] == "*") {
            $ids = null; // This will be used to fetch all synced systems        
        } else {    
            $ids = array_filter(explode(',', $validated['system_ids']));
        }        

        $systems = System::query()
            ->where('is_synced', 1)
            ->when(isset($ids), function ($query) use ($ids) {
                $query->whereIn('id', $ids);
            })      
            ->orderBy('company_name', 'asc') 
            ->limit(10)      
            ->get();

        if ($systems->isEmpty()) {
            return redirect()->back()->with('error', 'No valid systems found for the provided IDs.');
        }

        $jobs = $systems
            ->map(fn (System $system) => new TestQueueJob((int) $system->id, $payloadJson))
            ->all();

        $batch = Bus::batch($jobs)
            ->name('Sync systems batch (Web Trigger)')
            ->then(function (Batch $batch) {
                $total = Redis::get('batch:latest:result');
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