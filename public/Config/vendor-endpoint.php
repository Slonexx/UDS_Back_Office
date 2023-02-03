<?php
require_once 'lib.php';
$method = $_SERVER['REQUEST_METHOD'];

$url = ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$pp = explode('/', $url);
$n = count($pp);
$appId = $pp[$n - 2];
$accountId = $pp[$n - 1];



$app = AppInstanceContoller::load($appId, $accountId);
$replyStatus = true;

switch ($method) {
    case 'PUT':
        $requestBody = file_get_contents('php://input');

        $data = json_decode($requestBody);

        $appUid = $data->appUid;
        $accessToken = $data->access[0]->access_token;

        if (!$app->getStatusName()) {
            $app->TokenMoySklad = $accessToken;
            $app->status = AppInstanceContoller::SETTINGS_REQUIRED;
            $app->persist();

            $url = 'https://smartuds.kz/api/install/'.$accountId;
            $install = file_get_contents($url);
        }

        break;
    case 'GET':
        break;
    case 'DELETE':
        $url = 'https://smartuds.kz/DeleteVendorApi/'.$accountId;
        $install = file_get_contents($url);
        $replyStatus = false;
        break;
}

if (!$app->getStatusName()) {
    http_response_code(404);
} else if ($replyStatus) {
    header("Content-Type: application/json");
    echo '{"status": "' . $app->getStatusName() . '"}';
}


