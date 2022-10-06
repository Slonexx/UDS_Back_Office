<?php

namespace App\Http\Controllers\getData;

use App\Http\Controllers\Controller;
use App\Models\sendOperationsModel;
use App\Observers\sendOperationsSetttingObserver;
use Illuminate\Http\Request;

class getSetting extends Controller
{
    public function getSendSettingOperations($accountId){
        $find = sendOperationsModel::query()->where('accountId', $accountId)->first();
        try {
            $result = $find->getAttributes();
        } catch (\Throwable $e) {
            $result = [
                "accountId" => $accountId,
                "EnableOffs" => null,
                "operationsDocument" => null,
                "operationsPaymentDocument" => null,
            ];
        }
        return json_decode(json_encode($result));
    }
}
