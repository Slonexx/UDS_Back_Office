<?php

namespace App\Console\Commands;

use App\Components\MsClient;
use App\Components\UdsClient;
use App\Services\order\OrderUpdateMsService;
use App\Services\Settings\SettingsService;
use GuzzleHttp\Pool;
use Illuminate\Console\Command;


class UpdateOrdersCommand extends Command
{

    protected $signature = 'orders:update';

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
                    try {
                        $ClientCheckMC = new MsClient($settings->TokenMoySklad);
                        $body = $ClientCheckMC->get('https://online.moysklad.ru/api/remap/1.2/entity/employee');

                        $ClientCheckUDS = new UdsClient($settings->companyId, $settings->TokenUDS);
                        $body = $ClientCheckUDS->get('https://api.uds.app/partner/v2/settings');
                    } catch (\Throwable $e) { continue; }

                    if ($settings->TokenUDS == null || $settings->companyId == null){ continue; }

                    $data = [
                        "tokenMs" => $settings->TokenMoySklad,
                        "companyId" => $settings->companyId,
                        "apiKeyUds" => $settings->TokenUDS,
                        "accountId" => $settings->accountId,
                        "paymentOpt" => $settings->PaymentDocument,
                        "demandOpt" => $settings->Document,
                    ];

                    yield function() use ($data) {
                        app(OrderUpdateMsService::class)->updateOrdersMs($data);
                    };
                } catch (\Throwable $e) {

                }

            }
        };

        $responses  = Pool::batch($client, $requests($allSettings), ['concurrency' => count($allSettings)]);

    }
}
