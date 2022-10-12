<?php

namespace App\Observers;

use App\Models\SettingMain;
use Illuminate\Support\Facades\DB;

class SettingMainObserver
{
    public function created(SettingMain $BD)
    {


        $accountIds = SettingMain::all('accountId');

        foreach($accountIds as $accountId){

            $query = SettingMain::query();
            $logs = $query->where('accountId',$accountId->accountId)->get();
            if(count($logs) > 1){
                DB::table('setting_mains')
                    ->where('accountId','=',$accountId->accountId)
                    ->orderBy('created_at', 'ASC')
                    ->limit(1)
                    ->delete();
            }

        }

    }


    public function updated(SettingMain $BD)
    {
        //
    }

    public function deleted(SettingMain $BD)
    {
        //
    }

    public function restored(SettingMain $BD)
    {
        //
    }

    public function forceDeleted(SettingMain $BD)
    {
        //
    }
}
