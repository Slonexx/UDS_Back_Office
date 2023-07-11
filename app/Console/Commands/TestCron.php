<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestCron extends Command
{

    protected $signature = 'test:cron';

    protected $description = 'Test cron job';

    public function __construct()
    {
        parent::__construct();
    }


    public function handle(): void
    {
        $this->info('Cron job executed successfully!');
    }
}
