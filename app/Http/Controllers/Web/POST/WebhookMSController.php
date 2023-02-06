<?php

namespace App\Http\Controllers\Web\POST;

use App\Components\MsClient;
use App\Components\UdsClient;
use App\Http\Controllers\Config\getSettingVendorController;
use App\Http\Controllers\Controller;
use App\Models\Automation_new_update_MODEL;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Http\Request;

class WebhookMSController extends Controller
{
    public function customerorder(Request $request): \Illuminate\Http\JsonResponse
    {
        //if (property_exists($request['events'], 'updatedFields')){
        if (isset($request['events'][0]['updatedFields'])){
            if (!in_array('state', $request['events'][0]['updatedFields'])) {
                return response()->json([
                    'code' => 203,
                    'message' => $this->returnMessage("ERROR", $request['auditContext']['moment'], "Не было изменений статуса, скрипт прекращён!"),
                ]);
            }
        } else {
            return response()->json([
                'code' => 203,
                'message' => $this->returnMessage("ERROR", $request['auditContext']['moment'], "Отсутствует updatedFields, (изменений не было), скрипт прекращён!"),
            ]);
        }

        $find = Automation_new_update_MODEL::query()->where('accountId', $request['events'][0]['accountId'])->get()->all();
        if ($find == []) {
            return response()->json([
                'code' => 203,
                'message' => $this->returnMessage("ERROR", $request['auditContext']['moment'], "Отсутствует настройки автоматизации, скрипт прекращён!"),
            ]);
        }



        return response()->json([
            'code' => 200,
            'message' => $this->WebHookUpdateState($request['events'][0]['accountId'], $request['events'][0]['meta'],  $request['auditContext']['moment'] ),
            //$this->returnMessage("SUCCESS", $request['auditContext']['moment'], "Успешное выполнение, все данные обновлены"),
            ]);
    }

    private function returnMessage(string $State, $moment, string $Message): array|string
    {
        $result = '';
        switch ($State) {
            case 'ERROR':{
                $result = [
                    "ERROR ==========================================",
                    "[".$moment."] - Начала выполнение скрипта",
                    "[".date('Y-m-d H:i:s')."] - Конец выполнение скрипта",
                    "===============================================",
                    $Message,
                ];
                break;
            }
            case 'SUCCESS': {
                $result = [
                    "[".$moment."] - Начала выполнение скрипта",
                    "[".date('Y-m-d H:i:s')."] - Конец выполнение скрипта",
                    "===============================================",
                    $Message,
                ];
                break;
            }
        }
        return $result;
    }

    private function WebHookUpdateState($accountId, $meta, $moment): array|string
    {
        $Setting = new getSettingVendorController($accountId);
        $msClient = new MsClient($Setting->TokenMoySklad);
        $udsClient = new UdsClient($Setting->companyId, $Setting->TokenUDS);
        try {
            $body = $msClient->get($meta['href']);
            $uds = $udsClient->get('https://api.uds.app/partner/v2/settings');
        } catch (BadResponseException $e) {
            return $this->returnMessage("ERROR", $moment, $e->getMessage());
        }





        return $this->returnMessage("SUCCESS", $moment, "Успешное выполнение, все данные обновлены");
    }
}
