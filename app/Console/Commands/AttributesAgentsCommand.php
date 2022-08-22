<?php

namespace App\Console\Commands;

use App\Http\Controllers\BackEnd\BDController;
use App\Http\Controllers\Config\getSettingVendorController;
use App\Models\counterparty_add;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Promise\Utils;
use Illuminate\Console\Command;

class AttributesAgentsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attributes:start';

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
        //set Attributes

        $accountSavedSettings = counterparty_add::all();
        //dd($accountSavedSettings);
        foreach ($accountSavedSettings as $savedSetting){
                $client = new Client(['base_uri' => 'https://smartuds.kz/api/']);
                $settings = new getSettingVendorController($savedSetting->accountId);
                //dd($settings);
                $promises = [
                    'agents' => $client->postAsync('agentMs',[
                        'headers'=> ['Accept' => 'application/json'],
                        'form_params' => [
                            "tokenMs" => $settings->TokenMoySklad,
                            "companyId" => $settings->companyId,
                            "apiKeyUds" => $settings->TokenUDS,
                            "accountId" => $settings->accountId
                        ]
                    ]),
                    'attributes' => $client->postAsync('attributes',[
                        'headers'=> ['Accept' => 'application/json'],
                        'form_params' => [
                            "tokenMs" => $settings->TokenMoySklad,
                            "accountId" => $settings->accountId
                        ]
                    ])
                ];

                try {
                    Utils::unwrap($promises);
                } catch (ClientException $e) {
                    $bd = new BDController();
                    $bd->errorLog($settings->accountId,$e->getMessage());
                }
            }
    }
}
