<?php

namespace App\Http\Controllers;

use App\Components\MsClient;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function getProductStockMs($nameProduct,$apiKey)
    {
        $url = "https://online.moysklad.ru/api/remap/1.2/report/stock/all";
        $client = new MsClient($apiKey);
        $json = $client->get($url);
        $count = 0;
        foreach($json->rows as $row){
            if($row->name == $nameProduct){
                $count = $row->stock;
                break;
            }
        }
        return $count;
    }
}
