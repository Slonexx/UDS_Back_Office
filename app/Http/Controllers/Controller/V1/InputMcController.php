<?php

namespace App\Http\Controllers\Controller\V1;

use App\Components\ImportDateHttpClient;
use App\Http\Controllers\Controller;
use App\Models\goods;
use Nette\Utils\DateTime;

class InputMcController extends Controller
{

    function inputJsonMc(){

        //https://online.moysklad.ru/api/remap/1.2/entity/variant?search=ТОВАРЫЫЫ
        //

        $token = "";
        $login = "sergey@smart_demo";
        $password = "Aa1234!!";
        $base_url = 'entity/product';


        $OUM_description = ["Штука","Сантиметр","Метр","Миллиметр", "Литр; кубический дециметр", "Грамм", "Килограмм"];
        $OUM_description_to_UDS = ["PIECE","CENTIMETRE","METRE","MILLILITRE", "LITRE", "GRAM", "KILOGRAM"];


            $service = new ImportDateHttpClient($login, $password,$base_url);
            $response = $service->client->request('GET', '');
            $date = json_decode($response->getBody());

            $rows = $date->rows;

                foreach ($rows as $rows_item){
                    //Обнуление данных
                    $idProduct = $rows_item->id;
                    $price = 0;
                    $offerPrice_type = "";
                    $offerSkipLoyalty = false;
                    $offerPrice = 0;
                    $article = 0;
                    $description = "";
                    $imageId_to_UDS = "";
                    $imageHref = "";
                    $UOM = "";
                    $productFolder_Name = "";
                    $stock = 0;

                    //получение цены
                     $salePrices_index = $rows_item->salePrices;
                    //Временно так
                     $index = 0;
                     foreach ($salePrices_index as $salePrices_item){

                         //Проверка на Акционный ли товар и бонусную программу
                         $attributes_index = $rows_item->attributes;
                         $offerPrice_type = false;
                         $offerSkipLoyalty = false;
                         foreach ($attributes_index as $attributes_item){
                             if ($attributes_item->name == "Акционный товар (UDS)"){
                                 $offerPrice_type = $attributes_item->value;
                             }
                             if ($attributes_item->name == "Не применять бонусную программу (UDS)"){
                                 $offerSkipLoyalty = $attributes_item->value;
                             }
                         }

                         //Ошибка о том что цены нету
                         if ($salePrices_item->value < 100){
                             $value_price = 100;
                         } else $value_price = 0;

                         if ($index == 0){
                             //$externalCode = $salePrices_item->priceType->externalCode; Внешний ключ
                             $price = $salePrices_item->value / 100 + $value_price;
                         }
                             if ($salePrices_item->priceType->name == "Акционная цена (UDS)") {
                                 if($offerPrice_type == true) {
                                     if ($salePrices_item->value > 0)
                                         $offerPrice = $salePrices_item->value / 100 + $value_price;
                                        //ПРОВЕРКА НА ТО ЧТОБ АКЦИОННАЯ ЦЕНА БЫЛА МЕНЬШЕ ОБЫЧНОЙ ЦЕНЫ!!!
                                 }
                                 else $offerPrice = 0;
                             }

                         $index = $index+1;
                     }

                    //Получение остатка
                    $GET_URL_FILTER_ID = "entity/assortment?filter=id=$idProduct";

                    $service_get_url_filter = new ImportDateHttpClient($login, $password,$GET_URL_FILTER_ID);
                    $response_get_url_filter = $service_get_url_filter->client->request('GET', '');
                    $json_body_get_url_filter = json_decode($response_get_url_filter->getBody());
                    $rows_get_url_filter = $json_body_get_url_filter->rows;
                    foreach ($rows_get_url_filter as $rows_get_url_filter_item){
                        $stock = $rows_get_url_filter_item->quantity;
                    }


                    //Проверка на артикул
                    if(property_exists($rows_item, 'article') == true) $article = $rows_item->article; else  $article = "";
                    //Проверка на описание
                    if(property_exists($rows_item, 'description') == true) $description = $rows_item->description; else  $description = "";

                    //Получение картинки
                    $images_index =  $rows_item->images;
                    foreach ($images_index as $images_item){
                        $hrefImage = $images_item->href;
                        $hrefImage_final = substr($hrefImage, 41); //Не точное!!!!
                        $ImageService = new ImportDateHttpClient($login, $password, $hrefImage_final);
                        $ImageResponse = $ImageService->client->request('GET', '');
                             $dateImage = json_decode($ImageResponse->getBody());
                             $ImageRows = $dateImage->rows;
                              foreach ($ImageRows as $ImageRows_item){
                                  $imageHref = "";
                                  if(property_exists($ImageRows_item, 'miniature') == true) {
                                      $imageHref = $ImageRows_item->miniature->href;
                                      $Response_Image_UDS = $this->URLToUds();

                                      $Date_Response_Image_UDS = json_decode($Response_Image_UDS["Result"]);
                                      $imageId_to_UDS = $Date_Response_Image_UDS->imageId;
                                      $url_to_UDS = $Date_Response_Image_UDS->url;

                                      $DownloadImage_S3UDS = $this->ImageToUds($login, $password, $url_to_UDS, $imageHref);
                                  }
                              }
                    }

                    if(property_exists($rows_item, 'uom') == true) {
                        $UOM_index = $rows_item->uom;
                        foreach($UOM_index as $UOM_item){
                            $hrefUOM = $UOM_item->href;
                            $OUMhref = substr($hrefUOM, 41);
                            $UOMService = new ImportDateHttpClient($login, $password, $OUMhref);
                            $UOMResponse = $UOMService->client->request('get', '');
                            $dste_OUM = json_decode($UOMResponse->getBody());
                            $boolOUM_check = false;
                            $indexOUM_description_index = 0;
                            foreach ($OUM_description as $OUM_description_index) {
                                if ($OUM_description_index == $dste_OUM->description) {
                                    $boolOUM_check = true;
                                    $UOM = $OUM_description_to_UDS[$indexOUM_description_index];
                                }
                                $indexOUM_description_index++;
                            }
                            if ($boolOUM_check == false){
                                $UOM = $OUM_description_to_UDS[0];
                            }
                        }
                    }
                    else  $UOM = "";

                    if(property_exists($rows_item, 'productFolder') == true) {
                        $productFolder_index = $rows_item->productFolder;
                        foreach ($productFolder_index as $productFolder_item){
                            $productFolderHref = $productFolder_item->href;
                            $productFolderHref_final = substr($productFolderHref, 41);
                            $productFolderService = new ImportDateHttpClient($login, $password, $productFolderHref_final);
                            $productFolderResponse = $productFolderService->client->request('get', '');
                            $date_productFolder = json_decode($productFolderResponse->getBody());

                            //Попробовать категории которые могут быть несколько !!!!!!!!!!!!!!!!!!!!!!!
                            $productFolder_Name = $date_productFolder->name;
                            $productFolder_externalCode = $date_productFolder->externalCode;
                        }
                    }
                    else $productFolder_Name = "";

                //Занесение в базу для чека///
                     $product = goods::firstOrCreate([
                          'id_MC' => $idProduct,
                      ],[
                          'id_MC' => $idProduct,
                          'name' => $rows_item->name,
                          'stock' => $stock,
                          'price' => $price,
                          'offerPrice_type' => $offerPrice_type,
                          'offerSkipLoyalty' => $offerSkipLoyalty,
                          'offerPrice' => $offerPrice,
                          'article' => $article,
                          'description' => $description,
                          'photos' => $imageHref,
                          'measurement' => $UOM,
                          'type_CATEGORY' => $productFolder_Name,
                      ]);




                    $CreatProductUDS = $this->toUds($rows_item->name, $rows_item->id, $imageId_to_UDS, $price, $description , $UOM);

                   // $message = $this->toUds($rows_item->name, $rows_item->id, $imageId_to_UDS, $price, $description , $UOM);

                    /*if ($offerPrice_type == true){

                     }*/
                //Отправка на UDS

                 }


                return response()->json(
                [
                   // "message" => "the base has been moved",
                    "New URL S3 for image " => array(
                        "Message" => $Response_Image_UDS["Message"],
                        "Status code" => $Response_Image_UDS["Status code"],
                        ),
                    "Image UDS info" => array(
                        "Message" => $DownloadImage_S3UDS["Message"],
                        "Status code" => $DownloadImage_S3UDS["Status code"],
                        ),
                    "UDS_CREAT_PRODUCT" => array(
                        "Status code" => $CreatProductUDS["Status code"],
                        "Message" => $CreatProductUDS["Message"],
                    )
                ],201);

    }


