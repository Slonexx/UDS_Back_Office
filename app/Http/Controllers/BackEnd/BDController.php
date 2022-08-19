<?php

namespace App\Http\Controllers\BackEnd;

use App\Http\Controllers\Controller;
use App\Models\order_id;
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
}
