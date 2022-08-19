<?php

namespace App\Observers;

use App\Models\order_update;
use Illuminate\Support\Facades\DB;

class OrderUpdateModelObserver
{
    public function created(order_update $infoLogModel)
    {


        $accountIds = order_update::all('accountId');

        foreach($accountIds as $accountId){

            $query = order_update::query();
            $logs = $query->where('accountId',$accountId->accountId)->get();
            if(count($logs) > 100){
                DB::table('order_updates')
                    ->where('accountId','=',$accountId->accountId)
                    ->orderBy('created_at', 'ASC')
                    ->limit(1)
                    ->delete();
            }

        }

    }


    public function updated(order_update $infoLogModel)
    {
        //
    }

    public function deleted(order_update $infoLogModel)
    {
        //
    }

    public function restored(order_update $infoLogModel)
    {
        //
    }

    public function forceDeleted(order_update $infoLogModel)
    {
        //
    }
}
