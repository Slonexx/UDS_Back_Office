<?php

namespace App\Console\Commands;

use App\Components\MsClient;
use App\Components\UdsClient;
use App\Http\Controllers\BD\getMainSettingBD;
use App\Models\newAgentModel;
use App\Models\newProductModel;
use App\Services\newAgentService\createAgentForMS;
use App\Services\newProductService\createProductForMS;
use App\Services\newProductService\createProductForUDS;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Console\Command;

class CronCommandAgentCreate extends Command
{

    protected $signature = 'cronCommand:AgentCreate';

    protected $description = ' ';
    public function __construct()
    {
        parent::__construct();
    }
    public function handle(): void
    {
        $mutex = Cache::lock('process_NewAgent', 9000);

        if ( $mutex->get() ) {
            $allSettings = newAgentModel::all();

            foreach ($allSettings as $item) {
                $mainSetting = new getMainSettingBD($item->getAttributes()['accountId']);
                try {
                    $ClientCheckMC = new MsClient($mainSetting->tokenMs);
                    $ClientCheckMC->get('https://online.moysklad.ru/api/remap/1.2/entity/employee');
                    $ClientCheckUDS = new UdsClient($mainSetting->companyId, $mainSetting->TokenUDS);
                    $ClientCheckUDS->get('https://api.uds.app/partner/v2/settings');
                } catch (BadResponseException) { continue; }
                if ($item->getAttributes()['unloading'] == '0' or $item->getAttributes()['unloading'] == null) continue;

                $data = [
                    'accountId' => $item->getAttributes()['accountId'],

                    'unloading' => $item->getAttributes()['unloading'],
                    'examination' => $item->getAttributes()['examination'],
                    'email' => $item->getAttributes()['email'],
                    'gender' => $item->getAttributes()['gender'],
                    'birthDate' => $item->getAttributes()['birthDate'],

                    'url' => $item->getAttributes()['url'],
                    'offset' => $item->getAttributes()['offset'],
                ];
                if ($item->getAttributes()['unloading'] ==  null or $item->getAttributes()['unloading'] ==  '0') continue;


                try {
                    $create = new createAgentForMS($data, $ClientCheckMC, $ClientCheckUDS);
                    $create->initialization();
                } catch (BadResponseException) {
                    continue;
                }
            }

            $mutex->release();
        } else {
            $this->info('Previous task is still running. Skipping the current run.');
        }
    }
}
