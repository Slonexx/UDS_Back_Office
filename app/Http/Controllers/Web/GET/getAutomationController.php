<?php

namespace App\Http\Controllers\Web\GET;

use App\Components\MsClient;
use App\Http\Controllers\Config\getSettingVendorController;
use App\Http\Controllers\Controller;
use App\Models\Automation_new_update_MODEL;
use Illuminate\Http\Request;

class getAutomationController extends Controller
{
    public function getAutomation(Request $request, $accountId, $isAdmin): \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse
    {
        if ($isAdmin == "NO") {
            return redirect()->route('indexNoAdmin', ["accountId" => $accountId, "isAdmin" => $isAdmin]);
        }

        if (isset($request->message)) {
            $message = $request->message;
            if ($message == "Настройки сохранились") {
                $class = "mt-1 alert alert-success alert-dismissible fade show in text-center";
            } else $class = "mt-1 alert alert-warning alert-danger fade show in text-center";
        } else {
            $message = '';
            $class = '';
        };

        $Setting = new getSettingVendorController($accountId);
        $Client = new MsClient($Setting->TokenMoySklad);

        $body_meta = $Client->get('https://online.moysklad.ru/api/remap/1.2/entity/customerorder/metadata')->states;
        $body_store = $Client->get('https://online.moysklad.ru/api/remap/1.2/entity/store')->rows;
        $body_project = $Client->get('https://online.moysklad.ru/api/remap/1.2/entity/project')->rows;
        $body_saleschannel = $Client->get('https://online.moysklad.ru/api/remap/1.2/entity/saleschannel')->rows;

        $body_organization = $Client->get("https://online.moysklad.ru/api/remap/1.2/entity/organization/")->rows;

        $find = Automation_new_update_MODEL::query()->where('accountId', $accountId)->first();
        if ($find == null) {
            $activateAutomation = 0;
            $statusAutomation = 0;
            $projectAutomation = 0;
            $saleschannelAutomation = 0;
            $automationDocument = 1;
            $add_automationStore = 0;
            $add_automationPaymentDocument = 0;
            $documentAutomation = 0;
        } else {
            $activateAutomation = $find->getAttributes()['activateAutomation'];
            $statusAutomation = $find->getAttributes()['statusAutomation'];
            $projectAutomation = $find->getAttributes()['projectAutomation'];
            $saleschannelAutomation = $find->getAttributes()['saleschannelAutomation'];
            $automationDocument = $find->getAttributes()['automationDocument'];
            $add_automationStore = $find->getAttributes()['add_automationStore'];
            $add_automationPaymentDocument = $find->getAttributes()['add_automationPaymentDocument'];
            $documentAutomation = $find->getAttributes()['documentAutomation'];
        }

        return view('web.Setting.Automation', [
            'arr_meta' => $body_meta,
            'arr_project' => $body_project,
            'arr_store' => $body_store,
            'arr_saleschannel' => $body_saleschannel,
            'arr_organization' => $body_organization,

            'activateAutomation' => $activateAutomation,
            'statusAutomation' => $statusAutomation,
            'projectAutomation' => $projectAutomation,
            'saleschannelAutomation' => $saleschannelAutomation,

            'automationDocument' => $automationDocument,
            'add_automationStore' => $add_automationStore,
            'add_automationPaymentDocument' => $add_automationPaymentDocument,
            'documentAutomation' => $documentAutomation,


            "message" => $message,
            "class" => $class,

            "accountId" => $accountId,
            "isAdmin" => $isAdmin,
        ]);
    }
}
