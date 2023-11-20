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

            $record = newProductModel::where('accountId', $data['accountId'])->first();

            if ($record) $record->delete();

            $model = new newProductModel();

            $model->accountId = $data['accountId'];
            $model->ProductFolder = $data['ProductFolder'];
            $model->unloading = $data['unloading'];
            $model->salesPrices = $data['salesPrices'];
            $model->promotionalPrice = $data['promotionalPrice'];
            $model->Store = $data['Store'];
            $model->StoreRecord = $data['StoreRecord'];
            $model->productHidden = $data['productHidden'];



            if (intval($data['countRound']) >= 3) $model->countRound = '0';
            else  $model->countRound = $data['countRound'];



            $model->save();
        }
    }
}
