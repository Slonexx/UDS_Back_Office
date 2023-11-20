<?php

namespace App\Console\Commands\product;

use App\Components\MsClient;
use App\Components\UdsClient;
use App\Http\Controllers\BD\getMainSettingBD;
use App\Models\newProductModel;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Console\Command;

class CronCommandantProductCreateDay extends Command
{
    protected $signature = 'cronCommand:updateProductCreateDay';

    protected $description = '';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $allSettings = newProductModel::all();

        foreach ($allSettings as $item) {
            $mainSetting = new getMainSettingBD($item->accountId);
            try {
                $clientCheckMC = new MsClient($mainSetting->tokenMs);
                $clientCheckMC->get('https://api.moysklad.ru/api/remap/1.2/entity/employee');

                $clientCheckUDS = new UdsClient($mainSetting->companyId, $mainSetting->TokenUDS);
                $clientCheckUDS->get('https://api.uds.app/partner/v2/settings');
            } catch (BadResponseException) {
                continue;
            }

            $data = [
                'accountId' => $item->accountId,
                'ProductFolder' => $item->ProductFolder,
                'unloading' => $item->unloading,
                'salesPrices' => $item->salesPrices,
                'promotionalPrice' => $item->promotionalPrice,
                'Store' => $item->Store,
                'StoreRecord' => $item->StoreRecord,
                'productHidden' => $item->productHidden,
                'countRound' => $item->countRound,
            ];

            if ($item->unloading === null) continue;


            $record = newProductModel::where('accountId', $data['accountId'])->first();

            if ($record) {
                $record->update([
                    'ProductFolder' => $data['ProductFolder'],
                    'unloading' => $data['unloading'],
                    'salesPrices' => $data['salesPrices'],
                    'promotionalPrice' => $data['promotionalPrice'],
                    'Store' => $data['Store'],
                    'StoreRecord' => $data['StoreRecord'],
                    'productHidden' => $data['productHidden'],
                    'countRound' => intval($data['countRound']) >= 3 ? '0' : $data['countRound'],
                ]);
            }
        }
    }
}
