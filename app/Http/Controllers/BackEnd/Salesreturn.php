<?php

namespace App\Http\Controllers\BackEnd;

use App\Components\UdsClient;
use App\Http\Controllers\Config\getSettingVendorController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\GuzzleClient\ClientMC;
use Illuminate\Http\Request;

class Salesreturn extends Controller
{
    public function SalesreturnObject($accountId, $entity, $objectId){
       $Setting = new getSettingVendorController($accountId);

       $url = 'https://online.moysklad.ru/api/remap/1.2/entity/salesreturn/'.$objectId;
       $ClientMC = new ClientMC($url, $Setting->TokenMoySklad);
       $MAINBody = $ClientMC->requestGet();

       if (!property_exists($MAINBody, 'demand')) {
           $return = [
               'Status' => 400,
               'Data' => 'У данного документа нету связных документов отгрузки',
           ];
       } else {
           $href = $MAINBody->demand->meta->href;
           $ClientMC->setRequestUrl($href);
           $bodyDemand = $ClientMC->requestGet();
           $externalCode = $bodyDemand->externalCode;

           $ClientUDS = new UdsClient($Setting->companyId, $Setting->TokenUDS);
           try {
               $bodyUDS = $ClientUDS->get('https://api.uds.app/partner/v2/operations/'.$externalCode);
               $return = [
                   'Status' => 200,
                   'Data' => [
                       'id' => $bodyUDS->id,
                       'points' => $bodyUDS->points,
                       'cash' => $bodyUDS->cash,
                       'total' => $bodyUDS->total,
                   ],
               ];
           } catch (\Throwable $e) {
               $return = [
                   'Status' => (int) $e->getCode(),
                   'Data' => $e->getMessage(),
               ];
           }
       };
    return response()->json($return);
    }
}
