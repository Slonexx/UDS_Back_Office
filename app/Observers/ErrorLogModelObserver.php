<?php

namespace App\Observers;

use App\Models\errorLog;
use Illuminate\Support\Facades\DB;

class ErrorLogModelObserver
{

    public function created(errorLog $infoLogModel)
    {


        $accountIds = errorLog::all('accountId');

        foreach($accountIds as $accountId){

            $query = errorLog::query();
            $logs = $query->where('accountId',$accountId->accountId)->get();
            if(count($logs) > 100){
                DB::table('error_logs')
                    ->where('accountId','=',$accountId->accountId)
                    ->orderBy('created_at', 'ASC')
                    ->limit(1)
                    ->delete();
            }

        }

    }


    public function updated(errorLog $infoLogModel)
    {
        //
    }

    public function deleted(errorLog $infoLogModel)
    {
        //
    }

    public function restored(errorLog $infoLogModel)
    {
        //
    }

    public function forceDeleted(errorLog $infoLogModel)
    {
        //
    }

}
