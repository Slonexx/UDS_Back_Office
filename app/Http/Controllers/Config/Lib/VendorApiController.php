<?php

namespace App\Http\Controllers\Config\Lib;

use App\Http\Controllers\Controller;

use \Firebase\JWT\JWT;

require_once 'jwt.lib.php';


class VendorApiController extends Controller
{
    function context(string $contextKey) {
        return $this->request('POST', '/context/' . $contextKey);
    }

    function updateAppStatus(string $appId, string $accountId, string $status) {
        return $this->request('PUT',
            "/apps/$appId/$accountId/status",
            "{\"status\": \"$status\"}");
    }

    private function request(string $method, $path, $body = null) {

        $cfg = new cfg();

        return makeHttpRequest(
            $method,
            $cfg->moyskladVendorApiEndpointUrl . $path,
            buildJWT(),
            $body);
    }

}
function makeHttpRequest(string $method, string $url, string $bearerToken, $body = null) {
    $opts = $body
        ? array('http' =>
            array(
                'method'  => $method,
                'header'  => array('Authorization: Bearer ' . $bearerToken, "Content-type: application/json"),
                'content' => $body
            )
        )
        : array('http' =>
            array(
                'method'  => $method,
                'header'  => 'Authorization: Bearer ' . $bearerToken
            )
        );
    $context = stream_context_create($opts);
    $result = file_get_contents($url, false, $context);
    return json_decode($result);
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
