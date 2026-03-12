<?php

namespace App\Http\Controllers;

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
}