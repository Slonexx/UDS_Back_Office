<?php

namespace App\Services\AdditionalServices;

use App\Components\MsClient;
use App\Http\Controllers\Config\getSettingVendorController;
use DateTime;
use DateTimeInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Str;


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
            } catch (GuzzleException) {
            }
        }

        dd($imgIds);

        return $imgIds;
    }

    /**
     * @throws GuzzleException
     */
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

    /**
     * @throws GuzzleException
     */
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

    /**
     * @throws GuzzleException
     */
    private function setImageToUds($imgType, $url, $imageHref, $apiKeyMs): void
    {
        $clientMs = new Client([
            'headers' => [
                'Authorization' => $apiKeyMs,
                'Content-Type' => $imgType,
                'Accept-Encoding' => 'gzip',
            ]
        ]);

        $res = $clientMs->get($imageHref, ['http_errors' => false]);
        $statusCode = $res->getStatusCode();
        $image = $res->getBody()->getContents();

        if($statusCode == 200){
            $clientUds = new Client([
                'headers' => [
                    'Content-Type' => $imgType,
                ]
            ]);
            $res = $clientUds->put($url, [
                'json' => $image,
                'http_errors' => false
            ]);
        }



    }

    private function setUrlToUds($imgType, $companyId, $apiKey): array
    {
        $url = "https://api.uds.app/partner/v2/image-upload-url";

        $date = new DateTime();
        $uuid_v4 = Str::uuid(); //генерация уникального идентификатора версии 4 (RFC 4122)
        $timestamp = $date->format(DateTimeInterface::ATOM);
        $body = (object) [ 'contentType' => $imgType ];


        $client = new Client([
            'headers' => [
                'Accept' => 'application/json',
                'Accept-Charset' => 'utf-8',
                'Content-Type' => 'application/json',
                'Authorization' => ['Basic '. base64_encode($companyId.':'.$apiKey)],
                'X-Origin-Request-Id' => $uuid_v4->toString(),
                'X-Timestamp' => $timestamp
            ]
        ]);

        try {
            $urlRes = $client->post($url, [
                'json' => $body,
            ]);
            $encodedRes = $urlRes->getBody()->getContents();
            $response = json_decode($encodedRes);
            $statusCode = $urlRes->getStatusCode();

            $out["code"] = $statusCode;
            $out["result"] = $response;
            $out["message"] = "Создан новый URL S3. Готово!";;
            return $out;
        } catch (GuzzleException $e){
            $out["code"] = $e->getCode();
            $out["result"] = $e;
            $out["message"] = "ОШИБКА: " . $e->getMessage();
            return $out;
        }
    }
}
