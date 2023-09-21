<?php

namespace App\Console\Commands\product;

use App\Components\MsClient;
use App\Components\UdsClient;
use App\Http\Controllers\Config\getSettingVendorController;
use App\Http\Controllers\ProductController;
use App\Services\Settings\SettingsService;
use GuzzleHttp\Client;
use GuzzleHttp\Promise\EachPromise;
use GuzzleHttp\Psr7\Response;
use Illuminate\Console\Command;

class UpdateMsCommand extends Command
{
    //Из UDS обновление товаров в Моем Складе

    protected $signature = 'msProduct:update';

    protected $description = 'Command description';

    private SettingsService $settingsService;

    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
        parent::__construct();
    }

    public function checkSettings($accountIds): int
    {
        $countSettings = 0;
        foreach ($accountIds as $accountId){
            $settings = new getSettingVendorController($accountId);
            if ($settings->TokenUDS != null or $settings->companyId != null or $settings->UpdateProduct != "0"){
                try {
                    $clientCheck = new MsClient($settings->TokenMoySklad);
                    $body = $clientCheck->get('https://api.moysklad.ru/api/remap/1.2/entity/employee');
                    $ClientCheckUDS = new UdsClient($settings->companyId, $settings->TokenUDS);
                    $body = $ClientCheckUDS->get('https://api.uds.app/partner/v2/settings');
                    $countSettings++;
                } catch (\Throwable $e) {

                }
            }
        }
        return $countSettings;
    }

    public function handle()
    {
        $allSettings = $this->settingsService->getSettings();
        $accountIds = [];
        foreach ($allSettings as $setting){
            $accountIds[] = $setting->accountId;
        }
        if (count($accountIds) == 0) return;



        $promises = (function () use ($accountIds){
            foreach ($accountIds as $accountId){
                $settings = new getSettingVendorController($accountId);
                if ($settings->TokenUDS == null || $settings->companyId == null || $settings->UpdateProduct == "0"){ continue; }
                try {
                    $ClientCheckMC = new MsClient($settings->TokenMoySklad);
                    $body = $ClientCheckMC->get('https://api.moysklad.ru/api/remap/1.2/entity/employee');

                    $ClientCheckUDS = new UdsClient($settings->companyId, $settings->TokenUDS);
                    $body = $ClientCheckUDS->get('https://api.uds.app/partner/v2/settings');
                } catch (\Throwable $e) { continue; }

                $data = [
                    "tokenMs" => $settings->TokenMoySklad,
                    "companyId" => $settings->companyId,
                    "apiKeyUds" => $settings->TokenUDS,
                    "accountId" => $settings->accountId,
                ];

                try {
                    yield app(ProductController::class)->updateMs_data($data);
                } catch (\Throwable $e) {
                    continue;
                }
            }
        })();

        $eachPromise = new EachPromise($promises,[
            'concurrency' => $this->checkSettings($accountIds),
            'fulfilled' => function (Response $response) {
                if ($response->getStatusCode() == 200) {
                    dd($response->getBody());
                } else {
                    //dd($response);
                }
            },
            'rejected' => function ($reason) {

            }
        ]);

        $eachPromise->promise()->wait();
    }
}
