<?php

namespace App\Services\AdditionalServices;

use App\Components\MsClient;
use App\Http\Controllers\Config\getSettingVendorController;
use DateTime;
use DateTimeInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Support\Str;
use JetBrains\PhpStorm\ArrayShape;


class ImgService
{
    private const TIMEOUT = 20; // Максимальное время ожидания ответа в секундах

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
                    $dataImgUds = json_decode($responseImageUDS['result']);

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

    #[ArrayShape(["type" => "string", "content" => "string"])]
    private function getImgContent($url): array
    {
        $client = new Client();
        $res = $client->get($url, ["stream" => true, "timeout" => self::TIMEOUT]);
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

        $res = $clientMs->get($imageHref, ["timeout" => self::TIMEOUT]);
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
        $itemData = json_encode(
            array(
                'contentType' => $imgType,
            )
        );

        $opts = array(
            'http' => array(
                'method' => 'POST',
                'header' => "Accept: application/json\r\n" .
                    "Accept-Charset: utf-8\r\n" .
                    "Content-Type: application/json\r\n" .
                    "Authorization: Basic " . base64_encode("$companyId:$apiKey") . "\r\n" .
                    "X-Origin-Request-Id: " . $uuid_v4 . "\r\n" .
                    "X-Timestamp: " . $date->format(DateTimeInterface::ATOM),
                'content' => $itemData,
                'ignore_errors' => true,
                'timeout' => self::TIMEOUT
            )
        );

        $context = stream_context_create($opts);
        $result = file_get_contents($url, false, $context);

        preg_match('/([0-9])\d+/', $http_response_header[0], $matches);
        $response = intval($matches[0]);

        if ($response == 200) {
            $message = "Создан новый URL S3. Готово!";
        } else {
            $message = "ОШИБКА: $response";
        }

        $out["code"] = $response;
        $out["result"] = $result;
        $out["message"] = $message;
        return $out;
    }
}
