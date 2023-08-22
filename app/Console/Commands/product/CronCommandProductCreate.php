<?php

namespace App\Console\Commands\product;

use App\Components\MsClient;
use App\Components\UdsClient;
use App\Http\Controllers\BD\getMainSettingBD;
use App\Models\newProductModel;
use App\Services\newProductService\createProductForMS;
use App\Services\newProductService\createProductForUDS;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Console\Command;

class CronCommandProductCreate extends Command
{

    protected $signature = 'cronCommand:ProductCreate';

    protected $description = ' ';
    public function __construct()
    {
        parent::__construct();
    }
    public function handle(): void
    {
        $mutex = Cache::lock('process_NewProduct', 9000);

        if ($mutex->get()) {
            $allSettings = newProductModel::all();

            foreach ($allSettings as $item) {
                $mainSetting = new getMainSettingBD($item->getAttributes()['accountId']);
                try {
                    $ClientCheckMC = new MsClient($mainSetting->tokenMs);
                    $ClientCheckMC->get('https://online.moysklad.ru/api/remap/1.2/entity/employee');
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
                    $this->processJob($data, $ClientCheckMC, $ClientCheckUDS);
                } catch (BadResponseException) {
                    continue;
                }
            }

            $mutex->release();
        } else {
            $this->info('Previous task is still running. Skipping the current run.');
        }
    }

    protected function processJob($data, $ClientCheckMC, $ClientCheckUDS): void
    {
        if ($data['loading']) {
            if ($this->countRound($data['countRound'], $data['accountId'])) {
                $create = new createProductForMS($data, $ClientCheckMC, $ClientCheckUDS);
                $create->initialization();
            }
        } else {
            if ($this->countRound($data['countRound'], $data['accountId'])) {
                $create = new createProductForUDS($data, $ClientCheckMC, $ClientCheckUDS);
                $create->initialization();
            }
        }

    }

    private function countRound($countRound, $accountId): bool
    {
        $record = newProductModel::where('accountId', $accountId)->first();
        if ($countRound < 10) {
            $record->countRound = $countRound + 1;
            $record->save();
            return true;
        } else {
            $record->delete();
            return false;
        }
    }
}
