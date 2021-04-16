<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Log;

class FlushCacheFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:flush-cache-file';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Flush Cache File';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Log::info("Cron remove cache started");
        Cache::store("file")->flush();
        Log::info("Cron remove cache successfully");
        $this->info('command:flushcachefile Command Run successfully!');
    }
}
