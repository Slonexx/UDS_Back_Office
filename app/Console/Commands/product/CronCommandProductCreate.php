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
        $mutex = Cache::lock('process_NewProduct', 4500);
        if ($mutex->get()) {
        //if (true) {
            $allSettings = newProductModel::all();
            foreach ($allSettings as $sql) {
                $item = $sql->toArray();
                $mainSetting = new getMainSettingBD($item['accountId']);
                $msClient = new MsClient($mainSetting->tokenMs);
                $udsClient = new UdsClient($mainSetting->companyId, $mainSetting->TokenUDS);

                $statusMS = $msClient->newGet('https://api.moysklad.ru/api/remap/1.2/entity/employee');
                $statusUDS = $udsClient->checkingSetting();
                if (!$statusMS->status and !$statusUDS->status) continue;
                if ($item['ProductFolder'] == '0' or $item['ProductFolder'] == null) continue;

                $data = $item;

                if ($item['unloading'] == '1') $data['loading'] = true;
                else $data['loading'] = false;


                if ($item['unloading'] == null) continue;
                try {

                    dispatch(function () use ($data, $sql) {
                        $sql->countRound += 1;
                        $sql->save();

                        if ($data['loading']) $create = new createProductForMS($data);
                        else $create = new createProductForUDS($data);

                        if ($data['countRound'] < 3) $create->initialization();
                    })->onQueue('default');

                    $this->info($data['accountId'].': успешно.');
                } finally {
                    $mutex->release(); // Освобождаем мьютекс
                }

            }

        } else $this->info('Previous task is still running. Skipping the current run.');

    }
}
