<?php

namespace App\Http\Controllers\Config\Lib;

use App\Http\Controllers\Controller;

use GuzzleHttp\Client;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Config;

require_once 'jwt.lib.php';

class VendorApiController extends Controller
{
    function context(string $contextKey)
    {
        return $this->request('POST', '/context/' . $contextKey);
    }

    function updateAppStatus(string $accountId, string $status)
    {
        $appId = Config::get("Global.appId");
        return $this->request('PUT',
            "/apps/$appId/$accountId/status",
            ["status" => $status]);
    }

    private function request(string $method, $path, $body = null)
    {
        $url =  Config::get("Global.VendorEndpoint") . $path;
        $bearerToken = buildJWT();
        $client = new Client();
        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . $bearerToken,
                'Accept-Encoding' => 'gzip',
                'Content-type' => 'application/json'
            ]
        ];

        if ($body !== null) {
            $options['json'] = $body;
        }

        $response = $client->request($method, $url, $options);
        return json_decode($response->getBody()->getContents());


    }
}
function buildJWT(): string
{
    $token = array(
        "sub" =>  Config::get("Global.appUid"),
        "iat" => time(),
        "exp" => time() + 300,
        "jti" => bin2hex(random_bytes(32))
    );
    return JWT::encode($token,  Config::get("Global.secretKey"));
}






