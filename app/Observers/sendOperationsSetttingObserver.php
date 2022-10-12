<?php

namespace App\Observers;

use App\Models\f;
use App\Models\sendOperationsModel;
use Illuminate\Support\Facades\DB;

class sendOperationsSetttingObserver
{
    public function created(sendOperationsModel $BD)
    {


        $accountIds = sendOperationsModel::all('accountId');

        foreach($accountIds as $accountId){

            $query = sendOperationsModel::query();
            $logs = $query->where('accountId',$accountId->accountId)->get();
            if(count($logs) > 1){
                DB::table('send_operations_models')
                    ->where('accountId','=',$accountId->accountId)
                    ->orderBy('created_at', 'ASC')
                    ->limit(1)
                    ->delete();
            }

        }

    }


    public function updated(sendOperationsModel $BD)
    {
        //
    }

    public function deleted(sendOperationsModel $BD)
    {
        //
    }

    public function restored(sendOperationsModel $BD)
    {
        //
    }

    public function forceDeleted(orderSettingModel $BD)
    {
        //
    }
}
