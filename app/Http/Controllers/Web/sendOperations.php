<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Config\getSettingVendorController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\getData\getSetting;
use App\Models\sendOperationsModel;
use Illuminate\Http\Request;

class sendOperations extends Controller
{
    public function index(Request $request, $accountId, $isAdmin){
        if ($isAdmin == "NO"){
            return redirect()->route('indexNoAdmin', ["accountId" => $accountId, "isAdmin" => $isAdmin] );
        }

        $Setting = new getSettingVendorController($accountId);
        $companyId = $Setting->companyId;
        if ( $companyId == null ) {
            $message = " Основные настройки не были установлены ";
            return redirect()->route('indexError', [
                "accountId" => $accountId,
                "isAdmin" => $isAdmin,
                "message" => $message,
            ]);
        }

        $SettingBD = new getSetting();
        $SettingBD = $SettingBD->getSendSettingOperations($accountId);
        //dd($SettingBD);
        if ($SettingBD->operationsAccrue != null) $operationsAccrue = $SettingBD->operationsAccrue; else $operationsAccrue = 0 ;
        if ($SettingBD->operationsCancellation != null) $operationsCancellation = $SettingBD->operationsCancellation; else $operationsCancellation = 0 ;
        if ($SettingBD->operationsDocument != null) $operationsDocument = $SettingBD->operationsDocument; else $operationsDocument = 0 ;
        if ($SettingBD->operationsPaymentDocument != null) $operationsPaymentDocument = $SettingBD->operationsPaymentDocument; else $operationsPaymentDocument = 0 ;

        return view('web.Setting.send_operations', [
            "accountId"=> $accountId,
            "isAdmin" => $isAdmin,

            'operationsAccrue' => $operationsAccrue,
            'operationsCancellation' => $operationsCancellation,
            'operationsDocument' => $operationsDocument,
            'operationsPaymentDocument' => $operationsPaymentDocument,
        ]);
    }


    public function postOperations(Request $request, $accountId, $isAdmin){

        try {
            sendOperationsModel::create([
                'accountId' => $accountId,
                'operationsAccrue' => $request->operationsAccrue,
                'operationsCancellation' => $request->operationsCancellation,
                'operationsDocument' => $request->operationsDocument,
                'operationsPaymentDocument' => $request->PaymentDocument,
            ]);
            $message["alert"] = " alert alert-success alert-dismissible fade show in text-center ";
            $message["message"] = "Настройки сохранились!";

        } catch (\Throwable $e){
            $message["alert"] = " alert alert-danger alert-dismissible fade show in text-center ";
            $message["message"] = "Ошибка настройки не сохранились";
        }

        return view('web.Setting.send_operations', [
            "message" => $message,
            "accountId"=> $accountId,
            'isAdmin' => $isAdmin,

            'operationsAccrue' => $request->operationsAccrue,
            'operationsCancellation' => $request->operationsCancellation,
            'operationsDocument' => $request->operationsDocument,
            'operationsPaymentDocument' =>  $request->PaymentDocument,
        ]);

    }
}
