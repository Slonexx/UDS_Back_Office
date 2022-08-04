<?php

namespace App\Http\Controllers\Config\Vendor;

use App\Http\Controllers\Config\Lib\AppInstanceContoller;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Config\Vendor\AppInstance;
use Illuminate\Support\Facades\Request;


class VendorEndpointController extends Controller
{
    public function Activate(Request $request)
    {
        $this->loginfo("Request", $request);

        $method = "PUT";
        $path = $_SERVER['PATH_INFO'];
        $this->loginfo("path", $path);

        $pp = explode('/', $path);
        $n = count($pp);
        $appId = $pp[$n - 2];
        $accountId = $pp[$n - 1];

        $app = AppInstanceContoller::load($appId, $accountId);
        $replyStatus = true;

        $requestBody = file_get_contents('php://input');

        $data = json_decode($requestBody);

        $appUid = $data->appUid;
        $accessToken = $data->access[0]->access_token;

        if (!$app->getStatusName()) {
            $app->accessToken = $accessToken;
            $app->status = AppInstanceContoller::SETTINGS_REQUIRED;
            $app->persist();
        }

        if (!$app->getStatusName()) {
            http_response_code(404);
        } else if ($replyStatus) {
            header("Content-Type: application/json");
            echo '{"status": "' . $app->getStatusName() . '"}';
        }

    }


    function loginfo($name, $msg) {
        global $dirRoot;
        $logDir =  public_path();
        @mkdir($logDir);
        file_put_contents($logDir . '/log.txt', date(DATE_W3C) . ' [' . $name . '] '. $msg . "\n", FILE_APPEND);
    }

    public function delete(Request $request){

        $this->loginfo("request", $request);

        $method = "DELETE";
        $path = $request->PATH_INFO;
        $this->downloadJSONFile($path);


        $pp = explode('/', $path);
        $n = count($pp);
        $appId = $pp[$n - 2];
        $accountId = $pp[$n - 1];

        $app = AppInstanceContoller::load($appId, $accountId);

        $app->delete();
        $replyStatus = false;

        if (!$app->getStatusName()) {
            http_response_code(404);
        } else if ($replyStatus) {
            header("Content-Type: application/json");
            echo '{"status": "' . $app->getStatusName() . '"}';
        }
    }
}
