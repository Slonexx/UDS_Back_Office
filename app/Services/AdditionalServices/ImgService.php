<?php

namespace App\Services\AdditionalServices;

use App\Components\MsClient;
use App\Components\UdsClient;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Str;
use Nette\Utils\DateTime;

class ImgService
{

    public function setImgUDS($urlImages,$apiKeyMs,$companyId,$password)
    {
        //$urlGood = "https://api.uds.app/partner/v2/goods/".$item_good->id;

        $imgIds = [];

        //dd($urlImages);

        $clientMs = new MsClient($apiKeyMs);
        $images = $clientMs->get($urlImages);

        foreach ($images->rows as $image){
            if(property_exists($image, 'miniature')){
                $imgHref = $image->miniature->href;
                $imageType = $image->miniature->mediaType;

                $response_Image_UDS = $this->setUrlToUds($imageType,$companyId,$password);
                $dataImgUds = json_decode($response_Image_UDS['result']);

                //dd($response_Image_UDS,$dataImgUds);

                $imageId_to_UDS = $dataImgUds->imageId;
                $url_to_UDS = $dataImgUds->url;
                $downloadImage_S3UDS = $this->setImageToUds($imageType,$url_to_UDS,$imgHref,$apiKeyMs);
                //dd($response_Image_UDS,$downloadImage_S3UDS);
                $imgIds [] = $imageId_to_UDS;
            }
        }

        //dd($imgIds);

        return $imgIds;

        /*$body = [
            "data" => [
                "photos" => $imgIds,
            ],
        ];*/

        //dd($body);

        //$client = new UdsClient($companyId,$password);
        //$client->put($urlGood,$body);
    }

    public function setImgMS($product,$urls,$apiKeyMs)
    {
        //dd($product);
        $urlProduct = $product->meta->href;
        $count = 1;
        $body = [];

        foreach ($urls as $url){
            $content = $this->getImgContent($url);
            if ($content['type'] == 'image/png'){
                $body["images"][] = [
                    "filename" => $count.".png",
                    "content" => $content['content'],
                ];
            }
            elseif ($content['type'] == 'image/jpeg'){
                $body["images"][] = [
                    "filename" => $count.".jpeg",
                    "content" => $content['content'],
                ];
            }
            $count++;
        }

        $client = new MsClient($apiKeyMs);
        $client->put($urlProduct,$body);
    }

    private function getImgContent($url)
    {
        //$url = "https://thumbor.uds.app/IFr7jeq2K4arj2Xf5GKk_K2QLeA=/game-prod/549755819292/GOODS/c8df7baf-3abe-49ff-8d58-f378a8d9d7b3";
        //$url = "https://www.codeproject.com/KB/GDI-plus/ImageProcessing2/img.jpg";
        $client = new Client();
        $res = $client->get($url,["stream" => true]);
        $content_Type = $res->getHeaderLine('Content-Type');
        $b64image =base64_encode($res->getBody()->getContents());
        return [
            "type" => $content_Type,
            "content" => $b64image,
        ];
    }

    private function setImageToUds($imgType,$url, $imageHref, $apiKeyMs)
    {
        /*$opts = array(
            'http' => array(
                'method' => 'GET',
                'header' =>
                    "Content-Type: application/json\r\n" .
                    "Authorization: Basic ".$apiKeyMs."\r\n" ,
                'content' => $imageHref,
                'ignore_errors' => true
            )
        );

        $context = stream_context_create($opts);
        $image = file_get_contents($imageHref, false, $context);*/

        //dd($image);

        $clientMs = new Client([
            'headers' => [
                'Authorization' => $apiKeyMs,
                'Content-Type' => 'application/json',
            ]
        ]);

        $res = $clientMs->get($imageHref);
        //dd($res->getBody()->getContents());
        $image = $res->getBody()->getContents();


        $opts = array(
            'http' => array(
                'method' => 'PUT',
                'header' =>
                    "Content-Type: ".$imgType."\r\n" ,
                'content' => $image,
                'ignore_errors' => true
            )
        );

        //dd($image);

        $context = stream_context_create($opts);
        $result = file_get_contents($url,false, $context);

        preg_match('/([0-9])\d+/',$http_response_header[0],$matches);
        $responsecode = intval($matches[0]);

        //dd($responsecode);

        if ($responsecode == 200){
            $message = "Image sent to UDS";
        } else {
            $message = " 0_0 Error $responsecode";
        }

        $out["message"] = $message;
        $out["code"] = $responsecode;

        return $out;
    }

    private function setUrlToUds($img_type,$companyId,$apiKey)
    {
        $url = "https://api.uds.app/partner/v2/image-upload-url";
        //$companyId = "549755819292";
        //$apiKey = "YTI1Y2Y1MjItMzA3Ny00ZjFjLTllMDAtNzdjZDVhZmI0N2Q4";

        $date = new DateTime();
        $uuid_v4 = Str::uuid(); //generate universally unique identifier version 4 (RFC 4122)
        $itemData = json_encode(
            array(
                'contentType' => $img_type,
            )
        );

        $opts = array(
            'http' => array(
                'method' => 'POST',
                'header' => "Accept: application/json\r\n" .
                    "Accept-Charset: utf-8\r\n" .
                    "Content-Type: application/json\r\n" .
                    "Authorization: Basic ". base64_encode("$companyId:$apiKey")."\r\n" .
                    "X-Origin-Request-Id: ".$uuid_v4."\r\n" .
                    "X-Timestamp: ".$date->format(DateTime::ATOM),
                'content' => $itemData,
                'ignore_errors' => true
            )
        );

        $context = stream_context_create($opts);
        $result = file_get_contents($url, false, $context);

        //dd($context,$result);

        preg_match('/([0-9])\d+/',$http_response_header[0],$matches);
        $responsecode = intval($matches[0]);

        if ($responsecode == 200) {
            $message = "Creat new URL S3. Ready!";
        } else { $message = "ERROR: $responsecode";}

        $out["code"] = $responsecode;
        $out["result"] = $result;
        $out["message"] = $message;
        return $out;
    }

}