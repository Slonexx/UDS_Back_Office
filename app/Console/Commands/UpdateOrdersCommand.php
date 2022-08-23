<?php

namespace App\Console\Commands;

use App\Http\Controllers\Config\getSettingVendorController;
use App\Services\Settings\SettingsService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;
use GuzzleHttp\Promise\EachPromise;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Promise;

class UpdateOrdersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:update';
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
    /**
     * @throws GuzzleException
     * @throws \Throwable
     */
    public function handle()
    {
        $allSettings = $this->settingsService->getSettings();
        //dd($allSettings);
        $accountIds = [];
        foreach ($allSettings as $setting){
            $accountIds[] = $setting->accountId;
        }
        //dd($accountIds);

        if (count($accountIds) == 0) return;

        $client = new Client();
        $url = "https://smartuds.kz/api/updateOrdersMs";
        //$url = "https://online.moysklad.ru/api/remap/1.2/entity/currency";
        $countFailSettings = 0;
        $promises = (function () use ($accountIds, $client, $url, &$countFailSettings){
                    foreach ($accountIds as $accountId){
                        $settings = new getSettingVendorController($accountId);
                        //dd($settings);
                        if (
                            $settings->TokenUDS == null || $settings->companyId == null
                        ){
                            $countFailSettings++;
                            continue;
                        }
                        //dd($allSettings);
                        yield $client->postAsync($url,[
                            'form_params' => [
                                "tokenMs" => $settings->TokenMoySklad,
                                "companyId" => $settings->companyId,
                                "apiKeyUds" => $settings->TokenUDS,
                                "accountId" => $settings->accountId,
                                "paymentOpt" => $settings->PaymentDocument,
                                "demandOpt" => $settings->Document,
                            ],
                        ]);
                    }
        })();
        //dd($promises);
        //dd(count($accountIds) - $countFailSettings);
        $eachPromise = new EachPromise($promises,[
            'concurrency' => count($accountIds) - $countFailSettings,
            'fulfilled' => function (Response $response) {
                if ($response->getStatusCode() == 200) {
                    //dd($response);
                } else {
                    dd($response);
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
