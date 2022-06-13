<?php

namespace App\Http\Controllers\Controller\V1;

use App\Components\ImportDateHttpClient;
use App\Http\Controllers\Controller;
use App\Models\goods;

class InputMcController extends Controller
{

    function inputJsonMc(){

        $token = "";
        $login = "sergey@smart_demo";
        $password = "Aa1234!!";

        $offerPrice = 0;
        $price = "";


            $service = new ImportDateHttpClient();
            $response = $service->client->request('GET', '');
            $date = json_decode($response->getBody());

            $rows = $date->rows;
            $next = "";
                 foreach ($rows as $rows_item){
                    // $name = $row->name;
                     $salePrices_index = $rows_item->salePrices;
                     $attributes_index = $rows_item->attributes;
                     $offerPrice_type = false;
                     foreach ($attributes_index as $attributes_item){
                         if ($attributes_item->name == "Акционный товар (UDS)"){
                             $offerPrice_type = true;
                         }
                     }

                    //Временно так
                     $index = 0;
                     foreach ($salePrices_index as $salePrices_item){
                         if ($index == 0){
                             //$externalCode = $salePrices_item->priceType->externalCode; Внешний ключ
                             $price = $salePrices_item->value/100;
                         }
                             if ($salePrices_item->priceType->name == "Акционная цена (UDS)") {
                                 if($salePrices_item->value > 0 )
                                 $offerPrice = $salePrices_item->value / 100;
                                 else $offerPrice = 0;
                             }

                         $index = $index+1;
                     }

                     $product = goods::firstOrCreate([
                          'id_MC' => $rows_item->id,
                      ],[
                          'id_MC' => $rows_item->id,
                          'name' => $rows_item->name,
                          'price' => $price,
                          'offerPrice_type' => $offerPrice_type,
                          'offerPrice' => $offerPrice,
                      ]);

                 }





          //  dd($rows);
            return response()->json(
                [
                    "message" => "the base has been moved",
                    $product
                ],201);


    }





}
