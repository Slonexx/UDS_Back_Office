<?php

namespace App\Console\Commands\product;

use App\Components\MsClient;
use App\Components\UdsClient;
use App\Http\Controllers\BD\getMainSettingBD;
use App\Models\newProductModel;
use App\Services\NewProductService\createProductForMS;
use App\Services\NewProductService\createProductForUDS;
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
        $mutex = Cache::lock('2process_NewProduct', 9000);

        if ($mutex->get()) {
            $allSettings = newProductModel::all();

            foreach ($allSettings as $item) {
                $mainSetting = new getMainSettingBD($item->accountId);
                if ($this->countRound($item->countRound, $item)) {
                    continue;
                }

                try {
                    $clientCheckMC = (new MsClient($mainSetting->tokenMs))->get('https://api.moysklad.ru/api/remap/1.2/entity/employee');
                    $clientCheckUDS = (new UdsClient($mainSetting->companyId, $mainSetting->TokenUDS))->get('https://api.uds.app/partner/v2/settings');
                } catch (BadResponseException) {
                    continue;
                }

                if ($item->ProductFolder == '0' || $item->ProductFolder === null) {
                    continue;
                }

                $data = [
                    'accountId' => $item->accountId,
                    'salesPrices' => $item->salesPrices,
                    'promotionalPrice' => $item->promotionalPrice,
                    'Store' => $item->Store,
                    'StoreRecord' => $item->StoreRecord,
                    'productHidden' => $item->productHidden,
                    'countRound' => $item->countRound,
                    'loading' => $item->unloading === '1',
                ];

                if ($item->unloading === null) {
                    continue;
                }

                try {
                    $this->processJob($data, $clientCheckMC, $clientCheckUDS);
                } catch (BadResponseException) {
                    continue;
                }
            }

            $mutex->release();
        } else {
            $this->info('Previous task is still running. Skipping the current run.');
        }
    }

    protected function processJob($data, $clientCheckMC, $clientCheckUDS): void
    {
        $create = $data['loading']
            ? new createProductForMS($data, $clientCheckMC, $clientCheckUDS)
            : new createProductForUDS($data, $clientCheckMC, $clientCheckUDS);

        $create->initialization();
    }

    private function countRound($countRound, $record): bool
    {
        if ($countRound < 3) {
            $record->countRound = $countRound + 1;
            $record->save();
            return false;
        }

        return true;
    }
}
