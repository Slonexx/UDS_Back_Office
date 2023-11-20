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
            } catch (BadResponseException $e) {
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

            if ($item->unloading === null) {
                continue;
            }

            $record = NewProductModel::where('accountId', $data['accountId'])->first();

            if ($record) {
                $record->delete();
            }

            $model = new NewProductModel();

            $model->fill($data);

            if ($data['countRound'] < 3) {
                $model->countRound = $data['countRound'];
            } else {
                $model->countRound = 0;
            }

            $model->save();
        }
    }
}
