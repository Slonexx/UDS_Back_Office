<?php

namespace App\Http\Controllers;

use App\Components\MsClient;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Psr\Http\Message\ResponseInterface;
use Illuminate\Support\Facades\Http;

class TestController extends Controller
{
    public function test(Request $request){

        $request->validate([
            "tokenMs" => 'required|string',
            "companyId" => "required|string",
            "apiKeyUds" => "required|string",
        ]);
        $apiKey = $request->tokenMs;
        $apiKeyUds = $request->apiKeyUds;
        $companyId = $request->companyId;

        // $urlProduct = "https://online.moysklad.ru/api/remap/1.2/entity/product?search=TestMod";
        // $client = new MsClient($apiKey);
        // $json = $client->get($urlProduct);
        // $productMeta = $json->rows[0]->meta;

        // $nameModify = "color";
        // $character = "red";
        
        // app(ModifyProductController::class)->createModifyProductMs($productMeta,$nameModify,$character,$apiKey);

        // $count = app(StockController::class)->getProductStockMs("TestMod",$apiKey);
        // return $count;

        $json = app(AgentController::class)->getAgentUds($companyId, $apiKeyUds);
        return $json;
    }

}
