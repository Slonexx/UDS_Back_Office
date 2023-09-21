<?php

namespace App\Http\Controllers\Config\Lib;

use App\Http\Controllers\Controller;

use \Firebase\JWT\JWT;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

require_once 'jwt.lib.php';

class VendorApiController extends Controller
{
    function context(string $contextKey)
    {
        return $this->request('POST', '/context/' . $contextKey);
    }

    function updateAppStatus(string $appId, string $accountId, string $status)
    {

        return $this->request('PUT',
            "/apps/$appId/$accountId/status",
            ["status" => $status]);
    }

    private function request(string $method, $path, $body = null)
    {
        $url = (new cfg())->moyskladVendorApiEndpointUrl . $path;
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

    $cfg = new cfg();

    $token = array(
        "sub" => $cfg->appUid,
        "iat" => time(),
        "exp" => time() + 300,
        "jti" => bin2hex(random_bytes(32))
    );
    return JWT::encode($token, $cfg->secretKey);
}






