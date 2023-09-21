<?php

namespace App\Console\Commands;

use App\Components\MsClient;
use App\Components\UdsClient;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\BackEnd\BDController;
use App\Http\Controllers\Config\DeleteVendorApiController;
use App\Http\Controllers\Config\getSettingVendorController;
use App\Models\counterparty_add;
use App\Services\Settings\SettingsService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Promise\EachPromise;
use GuzzleHttp\Psr7\Response;
use Illuminate\Console\Command;

class ClearFileCommand extends Command
{

    protected $signature = 'ClearFileCommand:start';

    protected $description = 'ClearFileCommand';

    private SettingsService $settingsService;

    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
        parent::__construct();
    }

    public function handle()
    {
        $allSettings = $this->settingsService->getSettings();

        foreach ($allSettings as $setting){

            try {
                $ClientCheckMC = new MsClient($setting->TokenMoySklad);
                $body = $ClientCheckMC->get('https://api.moysklad.ru/api/remap/1.2/entity/employee');
            } catch (BadResponseException $e) {
                app(DeleteVendorApiController::class)->Delete($setting->accountId);
            }

        }

    }
}
