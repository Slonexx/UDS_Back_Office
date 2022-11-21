<?php

namespace App\Observers;

use App\Models\orderSettingModel;
use Illuminate\Support\Facades\DB;

class orderSettingObserver
{
    public function created(orderSettingModel $BD)
    {


        $accountIds = orderSettingModel::all('accountId');

        foreach($accountIds as $accountId){

            $query = orderSettingModel::query();
            $logs = $query->where('accountId',$accountId->accountId)->get();
            if(count($logs) > 1){
                DB::table('order_setting_models')
                    ->where('accountId','=',$accountId->accountId)
                    ->orderBy('created_at', 'ASC')
                    ->limit(1)
                    ->delete();
            }

        }

    }


    public function updated(orderSettingModel $BD)
    {
        //
    }

    public function deleted(orderSettingModel $BD)
    {
        //
    }

    public function restored(orderSettingModel $BD)
    {
        //
    }

    public function forceDeleted(orderSettingModel $BD)
    {
        //
    }
}
