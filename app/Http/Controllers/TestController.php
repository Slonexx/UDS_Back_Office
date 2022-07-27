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
        ]);
        $apiKey = $request->tokenMs;

        $urlProduct = "https://online.moysklad.ru/api/remap/1.2/entity/product?search=TestMod";
        $client = new MsClient($apiKey);
        $json = $client->get($urlProduct);
        $productMeta = $json->rows[0]->meta;

        $nameModify = "color";
        $characters = ["red", "blue", "white", "dark"];
        
        app(ModifyProductController::class)->createModifyProductMs($productMeta,$nameModify,$characters,$apiKey);

    }

}
