<?php

namespace App\Console\Commands;

use App\Components\ImportDateHttpClient;
use Illuminate\Console\Command;

class ImportJsonPlaceholderCommand extends Command
{

    protected $signature = 'UDS:get';

    protected $description = 'get info assortement';

    public function __construct()
    {
        parent::__construct();
    }


    public function handle()
    {
        $service = new ImportDateHttpClient();
        $response = $service->client->request('GET', '');
        $date = json_decode($response->getBody());
       // dd($date);
        $rows = $date->rows;
        dd($rows);

        /*foreach ($date as $rows){
            dd($item);
        }*/

    }
}
