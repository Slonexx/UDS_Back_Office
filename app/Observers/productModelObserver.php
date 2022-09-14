<?php

namespace App\Observers;

use App\Models\order_update;
use App\Models\ProductModel;
use Illuminate\Support\Facades\DB;

class productModelObserver
{
    public function created(ProductModel $infoLogModel)
    {


        $accountIds = ProductModel::all('accountId');

        foreach($accountIds as $accountId){

            $query = ProductModel::query();
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


    public function updated(ProductModel $infoLogModel)
    {
        //
    }

    public function deleted(ProductModel $infoLogModel)
    {
        //
    }

    public function restored(ProductModel $infoLogModel)
    {
        //
    }

    public function forceDeleted(ProductModel $infoLogModel)
    {
        //
    }
}
