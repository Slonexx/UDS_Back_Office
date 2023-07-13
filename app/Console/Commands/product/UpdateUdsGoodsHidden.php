<?php

namespace App\Console\Commands\product;

use App\Components\MsClient;
use App\Components\UdsClient;
use App\Http\Controllers\BD\getMainSettingBD;
use App\Http\Controllers\Config\getSettingVendorController;
use App\Services\product\ProductCreateUdsService;
use App\Services\product\ProductUpdateUdsHiddenService;
use App\Services\Settings\SettingsService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Promise\EachPromise;
use GuzzleHttp\Psr7\Response;
use Illuminate\Console\Command;

class UpdateUdsGoodsHidden extends Command
{

    protected $signature = 'udsGood:hidden';

    protected $description = 'udsGood';

    private SettingsService $settingsService;

    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
        parent::__construct();
    }

    public function handle()
    {
        $allSettings = $this->settingsService->getSettings();

        foreach ($allSettings as $settings) {
            $settingBD = new getMainSettingBD($settings->accountId);

            try {
                $ClientCheckMC = new MsClient($settings->TokenMoySklad);
                $body = $ClientCheckMC->get('https://online.moysklad.ru/api/remap/1.2/entity/employee');

                $ClientCheckUDS = new UdsClient($settings->companyId, $settings->TokenUDS);
                $body = $ClientCheckUDS->get('https://api.uds.app/partner/v2/settings');
            } catch (BadResponseException $e) { continue; }

            $data = [ "accountId" => $settings->accountId, ];
            dispatch(function () use ($data) {
                try {
                    app(ProductUpdateUdsHiddenService::class)->insertUpdate($data);
                } catch (BadResponseException){}
            })->onQueue('default');

            // Продолжение выполнения команды
            $this->info('Command executed successfully.');
            /* dd( app(ProductCreateUdsService::class)->insertToUds($data));*/
        }

    }

}
