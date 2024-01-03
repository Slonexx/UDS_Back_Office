<?php

namespace App\Console\Commands\product;

use App\Components\MsClient;
use App\Components\UdsClient;
use App\Http\Controllers\BD\getMainSettingBD;
use App\Models\newProductModel;
use App\Services\newProductService\createProductForMS;
use App\Services\newProductService\createProductForUDS;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class CronCommandProductCreate extends Command
{
    protected $signature = 'cronCommand:ProductCreate';

    protected $description = '';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $mutex = Cache::lock('process_NewProduct', 9000);

        if (/*true) { //*/$mutex->get()) {

            $allSettings = newProductModel::all();

            foreach ($allSettings as $item) {
                $mainSetting = new getMainSettingBD($item->getAttributes()['accountId']);
                try {
                    $ClientCheckMC = new MsClient($mainSetting->tokenMs);
                    $ClientCheckMC->get('https://api.moysklad.ru/api/remap/1.2/entity/employee');
                    $ClientCheckUDS = new UdsClient($mainSetting->companyId, $mainSetting->TokenUDS);
                    $ClientCheckUDS->get('https://api.uds.app/partner/v2/settings');
                } catch (BadResponseException) { continue; }
                if ($item->getAttributes()['ProductFolder'] == '0' or $item->getAttributes()['ProductFolder'] == null) continue;

                $data = [
                    'accountId' => $item->getAttributes()['accountId'],
                    'salesPrices' => $item->getAttributes()['salesPrices'],
                    'promotionalPrice' => $item->getAttributes()['promotionalPrice'],
                    'Store' => $item->getAttributes()['Store'],
                    'StoreRecord' => $item->getAttributes()['StoreRecord'],
                    'productHidden' => $item->getAttributes()['productHidden'],
                    'countRound' => $item->getAttributes()['countRound'],
                ];

                if ($item->getAttributes()['unloading'] ==  '1') { $data['loading'] = true; }
                else  $data['loading'] = false;

                if ($item->getAttributes()['unloading'] ==  null) continue;

                try {

                    dispatch(function () use ($data, $item) {
                        $item->countRound += 1;
                        $item->save();

                        if ($data['loading']) $create = new createProductForMS($data);
                        else $create = new createProductForUDS($data);

                        if ($data['countRound'] < 3) $create->initialization();
                    })->onQueue('default');

                    $this->info('successfully.');

                } finally {
                    $mutex->release(); // Освобождаем мьютекс
                }

            }

        } else {
            // Задача уже выполняется, пропускаем запуск
            $this->info('Previous task is still running. Skipping the current run.');
        }
    }
}
