<?php

namespace App\Console\Commands\product;

use App\Components\MsClient;
use App\Components\UdsClient;
use App\Http\Controllers\ProductController;
use App\Services\Settings\SettingsService;
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
        $allSettings = $this->settingsService->getSettings();
        foreach ($allSettings as $settings){
            if ($settings->TokenUDS == null or $settings->companyId == null or $settings->UpdateProduct == "1"){ continue; }
            $ClientCheckMC = new MsClient($settings->TokenMoySklad);
            $ClientCheckUDS = new UdsClient($settings->companyId, $settings->TokenUDS);

            try {
                $body = $ClientCheckMC->get('https://online.moysklad.ru/api/remap/1.2/entity/employee');
                $body = $ClientCheckUDS->get('https://api.uds.app/partner/v2/settings');
            } catch (\Throwable $e) {
                continue;
            }


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
                app(ProductController::class)->insertUds_data($data);
            } catch (\Throwable $e) {
                continue;
            }

        }

    }
}
