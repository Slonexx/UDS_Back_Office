<?php

namespace App\Console\Commands\product;

use App\Components\MsClient;
use App\Components\UdsClient;
use App\Http\Controllers\Config\getSettingVendorController;
use App\Http\Controllers\ProductController;
use App\Services\Settings\SettingsService;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Pool;
use Illuminate\Console\Command;


class CreateUdsCommand extends Command
{

    protected $signature = 'udsProduct:create';

    protected $description = 'Command description';

    private SettingsService $settingsService;

    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
        parent::__construct();
    }



    public function handle()
    {

        $client = new \GuzzleHttp\Client();
        $allSettings = $this->settingsService->getSettings();

        $requests = function ($allSettings) {
            foreach ($allSettings as $settings){
                try {
                    $ClientCheckMC = new MsClient($settings->TokenMoySklad);
                    $body = $ClientCheckMC->get('https://online.moysklad.ru/api/remap/1.2/entity/employee');

                    $ClientCheckUDS = new UdsClient($settings->companyId, $settings->TokenUDS);
                    $body = $ClientCheckUDS->get('https://api.uds.app/partner/v2/settings');
                } catch (\Throwable $e) { continue; }

                if ($settings->TokenUDS == null || $settings->companyId == null || $settings->UpdateProduct == "1"){ continue; }
                if ( $settings->ProductFolder == null) $folder_id = '0'; else $folder_id = $settings->ProductFolder;

                $data = [
                    "tokenMs" => $settings->TokenMoySklad,
                    "companyId" => $settings->companyId,
                    "apiKeyUds" => $settings->TokenUDS,
                    "folder_id" => $folder_id,
                    "store" => $settings->Store,
                    "accountId" => $settings->accountId,
                ];

                yield function() use ($data) {
                    try {
                        app(ProductController::class)->insertUds_data($data);
                    } catch (BadResponseException $e){

                    }
                };
            }
        };

        $responses  = Pool::batch($client, $requests($allSettings), ['concurrency' => count($allSettings)]);

        /*$allSettings = $this->settingsService->getSettings();
        $accountIds = [];
        foreach ($allSettings as $setting){
            $accountIds[] = $setting->accountId;
        }
        if (count($accountIds) == 0) return;

        $promises = (function () use ($accountIds){
            foreach ($accountIds as $accountId){

                $settings = new getSettingVendorController($accountId);
                try {
                    $ClientCheckMC = new MsClient($settings->TokenMoySklad);
                    $body = $ClientCheckMC->get('https://online.moysklad.ru/api/remap/1.2/entity/employee');

                    $ClientCheckUDS = new UdsClient($settings->companyId, $settings->TokenUDS);
                    $body = $ClientCheckUDS->get('https://api.uds.app/partner/v2/settings');
                } catch (\Throwable $e) { continue; }

                if ($settings->TokenUDS == null || $settings->companyId == null || $settings->UpdateProduct == "1"){ continue; }
                if ( $settings->ProductFolder == null) $folder_id = '0'; else $folder_id = $settings->ProductFolder;

                $data = [
                    "tokenMs" => $settings->TokenMoySklad,
                    "companyId" => $settings->companyId,
                    "apiKeyUds" => $settings->TokenUDS,
                    "folder_id" => $folder_id,
                    "store" => $settings->Store,
                    "accountId" => $settings->accountId,
                ];

                try {
                    yield app(ProductController::class)->insertUds_data($data);
                } catch (\Throwable $e) {
                    continue;
                }

            }

        })();

        $eachPromise = new EachPromise($promises,[
            'concurrency' => $this->checkSettings($accountIds),
            'fulfilled' => function (Response $response) {
                dd($response);
                //dd($response->getStatusCode());
            },
            'rejected' => function ($reason) {
                //dd($reason);
            }
        ]);
        //dd($eachPromise);
        $eachPromise->promise()->wait();*/
    }


    public function checkSettings($accountIds): int
    {
        $countSettings = 0;
        foreach ($accountIds as $accountId){
            $settings = new getSettingVendorController($accountId);
            if ($settings->TokenUDS != null || $settings->companyId != null){
                if ($settings->UpdateProduct != "1"){
                    $clientCheck = new MsClient($settings->TokenMoySklad);
                    try {
                        $body = $clientCheck->get('https://online.moysklad.ru/api/remap/1.2/entity/employee');
                        $ClientCheckUDS = new UdsClient($settings->companyId, $settings->TokenUDS);
                        $body = $ClientCheckUDS->get('https://api.uds.app/partner/v2/settings');
                        $countSettings++;
                    } catch (\Throwable $e) {

                    }
                }
            }
        }
        return $countSettings;
    }

}