    public function URLToUds(){
        $url = "https://api.uds.app/partner/v2/image-upload-url";
        $companyId = "549755819292";
        $apiKey = "YTI1Y2Y1MjItMzA3Ny00ZjFjLTllMDAtNzdjZDVhZmI0N2Q4";

        $date = new DateTime();
        $uuid_v4 = 'UUID'; //generate universally unique identifier version 4 (RFC 4122)
        $itemData = json_encode(
            array(
                'contentType' => "image/jpeg",
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

        preg_match('/([0-9])\d+/',$http_response_header[0],$matches);
        $responsecode = intval($matches[0]);

        if ($responsecode == 200) {
            $message = "Creat new URL S3. Ready!";
        } else { $message = "ERROR: $responsecode";}

        $out["Status code"] = $responsecode;
        $out["Result"] = $result;
        $out["Message"] = $message;
        return $out;
    }

    public function ImageToUds($login, $password, $url, $imageHref){

        $opts = array(
            'http' => array(
                'method' => 'GET',
                'header' =>
                    "Content-Type: application/json\r\n" .
                    "Authorization: Basic ". base64_encode("$login:$password")."\r\n" ,
                'content' => $imageHref,
                'ignore_errors' => true
            )
        );

        $context = stream_context_create($opts);
        $image = file_get_contents($imageHref, false, $context);

        $opts = array(
            'http' => array(
                'method' => 'PUT',
                'header' =>
                    "Content-Type: image/jpeg\r\n" ,
                'content' => $image,
                'ignore_errors' => true
            )
        );

        $context = stream_context_create($opts);
        $result = file_get_contents($url,false, $context);
        preg_match('/([0-9])\d+/',$http_response_header[0],$matches);
        $responsecode = intval($matches[0]);


        if ($responsecode == 200){
            $message = "Image sent to UDS";
        } else {
            $message = " 0_0 Error $responsecode";
        }

        $out["Message"] = $message;
        $out["Status code"] = $responsecode;

        return $out;
    }

    public function toUds($name_uds, $name_externalId, $imageId_to_UDS, $price_uds, $description_uds, $measurement_uds){
        $url = "https://api.uds.app/partner/v2/goods";
        $companyId = "549755819292";
        $apiKey = "YTI1Y2Y1MjItMzA3Ny00ZjFjLTllMDAtNzdjZDVhZmI0N2Q4";

        $date = new DateTime();
        $uuid_v4 = 'UUID'; //generate universally unique identifier version 4 (RFC 4122)

        $itemData = json_encode(
            array(
                'name' => $name_uds,
                'externalId' => $name_externalId,
                'data' => array(
                    'type' => 'ITEM',
                    'photos' => array($imageId_to_UDS
                    ),
                    'price' => $price_uds,
                    'description' => $description_uds,
                    'measurement' => $measurement_uds,
                )
            )
        );

        $opts = array(
            'http' => array(
                'method' => 'POST',
                'header' => "Accept: application/json\r\n" .
                    "Accept-Charset: utf-8\r\n" .
                    "Content-Type: application/json\r\n" .
                    "Authorization: Basic ". base64_encode("$companyId:$apiKey")."\r\n" .
                    "Content-Type: application/json\r\n" .
                    "X-Origin-Request-Id: ".$uuid_v4."\r\n" .
                    "X-Timestamp: ".$date->format(DateTime::ATOM),
                'content' => $itemData,
                'ignore_errors' => true
            )
        );

        $context = stream_context_create($opts);
        $result = file_get_contents($url, false, $context);

        preg_match('/([0-9])\d+/',$http_response_header[0],$matches);
        $responsecode = intval($matches[0]);

        if ($responsecode == 200) {
            $message = "The product was created in UDS";
        } else { $message = json_decode($result);}

        $out["Status code"] = $responsecode;
        $out["Message"] = $message;
        return $out;
    }


}
