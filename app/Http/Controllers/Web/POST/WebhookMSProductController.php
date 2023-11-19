<?php

namespace App\Http\Controllers\Web\POST;

use App\Http\Controllers\Controller;
use App\Services\WebhookMS\WebHookProduct;
use App\Services\WebhookMS\WebHookProductFolder;
use App\Services\WebhookMS\WebHookStock;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Http\Request;

class WebhookMSProductController extends Controller
{
    private WebHookProduct $WebHookProduct;
    private WebHookProductFolder $WebHookProductFolder;
    private WebHookStock $WebHookStock;

    public function __construct()
    {
        $this->WebHookProduct = new WebHookProduct();
        $this->WebHookProductFolder = new WebHookProductFolder();
        $this->WebHookStock = new WebHookStock();
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

    function productUpdate(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            return response()->json([
                'code' => 200,
                'message' => $this->WebHookProduct->initiation($request['events']),
            ]);
        } catch (BadResponseException $e){
            return response()->json([
                'code' => 203,
                'message' => $this->returnMessage("ERROR", $request['auditContext']['moment'], $e->getMessage()),
            ]);
        }

    }

    function productFolderUpdate(Request $request): \Illuminate\Http\JsonResponse
    {

        try {
            return response()->json([
                'code' => 200,
                'message' => $this->WebHookProductFolder->initiation($request['events']),
            ]);
        } catch (BadResponseException $e){
            return response()->json([
                'code' => 203,
                'message' => $this->returnMessage("ERROR", $request['auditContext']['moment'], $e->getMessage()),
            ]);
        }

    }

    function productStock(Request $request): \Illuminate\Http\JsonResponse
    {

        try {
            return response()->json([
                'code' => 'by Slonex',
                'message' => $this->WebHookStock->initiation($request['accountId'], $request['reportUrl']),
            ]);
        } catch (BadResponseException $e){
            return response()->json([
                'code' => 503,
                'message' => $this->returnMessage("ERROR", $request['auditContext']['moment'], $e->getMessage()),
            ]);
        }

    }
}
