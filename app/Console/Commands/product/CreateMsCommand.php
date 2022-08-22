<?php

namespace App\Console\Commands\product;

use App\Http\Controllers\Config\getSettingVendorController;
use App\Services\Settings\SettingsService;
use GuzzleHttp\Client;
use GuzzleHttp\Promise\EachPromise;
use GuzzleHttp\Psr7\Response;
use Illuminate\Console\Command;

class CreateMsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'msProduct:create';

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
                $countSettings++;
            }
        }
        return $countSettings;
    }

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
        //Из UDS создание товаров в Моем Складе
        $url = "https://smartuds.kz/api/productMs";

        $promises = (function () use ($accountIds, $client, $url){
            foreach ($accountIds as $accountId){
                $settings = new getSettingVendorController($accountId);
                if (
                    $settings->TokenUDS == null || $settings->companyId == null
                ){
                    continue;
                }
                //dd($allSettings);
                yield $client->postAsync($url,[
                    'form_params' => [
                        "tokenMs" => $settings->TokenMoySklad,
                        "companyId" => $settings->companyId,
                        "apiKeyUds" => $settings->TokenUDS,
                        "folder_id" => $settings->ProductFolder,
                        "accountId" => $settings->accountId,
                    ],
                ]);
            }
        })();

        $eachPromise = new EachPromise($promises,[
            'concurrency' => $this->checkSettings($accountIds),
            'fulfilled' => function (Response $response) {
                if ($response->getStatusCode() == 200) {
                    //dd($response);
                } else {
                    //dd($response);
                }
            },
            'rejected' => function ($reason) {
                //dd($reason);
            }
        ]);
        //dd($eachPromise);
        $eachPromise->promise()->wait();
    }
}
