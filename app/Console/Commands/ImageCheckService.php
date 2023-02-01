<?php

namespace App\Console\Commands;

use App\Components\MsClient;
use App\Components\UdsClient;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\BackEnd\BDController;
use App\Http\Controllers\Config\getSettingVendorController;
use App\Models\counterparty_add;
use App\Services\AdditionalServices\ImgService;
use GuzzleHttp\Client;
use GuzzleHttp\Promise\EachPromise;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Console\Command;

class ImageCheckService extends Command
{

    protected $signature = 'ImageCheckService:start';

    protected $description = 'Command ImageCheckService';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $imgIds = app(ImgService::class)->setImgUDS(
        "https://online.moysklad.ru/api/remap/1.2/entity/product/80ca390b-97b4-11ed-0a80-0721008c6f63/images",
        "320a1fdf9222b8f40c968d0df757a30165a2b9fe",
        "549755819292",
        "ZjRkYjgzYjktNjIzNy00OGY1LTg1YmMtMTU1YjRhMWFlZTk0",
        );
        dd($imgIds);
        $client = new Client(['base_uri' => 'https://smartuds.kz/api/updateProductUds']);
        $res = $client->post('',[
            'headers'=> ['Accept' => 'application/json'],
            'form_params' => [
                "accountId" => "1dd5bd55-d141-11ec-0a80-055600047495",
                "tokenMs" => "320a1fdf9222b8f40c968d0df757a30165a2b9fe",
                "companyId" => "549755819292",
                "apiKeyUds" => "ZjRkYjgzYjktNjIzNy00OGY1LTg1YmMtMTU1YjRhMWFlZTk0",
                "folder_id" => "0",
                "store" => "МАГАЗИН",
            ]
        ]);
        dd($res);
    }
}
