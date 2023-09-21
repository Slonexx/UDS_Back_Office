<?php

namespace App\Console\Commands;

use App\Components\MsClient;
use App\Components\UdsClient;
use App\Http\Controllers\BD\getMainSettingBD;
use App\Models\newAgentModel;
use App\Services\newAgentService\createAgentForMS;
use Illuminate\Support\Facades\Cache;
use GuzzleHttp\Exception\BadResponseException;
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
        $mutex = Cache::lock('process_NewAgents', 3600);

        if (!$mutex->get()) {
            $this->info('Previous task is still running. Skipping the current run.');
            return;
        }

        $allSettings = newAgentModel::all();

        foreach ($allSettings as $item) {
            if (!$this->shouldProcessItem($item)) {
                continue;
            }

            $mainSetting = new getMainSettingBD($item->accountId);
            $this->processItem($item, $mainSetting);
        }

        $mutex->release();
    }

    private function shouldProcessItem($item): bool
    {
        $unloadingValue = $item->getAttributes()['unloading'];
        return !empty($unloadingValue) && $unloadingValue !== '0';
    }

    private function processItem($item, $mainSetting): void
    {
        try {
            $clientCheckMC = new MsClient($mainSetting->tokenMs);
            $clientCheckMC->get('https://api.moysklad.ru/api/remap/1.2/entity/employee');

            $clientCheckUDS = new UdsClient($mainSetting->companyId, $mainSetting->TokenUDS);
            $clientCheckUDS->get('https://api.uds.app/partner/v2/settings');
        } catch (BadResponseException) {
            return;
        }

        $data = [
            'accountId' => $item->accountId,

            'unloading' => $item->unloading,

            'examination' => $item->examination,
            'email' => $item->email,
            'gender' => $item->gender,
            'birthDate' => $item->birthDate,

            'url' => $item->url,
            'offset' => $item->offset,

            'countRound' => $item->countRound,
        ];

        $record = newAgentModel::where('accountId', $item->accountId)->first();
        if ( $item->countRound < 20) {
            newAgentModel::where('accountId',  $item->accountId)->update([
                'countRound' => $record->countRound + 1,
            ]);
            try {
                (new createAgentForMS($data, $clientCheckMC, $clientCheckUDS))->initialization();
            } catch (BadResponseException) { return; }

        } else {
            newAgentModel::where('accountId',  $item->accountId)->update([
                'unloading' => 0,
                'examination' => null,
                'email' => null,
                'gender' => null,
                'birthDate' => null,
            ]);

        }



    }



}

