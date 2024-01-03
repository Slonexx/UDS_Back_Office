<?php


namespace App\Console\Commands\product;

use App\Components\MsClient;
use App\Components\UdsClient;
use App\Http\Controllers\BD\getMainSettingBD;
use App\Http\Controllers\BD\newProductSettingBD;
use App\Http\Controllers\getData\getSetting;
use App\Models\newProductModel;
use App\Services\newProductService\createProductForMS;
use App\Services\newProductService\createProductForUDS;
use App\Services\product\ProductCreateUdsService;
use App\Services\Settings\SettingsService;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Promise\Each;
use Illuminate\Console\Command;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Generator;

class NewCronCommandProductCreate extends Command
{

    protected $signature = 'devCronCommand:ProductCreate';

    protected $description = ' ';

    private SettingsService $settingsService;

    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
        parent::__construct();
    }

    public function handle()
    {

        $allSettings = newProductModel::all();

        foreach ($allSettings as $item) {

            if ($item->getAttributes()['accountId'] != '1dd5bd55-d141-11ec-0a80-055600047495') continue;

            $mainSetting = new getMainSettingBD($item->getAttributes()['accountId']);
            try {
                $ClientCheckMC = new MsClient($mainSetting->tokenMs);
                $ClientCheckMC->get('https://api.moysklad.ru/api/remap/1.2/entity/employee');
                $ClientCheckUDS = new UdsClient($mainSetting->companyId, $mainSetting->TokenUDS);
                $ClientCheckUDS->get('https://api.uds.app/partner/v2/settings');
            } catch (BadResponseException) {
                continue;
            }
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

            if ($item->getAttributes()['unloading'] == '1') {
                $data['loading'] = true;
            } else  $data['loading'] = false;

            if ($item->getAttributes()['unloading'] == null) continue;

            dispatch(function () use ($data, $item) {
                $item->countRound += 1;
                $item->save();

                if ($data['loading'] && $data['countRound'] < 300) {
                    $create = new createProductForMS($data);
                } else {
                    $create = new createProductForUDS($data);
                }
                $create->initialization();
            })->onQueue('default');

            $this->info('successfully.');


        }

    }
}
