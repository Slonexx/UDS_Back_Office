<?php

namespace App\Observers;

use App\Models\webhookClintLog;
use Illuminate\Support\Facades\DB;

class webhookClientModelObserver
{


    public function created(webhookClintLog $infoLogModel)
    {


        $accountIds = webhookClintLog::all('accountId');

        foreach($accountIds as $accountId){

            $query = webhookClintLog::query();
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


    public function updated(webhookClintLog $infoLogModel)
    {
        //
    }

    public function deleted(webhookClintLog $infoLogModel)
    {
        //
    }

    public function restored(webhookClintLog $infoLogModel)
    {
        //
    }

    public function forceDeleted(webhookClintLog $infoLogModel)
    {
        //
    }
}
