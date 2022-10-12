<?php

namespace App\Http\Controllers\BackEnd;

use App\Http\Controllers\Controller;
use App\Models\Agent_503;
use App\Models\counterparty_add;
use App\Models\errorLog;
use App\Models\order_id;
use App\Models\order_update;
use App\Models\ProductModel;
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

    public function errorProductLog($accountId, $message)
    {
        try {
            ProductModel::create([
                "accountId" => $accountId,
                "message" => $message,
            ]);
        } catch (ClientException $e) {
            ProductModel::create([
                "accountId" => $accountId,
                "message" => $e->getMessage(),
            ]);
        }
    }

    public function throwToRetryAgent($accountId, $url, $offset){
        try {
            $results = DB::table('agent_503s')->where('accountId',$accountId)->get();

            if (count($results) > 0 ){
                DB::table('agent_503s')->where('accountId',$accountId)->update([
                    'url' => $url,
                    'offset' => $offset,
                ]);
            } else {
                Agent_503::create([
                    'accountId' => $accountId,
                    'url' => $url,
                    'offset' => $offset,
                ]);
            }

        } catch (ClientException $e) {
            Agent_503::create([
                'accountId' => $accountId,
                'url' => $e->getMessage(),
                'offset' => 0,
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
