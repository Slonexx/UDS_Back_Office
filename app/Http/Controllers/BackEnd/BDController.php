<?php

namespace App\Http\Controllers\BackEnd;

use App\Http\Controllers\Controller;
use App\Models\counterparty_add;
use App\Models\errorLog;
use App\Models\order_id;
use App\Models\order_update;
use App\Models\webhookOrderLog;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BDController extends Controller
{
    public function createOrderID($accountId, $orderID, $companyId){
        try {
            order_id::create([
                'accountId' => $accountId,
                'orderID' => $orderID,
            ]);
        } catch (ClientException $exception){
            webhookOrderLog::create([
                'accountId' => $accountId,
                'message' => $exception->getMessage(),
                'companyId' => $companyId,
                ]);
        }
    }

    public function deleteOrderID($accountId, $orderID){
        DB::table('order_ids')
            ->where('accountId','=', $accountId)
            ->where('orderID', '=', $orderID)
            ->orderBy('created_at', 'ASC')
            ->limit(1)
            ->delete();
    }


    public function createCounterparty($accountId, $tokenMC, $companyId, $tokenUDS){
        try {
            counterparty_add::create([
                'accountId' => $accountId,
                'tokenMC' => $tokenMC,
                'companyId' => $companyId,
                'tokenUDS' => $tokenUDS,
            ]);
        } catch (ClientException $exception){
            errorLog::create([
                'accountId' => $accountId,
                'ErrorMessage' => $exception->getMessage(),
            ]);
        }
    }

    public function deleteCounterparty($tokenMC){
        DB::table('counterparty_adds')
            ->where('tokenMC','=', $tokenMC)
            ->orderBy('created_at', 'ASC')
            ->limit(1)
            ->delete();
    }


    public function errorLog($accountId, $message){
        try {
            errorLog::create([
                'accountId' => $accountId,
                'ErrorMessage' => $message,
            ]);
        } catch (ClientException $exception){
            errorLog::create([
                'accountId' => $accountId,
                'ErrorMessage' => $exception->getMessage(),
            ]);
        }
    }


    public function createUpdateOrder($accountId, $message){
        try {
            order_update::create([
                'accountId' => $accountId,
                'message' => $message,
            ]);
        } catch (ClientException $exception){
            errorLog::create([
                'accountId' => $accountId,
                'ErrorMessage' => $exception->getMessage(),
            ]);
        }
    }

}
