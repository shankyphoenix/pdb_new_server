<?php

namespace App\Jobs;

use App\Models\System;
use App\Utilities\CryptoUtility;
use App\Utilities\ResponseParserUtility;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;
use RuntimeException;

class TestQueueJob implements ShouldQueue
{
    use Batchable, Queueable;

    /**
     * The system identifier used to load fetch_url.
     */
    public int $systemId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $systemId)
    {
        $this->systemId = $systemId;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $system = System::query()->find($this->systemId);

        if (! $system) {
            throw new RuntimeException("System not found for id {$this->systemId}.");
        }

        $jobPassword = (string) env('SYSTEM_JOB_ENCRYPT_PASSWORD', '');

        if (trim($jobPassword) === '') {
            throw new RuntimeException('Missing SYSTEM_JOB_ENCRYPT_PASSWORD in .env.');
        }

        $payloadData = [
            'run_update_sql' => [
                'update ~DB_PREFIX~manager set managerNAME=? where managerID = ?',
                ['Last Updated', 14],
            ],
            'run_select_sql' => [
                'select count(1) as count from ~DB_PREFIX~manager',
                [],
            ],
            'system_info' => ['end here'],
        ];

        $payloadJson = json_encode($payloadData);

        if ($payloadJson === false) {
            throw new RuntimeException('Failed to build job payload JSON.');
        }

        // JS btoa(...) equivalent for ASCII payload content.
        $payload = base64_encode($payloadJson);
        $encrypted_payload = CryptoUtility::encryptData($payload, $jobPassword);

        if ($encrypted_payload === '') {
            throw new RuntimeException('Failed to encrypt payload.');
        }

        $url = rtrim($system->system_url, '/').'/run_job_as_worker';

        if (! is_string($url) || trim($url) === '') {
            throw new RuntimeException("Missing fetch_url for system id {$this->systemId}.");
        }

        $response = Http::timeout(180)->get(
            $url,
            ['payload' => $encrypted_payload]
        );

        if ($this->batchId && $response->successful()) {
            $body = $response->json();
            $count = ResponseParserUtility::parseSelectCount($body);

            \Log::info('Count is ........................', [$count]);
            if ($count > 0) {
                Redis::set('batch:lastest:updated_at', date("Y-m-d H:i:s"));
                Redis::set('batch:lastest:batch_id', $this->batchId);

                if (is_numeric($count)) {
                    Redis::incrBy('batch:lastest:result', (int) $count);
                } else {
                    Redis::append('batch:lastest:other_results', (string) $count);
                }

                $latestResponse = json_encode([
                    'system_id' => $this->systemId,
                    'url' => $url,
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'successful' => $response->successful(),
                    'at' => now()->toDateTimeString(),
                ]);

                Redis::set("system:{$this->systemId}:latest_response", $latestResponse);
            }
        }

        if ($response->successful()) {
            \Log::info('TestQueueJob success', [
                'system_id' => $this->systemId,
                'url' => $url,
                'status' => $response->status(),
                'response_body' => $response->body(),
            ]);

            return $response;
        }

        \Log::error('TestQueueJob failed', [
            'system_id' => $this->systemId,
            'url' => $url,
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        throw new RuntimeException("Fetch call failed for system id {$this->systemId}.");
    }
}
