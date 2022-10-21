<?php

namespace App\Console\Commands\product;

use App\Components\MsClient;
use App\Http\Controllers\Config\getSettingVendorController;
use App\Services\Settings\SettingsService;
use Dflydev\DotAccessData\Data;
use GuzzleHttp\Client;
use GuzzleHttp\Promise\EachPromise;
use GuzzleHttp\Psr7\Response;
use Illuminate\Console\Command;
use function Symfony\Component\Translation\t;

class CreateUdsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'udsProduct:create';

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
            if ($settings->TokenUDS != null || $settings->companyId != null){
                if ($settings->UpdateProduct != "1"){
                    $clientCheck = new MsClient($settings->TokenMoySklad);
                    try {
                        $body = $clientCheck->get('https://online.moysklad.ru/api/remap/1.2/entity/webhook');
                        $countSettings++;
                    } catch (\Throwable $e) {

                    }
                }
            }
        }
        return $countSettings;
    }

    public function handle()
    {
        //dd($this->COUNT_FAILED_SETTINGS);
        $allSettings = $this->settingsService->getSettings();
        //dd($allSettings);
        $accountIds = [];
        foreach ($allSettings as $setting){
            $accountIds[] = $setting->accountId;
        }
        //dd($accountIds);
        if (count($accountIds) == 0) return;

        $client = new Client();
        //Из Моего склада создание товаров в UDS
        $url = "https://smartuds.kz/api/productUds";

        $promises = (function () use ($accountIds, $client, $url){
            foreach ($accountIds as $accountId){
                $settings = new getSettingVendorController($accountId);
                try {
                    $clientCheck = new MsClient($settings->TokenMoySklad);
                    $body = $clientCheck->get('https://online.moysklad.ru/api/remap/1.2/entity/webhook');
                    continue;
                } catch (\Throwable $e) {

                }
                if ($settings->TokenUDS == null || $settings->companyId == null
                    || $settings->UpdateProduct == "1"){ continue; }
                //($settings);
                if ( $settings->ProductFolder == null) $folder_id = '0'; else $folder_id = $settings->ProductFolder;
                try {
                    yield $client->postAsync( $url, [
                        'headers' => ['Accept' => 'application/json'],
                        'form_params' => [
                            "tokenMs" => $settings->TokenMoySklad,
                            "companyId" => $settings->companyId,
                            "apiKeyUds" => $settings->TokenUDS,
                            "folder_id" => $folder_id,
                            "store" => $settings->Store,
                            "accountId" => $settings->accountId,
                        ],
                    ]);
                } catch (\Throwable $e) {
                    continue;
                }
            }
        })();

        $eachPromise = new EachPromise($promises,[
            'concurrency' => $this->checkSettings($accountIds),
            'fulfilled' => function (Response $response) {
                if ($response->getStatusCode() == 200) {
                    //dd($response->getBody()->getContents());
                } else {
                    //dd($response);
                }
                //dd($response->getStatusCode());
            },
            'rejected' => function ($reason) {
                //dd($reason);
            }
        ]);
        //dd($eachPromise);
        $eachPromise->promise()->wait();
    }
}
