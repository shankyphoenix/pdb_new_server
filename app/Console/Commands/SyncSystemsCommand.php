<?php

namespace App\Console\Commands;

use App\Jobs\SyncSystemsBatchJob;
use Illuminate\Console\Command;

class SyncSystemsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:systems';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Dispatch TestQueueJob for all systems marked as is_synced=1';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Dispatching SyncSystemsBatchJob...');

        SyncSystemsBatchJob::dispatch();

        $this->info('✅ SyncSystemsBatchJob dispatched to queue.');

        return self::SUCCESS;
    }
}
