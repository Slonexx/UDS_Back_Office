<?php

namespace App\Observers;

use App\Http\Controllers\BackEnd\BDController;
use App\Models\counterparty_add;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Artisan;
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

        /*$client = new Client(['base_uri' => 'https://smartuds.kz/api/']);
        $client->post('attributes',[
            'headers'=> ['Accept' => 'application/json'],
            'form_params' => [
                "tokenMs" => $infoLogModel->tokenMC,
                "accountId" => $infoLogModel->accountId
            ]
        ]);*/

        /*Artisan::queue('attributes:start',[
             'accountId' => $infoLogModel->accountId
        ])->onQueue("attributes");*/
        //exec('php artisan queue:work --queue=attributes --once > storage/logs/process.log &');
        //$infoLogModel->delete();
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
