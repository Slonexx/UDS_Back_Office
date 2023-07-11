<?php

namespace App\Console\Commands;

use App\Components\MsClient;
use App\Components\UdsClient;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\BackEnd\BDController;
use App\Http\Controllers\Config\getSettingVendorController;
use App\Models\counterparty_add;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Promise\EachPromise;
use GuzzleHttp\Psr7\Response;
use Illuminate\Console\Command;

class AgentsCommand extends Command
{

    protected $signature = 'agents:start';

    protected $description = 'Command description';

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

                try {
                    $ClientCheckMC = new MsClient($settings->TokenMoySklad);
                    $body = $ClientCheckMC->get('https://online.moysklad.ru/api/remap/1.2/entity/employee');

                    $ClientCheckUDS = new UdsClient($settings->companyId, $settings->TokenUDS);
                    $body = $ClientCheckUDS->get('https://api.uds.app/partner/v2/settings');
                } catch (\Throwable $e) { continue; }

               $data = [
                    "tokenMs" => $settings->TokenMoySklad,
                    "companyId" => $settings->companyId,
                    "apiKeyUds" => $settings->TokenUDS,
                    "accountId" => $settings->accountId
                ];

                try {
                    yield app(AgentController::class)->insert($data);

                } catch (BadResponseException) {
                    continue;
                }
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
                dd($reason);
            }
        ]);
        //dd($eachPromise);
        $eachPromise->promise()->wait();

    }
}
