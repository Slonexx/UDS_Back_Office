<?php

namespace App\Observers;

use App\Models\webhookClintLog;
use App\Models\webhookOrderLog;
use Illuminate\Support\Facades\DB;

class webhookOrderModelObserver
{



    public function created(webhookOrderLog $infoLogModel)
    {


        $accountIds = webhookOrderLog::all('accountId');

        foreach($accountIds as $accountId){

            $query = webhookOrderLog::query();
            $logs = $query->where('accountId',$accountId->accountId)->get();
            if(count($logs) > 100){
                DB::table('order_ids')
                    ->where('accountId','=',$accountId->accountId)
                    ->orderBy('created_at', 'ASC')
                    ->limit(1)
                    ->delete();
            }

        }

    }


    public function updated(webhookOrderLog $infoLogModel)
    {
        //
    }

    public function deleted(webhookOrderLog $infoLogModel)
    {
        //
    }

    public function restored(webhookOrderLog $infoLogModel)
    {
        //
    }

    public function forceDeleted(webhookOrderLog $infoLogModel)
    {
        //
    }
}
