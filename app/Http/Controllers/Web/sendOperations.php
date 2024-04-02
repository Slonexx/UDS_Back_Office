<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Config\getSettingVendorController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\getData\getSetting;
use App\Models\sendOperationsModel;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class sendOperations extends Controller
{
    public function index($accountId, $isAdmin): View|Factory|Application|RedirectResponse
    {
        if ($isAdmin == "NO") {
            return redirect()->route('indexNoAdmin', ["accountId" => $accountId, "isAdmin" => $isAdmin]);
        }

        $Setting = new getSettingVendorController($accountId);
        $companyId = $Setting->companyId;
        if ($companyId == null) {
            $message = " Основные настройки не были установлены ";
            return redirect()->route('indexError', [
                "accountId" => $accountId,
                "isAdmin" => $isAdmin,
                "message" => $message,
            ]);
        }

        $SettingBD = (new getSetting())->getSendSettingOperations($accountId);
//dd($SettingBD);
        $operationsAccrue = $SettingBD->operationsAccrue ?? 0;
        $operationsCancellation = $SettingBD->operationsCancellation ?? 0;
        $operationsDocument = $SettingBD->operationsDocument ?? 0;
        $operationsPaymentDocument = $SettingBD->operationsPaymentDocument ?? 0;
        $customOperation = $SettingBD->customOperation ?? 0;

        return view('web.Setting.send_operations', [
            "accountId" => $accountId,
            "isAdmin" => $isAdmin,
            'operationsAccrue' => $operationsAccrue,
            'operationsCancellation' => $operationsCancellation,
            'operationsDocument' => $operationsDocument,
            'operationsPaymentDocument' => $operationsPaymentDocument,
            'customOperation' => $customOperation,

            "message" => $request->message ?? '',
            "class_message" => $request->class_message ?? 'is-info',
        ]);
    }


    public function postOperations(Request $request, $accountId, $isAdmin): Factory|View|Application
    {

        try {

            $model = new sendOperationsModel();
            $existingRecords = sendOperationsModel::where('accountId', $accountId)->get();

            if (!$existingRecords->isEmpty()) {
                foreach ($existingRecords as $record) {
                    $record->delete();
                }
            }

            $model->accountId = $accountId;
            $model->operationsAccrue = $request->operationsAccrue ?? 0 ;
            $model->operationsCancellation = $request->operationsCancellation ?? 0 ;
            $model->operationsDocument = $request->operationsDocument ?? 0 ;
            $model->operationsPaymentDocument = $request->PaymentDocument ?? 0 ;
            $model->customOperation = $request->customOperation ?? 0 ;

            $model->save();

            $class_message = "is-success";
            $message = "Настройки сохранились!";

        } catch (BadResponseException) {
            $class_message = "is-danger";
            $message = "Ошибка настройки не сохранились";
        }


        return view('web.Setting.send_operations', array(
            'message' => $message,
            'class_message' => $class_message,


            "accountId" => $accountId,
            'isAdmin' => $isAdmin,

            'operationsAccrue' => $request->operationsAccrue ?? 0 ,
            'operationsCancellation' => $request->operationsCancellation ?? 0 ,
            'operationsDocument' => $request->operationsDocument ?? 0 ,
            'operationsPaymentDocument' => $request->PaymentDocument ?? 0 ,
            'customOperation' => $request->customOperation ?? 0 ,
        ));

    }
}
