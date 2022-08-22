<?php

namespace App\Observers;

use App\Http\Controllers\BackEnd\BDController;
use App\Models\counterparty_add;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Promise\Utils;

class CounterpartyAddModelObserver
{
    public function created(counterparty_add $infoLogModel)
    {


        $all = counterparty_add::all('tokenMC');

        foreach($all as $item){

            $query = counterparty_add::query();
            $logs = $query->where('tokenMC',$item->tokenMC)->get();
            if(count($logs) > 1){
                DB::table('counterparty_adds')
                    ->where('tokenMC','=',$item->tokenMC)
                    ->orderBy('created_at', 'ASC')
                    ->limit(1)
                    ->delete();
            }

        }

        //agent add
        //set Attributes
        $client = new Client(['base_uri' => 'https://smartuds.kz/api/']);

        $promises = [
            'agents' => $client->postAsync('agentMs',[
                'headers'=> ['Accept' => 'application/json'],
                'form_params' => [
                   "tokenMs" => $infoLogModel->tokenMC,
                   "companyId" => $infoLogModel->companyId,
                   "apiKeyUds" => $infoLogModel->tokenUDS,
                   "accountId" => $infoLogModel->accountId
               ]
            ]),
            'attributes' => $client->postAsync('attributes',[
                'headers'=> ['Accept' => 'application/json'],
                'form_params' => [
                    "tokenMs" => $infoLogModel->tokenMC,
                    "accountId" => $infoLogModel->accountId
                ]
            ])
        ];

        try {
            Utils::unwrap($promises);
        } catch (\Throwable $e) {
            $bd = new BDController();
            $bd->errorLog($infoLogModel->accountId,$e->getMessage());
        }

        $infoLogModel->delete();
    }


    public function updated(counterparty_add $infoLogModel)
    {
        //
    }

    public function deleted(counterparty_add $infoLogModel)
    {
        //
    }

    public function restored(counterparty_add $infoLogModel)
    {
        //
    }

    public function forceDeleted(counterparty_add $infoLogModel)
    {
        //
    }
}
