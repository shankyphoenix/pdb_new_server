<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class ImportSystemDataJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 3600;

    protected string $masterConnection;
    protected string $systemConnection;
    protected int $systemId;
    protected string $startDate;
    protected string $endDate;

    /**
     * Create a new job instance.
     *
     * @param string $masterConnection The name of the master DB connection defined in config/database.php
     * @param string $systemConnection The name of the source system DB connection defined in config/database.php
     * @param int $systemId The ID of the system to sync.
     * @param string $startDate The start date for the sync (Y-m-d).
     * @param string $endDate The end date for the sync (Y-m-d).
     */
    public function __construct(string $masterConnection, string $systemConnection, int $systemId, string $startDate, string $endDate)
    {
        $this->masterConnection = $masterConnection;
        $this->systemConnection = $systemConnection;
        $this->systemId = $systemId;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("ImportSystemDataJob: Starting sync for System ID: {$this->systemId}");

        try {
            $this->configureSystemConnection();
        } catch (\Exception $e) {
            Log::error("ImportSystemDataJob: Failed to configure connection for System ID: {$this->systemId}. Error: " . $e->getMessage());
            return;
        }

        // Example: Syncing Quote History (corresponds to table index 0 in import.php)
        $this->syncQuoteHistory();

        // Additional sync methods (syncLeads, syncQuotes, etc.) from import.php can be ported similarly.

        Log::info("ImportSystemDataJob: Sync completed for System ID: {$this->systemId}");
    }

    /**
     * Dynamically configures the source system database connection using credentials from the database.
     */
    protected function configureSystemConnection(): void
    {
        // Fetch credentials from the master database table 'cddb_company'
        // mapping fields like host, username, and db as used in import.php
        $system = DB::connection($this->masterConnection)
            ->table('cddb_company')
            ->where('id', $this->systemId)
            ->first();

        if (!$system) {
            throw new \RuntimeException("System record not found for ID: {$this->systemId}");
        }

        // Set the database configuration dynamically for the connection name provided
        $config = [
            'driver' => 'mysql',
            'host' => $system->host,
            'port' => $system->port ?? '3306',
            'database' => $system->db,
            'username' => $system->username,
            'password' => $system->password,
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => $system->prefix ?? '', // Laravel Query Builder automatically handles this
            'strict' => false,
        ];

        Config::set("database.connections.{$this->systemConnection}", $config);
        DB::purge($this->systemConnection); // Ensures any existing stale instance is removed
    }

    /**
     * Demonstrates syncing the Quote History table using batch processing.
     */
    protected function syncQuoteHistory(): void
    {
        $source = DB::connection($this->systemConnection);
        $dest = DB::connection($this->masterConnection);

        // 1. Cleanup existing records for the system and period in the master connection
        $dest->table('cddb_sync_quote_history')
            ->where('system_id', $this->systemId)
            ->whereBetween(DB::raw('date(quote_sent_date_time)'), [$this->startDate, $this->endDate])
            ->delete();

        // 2. Fetch data from the system connection using a cursor to save memory
        // Filtering logic based on import.php 'where' clause for cddb_sync_quote_history
        $rows = $source->table('quote_management_history')
            ->whereBetween(DB::raw('date(QuoteSentDateTime)'), [$this->startDate, $this->endDate])
            ->cursor();

        // 3. Batch insert data into the master connection
        $batch = [];
        foreach ($rows as $row) {
            $batch[] = [
                'rid' => $row->RID,
                'uid' => $row->UID,
                'luid' => $row->LUID,
                'quote_number' => $row->QuoteNumber,
                'current_id' => $row->currentID,
                'status' => $row->Status,
                'quote_sent_date_time' => $row->QuoteSentDateTime,
                'system_id' => $this->systemId,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Execute insert in batches of 500 to keep the master connection efficient
            if (count($batch) >= 500) {
                $dest->table('cddb_sync_quote_history')->insert($batch);
                $batch = [];
            }
        }

        if (!empty($batch)) {
            $dest->table('cddb_sync_quote_history')->insert($batch);
        }
    }
}