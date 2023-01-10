<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class updateCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'updateCache:cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'updateCache';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): string
    {
        Artisan::call('optimize');
        Artisan::call('route:cache');
        Artisan::call('view:clear');
        return "Кэш очищен.";
    }
}
