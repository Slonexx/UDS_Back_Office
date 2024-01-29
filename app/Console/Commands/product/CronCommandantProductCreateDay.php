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

            $countRound =  $item->countRound;

            $model = new newProductModel();

            $model->accountId = $item->accountId;
            $model->ProductFolder = $item->ProductFolder;
            $model->unloading = $item->unloading;
            $model->salesPrices = $item->salesPrices;
            $model->promotionalPrice = $item->promotionalPrice;
            $model->Store = $item->Store;
            $model->StoreRecord = $item->StoreRecord;
            $model->productHidden = $item->productHidden;

            $record = newProductModel::where('accountId', $item->accountId)->first();

            if ($record) $record->delete();

            if ($countRound >= 3) $model->countRound = 0; else  $model->countRound = $countRound;


            $model->save();
        }
    }
}
