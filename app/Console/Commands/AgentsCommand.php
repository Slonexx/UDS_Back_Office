<?php

namespace App\Console\Commands;

use App\Components\MsClient;
use App\Components\UdsClient;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\BackEnd\BDController;
use App\Http\Controllers\Config\getSettingVendorController;
use App\Models\counterparty_add;
use App\Services\agent\AgentService;
use App\Services\product\ProductCreateUdsService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Promise\EachPromise;
use GuzzleHttp\Psr7\Response;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class AgentsCommand extends Command
{

    protected $signature = 'agents:start';

    protected $description = 'Command description';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): void
    {


        $mutex = Cache::lock('process_Agents', 6000); // Задаем мьютекс с временем жизни 150 секунд (2,5 часа)


        if ($mutex->get()) {
            // Мьютекс успешно получен, выполняем задачу
            try {
                $accountSavedSettings = counterparty_add::all();

                foreach ($accountSavedSettings as $accountId) {
                    $settings = new getSettingVendorController($accountId);

                    try {
                        $ClientCheckMC = new MsClient($settings->TokenMoySklad);
                        $body = $ClientCheckMC->get('https://online.moysklad.ru/api/remap/1.2/entity/employee');

                        $ClientCheckUDS = new UdsClient($settings->companyId, $settings->TokenUDS);
                        $body = $ClientCheckUDS->get('https://api.uds.app/partner/v2/settings');
                    } catch (\Throwable $e) { continue; }

                    $data = [
                        "tokenMs" => $settings->TokenMoySklad,
                        "companyId" => $settings->companyId,
                        "apiKeyUds" => $settings->TokenUDS,
                        "accountId" => $settings->accountId
                    ];

                    dispatch(function () use ($data) {
                        try {
                            app(AgentService::class)->insertToMs($data);
                        }catch (BadResponseException){
                        }
                    })->onQueue('default');

                    // Продолжение выполнения команды
                    $this->info('successfully.');
                    /* dd( app(ProductCreateUdsService::class)->insertToUds($data));*/
                }
            } finally {
                $mutex->release(); // Освобождаем мьютекс
            }
        } else {
            // Задача уже выполняется, пропускаем запуск
            $this->info('Previous task is still running. Skipping the current run.');
        }

    }


}
