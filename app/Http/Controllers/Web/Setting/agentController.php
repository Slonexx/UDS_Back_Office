<?php

namespace App\Http\Controllers\Web\Setting;

use App\Components\MsClient;
use App\Http\Controllers\BD\getMainSettingBD;
use App\Http\Controllers\BD\newAgentSettingBD;
use App\Http\Controllers\BD\newProductSettingBD;
use App\Http\Controllers\Config\getSettingVendorController;
use App\Http\Controllers\Controller;
use App\Models\newAgentModel;
use App\Models\ProductFoldersByAccountID;
use Illuminate\Http\Request;

class agentController extends Controller
{
    public function getAgent(Request $request, $accountId, $isAdmin): \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse
    {
        if ($isAdmin == "NO") {
            return redirect()->route('indexNoAdmin', ["accountId" => $accountId, "isAdmin" => $isAdmin]);
        }

        $newSetting = new newAgentSettingBD($accountId);

        $message = $this->messageRequest($request->message);

        if ($newSetting->unloading == null or  $newSetting->unloading == '0'){
            $examination = '0';
            $email = '';
            $gender = '';
            $birthDate = '';
        } else {
            $examination = $newSetting->examination ?? '0';
            $email = $newSetting->email ?? '0';
            $gender = $newSetting->gender ?? '';
            $birthDate = $newSetting->birthDate ?? '';
        }

        return view('web.Setting.Agent.Agent', [

            'unloading' => (string) $newSetting->unloading,
            'examination' => $examination,
            'email' => $email,
            'gender' => $gender,
            'birthDate' => $birthDate,


            "accountId" => $accountId,
            "message" => $message,
            "isAdmin" => $isAdmin,
        ]);
    }


    public function postAgent(Request $request, $accountId, $isAdmin): \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse
    {
        if ($isAdmin == "NO") {
            return redirect()->route('indexNoAdmin', ["accountId" => $accountId, "isAdmin" => $isAdmin]);
        }

        $message = $this->messageRequest($request->message);


        $this->createNewAgentModel($accountId, $request);
        $message["alert"] = " alert alert-success alert-dismissible fade show in text-center ";
        $message["message"] = "Настройки сохранились!";


        return redirect()->route('agent', ['accountId' => $accountId, 'isAdmin' => $isAdmin, 'message' => $message]);
    }


    private function messageRequest(mixed $message): array
    {
        if ($message) {
            return [
                'status' => true,
                'message' => $message['message'],
                'alert' => $message['alert'],
            ];
        } else return [
            'status' => false,
            'message' => "",
            'alert' => "",
        ];
    }



    private function createNewAgentModel($accountId, $request): void
    {
        $model = new newAgentModel();
        $existingRecords = newAgentModel::where('accountId', $accountId)->get();

        if (!$existingRecords->isEmpty()) { foreach ($existingRecords as $record) { $record->delete(); } }

        $model->accountId = $accountId;
        $model->unloading = $request->unloading;

        if ($request->unloading == '0'){
            $model->examination = null;
            $model->email = null;
            $model->gender = null;
            $model->birthDate = null;
            $model->url = 'https://api.uds.app/partner/v2/customers?max=50&offset=0';
            $model->offset = 0;
        } else {
            $model->examination = $request->examination ?? '';
            $model->email = $request->email ?? '';
            $model->gender = $request->gender ?? '';
            $model->birthDate = $request->birthDate ?? '';
            $model->url = 'https://api.uds.app/partner/v2/customers?max=50&offset=0';
            $model->offset = 0;
        }

        $model->save();
    }
}
