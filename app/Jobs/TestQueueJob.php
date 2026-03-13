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
     * The JSON payload for the job.
     */
    public string $payloadJson;

    /**
     * Create a new job instance.
     */
    public function __construct(int $systemId, string $payloadJson)
    {
        $this->systemId = $systemId;
        $this->payloadJson = $payloadJson;
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

        // JS btoa(...) equivalent for ASCII payload content.
        $payload = base64_encode($this->payloadJson);
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

            
            $date = new \DateTime("now", new \DateTimeZone("Asia/Kolkata"));
            Redis::set('batch:latest:updated_at', $date->format('Y-m-d H:i:s'));
            Redis::set('batch:latest:batch_id', $this->batchId);
            
            $latestResponse = json_encode([
                'system_id' => $this->systemId,
                'system_name' => $system->company_name,
                'url' => $url,
                'status' => $response->status(),
                'body' => $response->body(),
                'successful' => $response->successful(),
                'at' => now()->toDateTimeString(),
            ]);

            Redis::set("batch:latest:systems:{$this->systemId}:response", $latestResponse);
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
