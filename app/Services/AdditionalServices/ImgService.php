<?php

namespace App\Services\AdditionalServices;

use App\Components\MsClient;
use DateTime;
use DateTimeInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use JetBrains\PhpStorm\ArrayShape;

class ImgService
{

    public function setImgUDS($urlImages,$apiKeyMs,$companyId,$password): array
    {
        //$urlGood = "https://api.uds.app/partner/v2/goods/".$item_good->id;

        $imgIds = [];

        $clientMs = new MsClient($apiKeyMs);
        $images = $clientMs->get($urlImages);

        foreach ($images->rows as $image){
            try {
                if(property_exists($image, 'meta')){

                    $imgHref = $image->meta->downloadHref;
                    $imageType = 'image/png';

                    $response_Image_UDS = $this->setUrlToUds($imageType,$companyId,$password);
                    $dataImgUds = json_decode($response_Image_UDS['result']);

                    $url_to_UDS = $dataImgUds->url;
                    $this->setImageToUds($imageType,$url_to_UDS,$imgHref,$apiKeyMs);
                    $imgIds [] = $dataImgUds->imageId;
                }
            } catch (\Throwable $e){
                Storage::disk('local')->put('Error_to_S3_Image.txt',$url_to_UDS."                                                    \r\n". $e);
                dd($e->getMessage());
            }
        }
        /*dd($imgIds);*/
        return $imgIds;
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

    #[ArrayShape(["type" => "string", "content" => "string"])] private function getImgContent($url): array
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

    /**
     * @throws GuzzleException
     */
    private function setImageToUds($imgType, $url, $imageHref, $apiKeyMs)
    {
        $clientMs = new Client();

        $res = $clientMs->get($imageHref,[
            'headers' => [
                'Authorization' => $apiKeyMs,
                'Content-Type' => 'application/json',
            ]
        ]);
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

        $context = stream_context_create($opts);
        $result = file_get_contents($url,false, $context);


        /*$client = new Client();
        $res = $client->put($url,[
            'headers'=> ['Content-Type' => "image/png"],
            'form_params' => [ $image ]
        ]);*/
    }

    private function setUrlToUds($img_type,$companyId,$apiKey): array
    {
        $url = "https://api.uds.app/partner/v2/image-upload-url";

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
                    "X-Timestamp: ".$date->format(DateTimeInterface::ATOM),
                'content' => $itemData,
                'ignore_errors' => true
            )
        );

        $context = stream_context_create($opts);
        $result = file_get_contents($url, false, $context);

        //dd($context,$result);

        preg_match('/([0-9])\d+/',$http_response_header[0],$matches);
        $response = intval($matches[0]);

        if ($response == 200) {
            $message = "Creat new URL S3. Ready!";
        } else { $message = "ERROR: $response";}

        $out["code"] = $response;
        $out["result"] = $result;
        $out["message"] = $message;
        return $out;
    }

}
