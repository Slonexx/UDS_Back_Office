<?php

namespace App\Console\Commands;

use App\Http\Controllers\BackEnd\BDController;
use App\Http\Controllers\Config\getSettingVendorController;
use App\Models\counterparty_add;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Promise\EachPromise;
use GuzzleHttp\Promise\Utils;
use GuzzleHttp\Psr7\Response;
use Illuminate\Console\Command;

class AgentsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'agents:start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        //agent add
        $accountSavedSettings = counterparty_add::all();
        $accountIds = [];
        foreach ($accountSavedSettings as $savedSetting){
            $accountIds[] = $savedSetting->accountId;
        }

        if (count($accountIds) == 0) return;

        $client = new Client();
        //Из UDS создание товаров в Моем Складе
        $url = "https://smartuds.kz/api/agentMs";

        $promises = (function () use ($accountIds, $client, $url){
            foreach ($accountIds as $accountId){
                $settings = new getSettingVendorController($accountId);
/*                if (
                    $settings->TokenUDS == null || $settings->companyId == null
                ){
                    continue;
                }*/
                //dd($allSettings);
                yield $client->postAsync($url,[
                    'headers'=> ['Accept' => 'application/json'],
                    'form_params' => [
                        "tokenMs" => $settings->TokenMoySklad,
                        "companyId" => $settings->companyId,
                        "apiKeyUds" => $settings->TokenUDS,
                        "accountId" => $settings->accountId
                    ]
                ]);
            }
        })();

        $eachPromise = new EachPromise($promises,[
            'concurrency' => count($accountIds),
            'fulfilled' => function (Response $response) {
                if ($response->getStatusCode() == 200) {
                    //dd($response);
                } else {
                    //dd($response);
                }
            },
            'rejected' => function ($reason) {
                //dd($reason);
            }
        ]);
        //dd($eachPromise);
        $eachPromise->promise()->wait();

    }
}
