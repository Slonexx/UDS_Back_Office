<?php

namespace App\Console\Commands;

use App\Components\MsClient;
use App\Http\Controllers\AttributeController;
use App\Services\Settings\SettingsService;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Console\Command;

class updateCache extends Command
{

    protected $signature = 'updateCache:cache';

    protected $description = 'updateCache';
    private SettingsService $settingsService;

    public function __construct(settingsService $settingsService)
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
                $body = $ClientCheckMC->get('https://api.moysklad.ru/api/remap/1.2/entity/employee');
            } catch (BadResponseException $e) {continue;}

            $data = [
                "tokenMs" => $settings->TokenMoySklad,
                "accountId" => $settings->accountId,
            ];

            dispatch(function () use ($data) {
                app(AttributeController::class)->setAllAttributesOfData($data);
            })->onQueue('default');

            $this->info('Command executed successfully.');
        }


    }

}
