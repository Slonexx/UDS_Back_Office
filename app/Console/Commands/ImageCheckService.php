<?php

namespace App\Console\Commands;

use App\Components\MsClient;
use App\Components\UdsClient;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\BackEnd\BDController;
use App\Http\Controllers\Config\getSettingVendorController;
use App\Models\counterparty_add;
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
        $client = new Client();
        $headers = [
            'Accept' => 'application/json',
            'Authorization' => 'Basic NTQ5NzU1ODE5MjkyOk0yWTVNV1U1Tm1VdE5EY3dNaTAwTXpjNExUaGtOall0TkdZM05XUXdNV0ptTnpBMQ=='
        ];
        $options = [
            'multipart' => [
                [
                    'name' => 'accountId',
                    'contents' => '1dd5bd55-d141-11ec-0a80-055600047495'
                ],
                [
                    'name' => 'tokenMs',
                    'contents' => '320a1fdf9222b8f40c968d0df757a30165a2b9fe'
                ],
                [
                    'name' => 'companyId',
                    'contents' => '549755819292'
                ],
                [
                    'name' => 'apiKeyUds',
                    'contents' => 'ZjRkYjgzYjktNjIzNy00OGY1LTg1YmMtMTU1YjRhMWFlZTk0'
                ],
                [
                    'name' => 'folder_id',
                    'contents' => '0'
                ],
                [
                    'name' => 'store',
                    'contents' => 'МАГАЗИН'
                ]
            ]];
        $request = new Request('POST', 'https://smartuds.kz/api/productUds', $headers);
        $res = $client->sendAsync($request, $options)->wait();
        dd($res);
    }
}
