<?php

namespace App\Services\AdditionalServices;

use App\Components\MsClient;
use App\Http\Controllers\Config\getSettingVendorController;
use DateTime;
use DateTimeInterface;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Support\Str;
use JetBrains\PhpStorm\ArrayShape;


class ImgService
{

    public function setImgUDS($urlImages, $accountId): array
    {
        $Setting = new getSettingVendorController($accountId);
        $apiKeyMs = $Setting->TokenMoySklad;
        $companyId = $Setting->companyId;
        $password = $Setting->TokenUDS;
        $imgIds = [];

        $clientMs = new MsClient($apiKeyMs);
        $images = $clientMs->get($urlImages);

        if ($images->meta->size == 0) {
            return [];
        }

        foreach ($images->rows as $image) {
            try {
                if (property_exists($image, 'meta')) {

                    $imgHref = $image->meta->downloadHref;
                    $imageType = 'image/png';

                    $responseImageUDS = $this->setUrlToUds($imageType, $companyId, $password);
                    if($responseImageUDS['code'] == 200)
                        $dataImgUds = $responseImageUDS['result'];
                    else
                        continue;

                    $urlToUDS = $dataImgUds->url;
                    $this->setImageToUds($imageType, $urlToUDS, $imgHref, $apiKeyMs);
                    $imgIds[] = $dataImgUds->imageId;
                }
            } catch (BadResponseException ) { }
        }

        return $imgIds;
    }

    public function setImgMS($product, $urls, $apiKeyMs): void
    {
        $urlProduct = $product->meta->href;
        $count = 1;
        $body = [];

        foreach ($urls as $url) {
            $content = $this->getImgContent($url);
            if (in_array($content['type'], ['image/png', 'image/jpeg'])) {
                $fileExtension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
                $body["images"][] = [
                    "filename" => $count . "." . $fileExtension,
                    "content" => $content['content'],
                ];
            }
            $count++;
        }

        $client = new MsClient($apiKeyMs);
        $client->put($urlProduct, $body);
    }

    private function getImgContent($url): array
    {
        $client = new Client();
        $res = $client->get($url);
        $content_Type = $res->getHeaderLine('Content-Type');
        $b64image = base64_encode($res->getBody()->getContents());
        return [
            "type" => $content_Type,
            "content" => $b64image,
        ];
    }

    private function setImageToUds($imgType, $url, $imageHref, $apiKeyMs): void
    {
        $clientMs = new Client([
            'headers' => [
                'Authorization' => $apiKeyMs,
                'Content-Type' => $imgType,
                'Accept-Encoding' => 'gzip',
            ]
        ]);

        $res = $clientMs->get($imageHref);
        $image = $res->getBody()->getContents();

        $opts = array(
            'http' => array(
                'method' => 'PUT',
                'header' =>
                    "Content-Type: " . $imgType . "\r\n",
                'content' => $image,
                'ignore_errors' => true
            )
        );

        $context = stream_context_create($opts);
        $result = file_get_contents($url, false, $context);
    }

    private function setUrlToUds($imgType, $companyId, $apiKey): array
    {
        $url = "https://api.uds.app/partner/v2/image-upload-url";

        $date = new DateTime();
        $uuid_v4 = Str::uuid(); //генерация уникального идентификатора версии 4 (RFC 4122)
        $timestamp = $date->format(DateTimeInterface::ATOM);
        $body = array(
            'contentType' => $imgType,
        );

        $preparedAuthValue = "Basic" . base64_encode("$companyId:$apiKey");
            
        $client = new Client([
            'headers' => [
                'Accept' => 'application/json',
                'Accept-Charset' => 'utf-8',
                'Content-Type' => 'application/json',
                'Authorization' => $preparedAuthValue,
                'X-Origin-Request-Id' => $uuid_v4,
                'X-Timestamp' => $timestamp
            ]
        ]);

        try {
            $urlRes = $client->post($url, [
                'json' => $body,
                'http_errors' => false
            ]);
            $encodedRes = $urlRes->getBody()->getContents();
            $response = json_decode($encodedRes);
            $statusCode = $urlRes->getStatusCode();

            if ($statusCode == 200) 
                $message = "Создан новый URL S3. Готово!";
            else 
                $message = "ОШИБКА: $response";

            $out["code"] = $statusCode;
            $out["result"] = $response;
            $out["message"] = $message;
            return $out;
        } catch (Exception $e){
            $out["code"] = 500;
            $out["result"] = $e;
            $out["message"] = "ОШИБКА: " . $e->getMessage();
            return $out;
        }
        
    }
}
