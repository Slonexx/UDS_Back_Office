<?php

namespace App\Observers;

use App\Models\InfoLogModel;
use App\Models\order_id;
use Illuminate\Support\Facades\DB;

class orderIDModelObserver
{

    public function created(order_id $infoLogModel)
    {


        $accountIds = order_id::all('accountId');

        foreach($accountIds as $accountId){

            $query = order_id::query();
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


    public function updated(order_id $infoLogModel)
    {
        //
    }

    public function deleted(order_id $infoLogModel)
    {
        //
    }

    public function restored(order_id $infoLogModel)
    {
        //
    }

    public function forceDeleted(order_id $infoLogModel)
    {
        //
    }
}
