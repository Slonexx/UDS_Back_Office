<?php

namespace App\Console\Commands\product;

use App\Components\MsClient;
use App\Components\UdsClient;
use App\Services\product\ProductCreateUdsService;
use App\Services\Settings\SettingsService;
use GuzzleHttp\Promise\Each;
use Illuminate\Console\Command;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Generator;

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

        foreach ($allSettings as $settings) {
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
            app(ProductCreateUdsService::class)->insertToUds($data);
        }





        $responses = Http::pool(function (Pool $pool) use ($allSettings) {
            return [
                Each::ofLimit(
                    (function () use ($pool, $allSettings): Generator {
                        foreach ($allSettings as $settings) {
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
                            yield app(ProductCreateUdsService::class)->insertToUds($data);

                        }
                    })(),
                    count($allSettings), // this is the concurrency
                    function (Response $response) { dump($response->effectiveUri()->getPath()); }
                )
            ];
        });
        foreach($responses as $response) { dump((string)$response->effectiveUri()); }

    }

}
