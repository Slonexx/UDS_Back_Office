<?php

namespace App\Http\Controllers\getData;

use App\Http\Controllers\Controller;
use App\Models\sendOperationsModel;
use App\Models\SettingMain;
use App\Observers\sendOperationsSetttingObserver;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Http\Request;

class getSetting extends Controller
{
    public function getSendSettingOperations($accountId){
        $find = sendOperationsModel::query()->where('accountId', $accountId)->first();
        if ($find == null) {
            $result = [
                "accountId" => $accountId,
                "operationsAccrue" => null,
                "operationsCancellation" => null,
                "operationsDocument" => null,
                "operationsPaymentDocument" => null,
                "customOperation" => null,
            ];
        } else
        try {
            $result = $find->getAttributes();
        } catch (BadResponseException $e) {
            $result = [
                "accountId" => $accountId,
                "operationsAccrue" => null,
                "operationsCancellation" => null,
                "operationsDocument" => null,
                "operationsPaymentDocument" => null,
            ];
        }
        return json_decode(json_encode($result));
    }
    public function getSettingMain($accountId){
        $find = SettingMain::query()->where('accountId', $accountId)->first();
        try {
            $result = $find->getAttributes();
        } catch (BadResponseException $e) {
            $result = [
                "accountId" => $accountId,
                "TokenMoySklad" => null,
                "companyId" => null,
                "TokenUDS" => null,
                "ProductFolder" => null,
                "UpdateProduct" => null,
                "Store" => null,
            ];
        }
        return json_decode(json_encode($result));
    }
}
