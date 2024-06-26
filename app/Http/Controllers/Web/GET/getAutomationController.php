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

        $Setting = new getSettingVendorController($accountId);
        $Client = new MsClient($Setting->TokenMoySklad);

        $demand = $Client->get('https://api.moysklad.ru/api/remap/1.2/entity/demand/metadata');
        if (!property_exists($demand,'states')) {
            $demand = [0=>['name'=>'Отсутствуют статусы в отгрузках, автоматизация не будет работать']];
        } else $demand = $demand->states;

        $body_meta = [
            'customerorder' => $Client->get('https://api.moysklad.ru/api/remap/1.2/entity/customerorder/metadata')->states,
            'demand' => $demand,
            ];
        $body_store = $Client->get('https://api.moysklad.ru/api/remap/1.2/entity/store')->rows;
        $body_project = $Client->get('https://api.moysklad.ru/api/remap/1.2/entity/project')->rows;
        $body_saleschannel = $Client->get('https://api.moysklad.ru/api/remap/1.2/entity/saleschannel')->rows;

        $body_organization = $Client->get("https://api.moysklad.ru/api/remap/1.2/entity/organization/")->rows;

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


            "message" => $request->message ?? '',
            "class_message" => $request->class_message ?? 'is-info',

            "accountId" => $accountId,
            "isAdmin" => $isAdmin,
        ]);
    }
}
