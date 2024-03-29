<?php

namespace App\Http\Controllers\BackEnd;

use App\Components\MsClient;
use App\Components\UdsClient;
use App\Http\Controllers\Config\getSettingVendorController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\GuzzleClient\ClientMC;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Http\Request;

class Salesreturn extends Controller
{
    public function SalesreturnObject($accountId, $entity, $objectId)
    {
        $Setting = new getSettingVendorController($accountId);
        $ClientUDS = new UdsClient($Setting->companyId, $Setting->TokenUDS);

        $url = 'https://api.moysklad.ru/api/remap/1.2/entity/salesreturn/' . $objectId;
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
            $OLD_partialAmount = ((int)substr(strrchr($MAINBody->externalCode, "="), 1));

            try {
                $bodyUDS = $ClientUDS->get('https://api.uds.app/partner/v2/operations/' . $externalCode);
            } catch (\Throwable $e) {
                if (!property_exists($bodyDemand, 'customerOrder')) {
                    $return = [
                        'Status' => 400,
                        'Data' => 'У данного документа нету связных документов отгрузки',
                    ];
                } else {
                    $href = $bodyDemand->customerOrder->meta->href;
                    $ClientMC->setRequestUrl($href);
                    $customerOrder = $ClientMC->requestGet();
                    $externalCode = $customerOrder->externalCode;
                }
            }

            try {
                $bodyUDS = $ClientUDS->get('https://api.uds.app/partner/v2/operations/' . $externalCode);
                $procent = ($bodyUDS->total - ($bodyUDS->total - $OLD_partialAmount)) * 100 / $bodyUDS->total;
                $return = [
                    'Status' => 200,
                    'Data' => [
                        'id' => $bodyUDS->id,
                        'points' => ($bodyUDS->points * -1) - (($bodyUDS->points * -1) * $procent / 100),
                        'cash' => $bodyUDS->cash,
                        'total' => ((int)$bodyUDS->total - $OLD_partialAmount),
                    ],
                ];
            } catch (\Throwable $e) {
                $return = [
                    'Status' => (int)$e->getCode(),
                    'Data' => $e->getMessage(),
                ];
            }
        };
        return response()->json($return);
    }

    public function sReturn(Request $request)
    {
        $data = $request->validate([
            "accountId" => 'required|string',
            "objectId" => 'required|string',
            "return_id" => 'required|string',
            "partialAmount" => "required|string",
        ]);

        $Setting = new getSettingVendorController($data['accountId']);
        $ClientMC = new MsClient($Setting->TokenMoySklad);
        $url = 'https://api.moysklad.ru/api/remap/1.2/entity/salesreturn/' . $data['objectId'];
        $BodyMC = $ClientMC->get($url);
        $OLD_partialAmount = ((int)substr(strrchr($BodyMC->externalCode, "="), 1));
        $partialAmount = ((int)$data['partialAmount']) + $OLD_partialAmount;
        $externalCode = $BodyMC->externalCode . '=' . $partialAmount;
        $urlUDS = 'https://api.uds.app/partner/v2/operations/' . $data['return_id'] . '/refund';
        $ClientUDS = new UdsClient($Setting->companyId, $Setting->TokenUDS);
        try {
            $bodyUDS = $ClientUDS->post($urlUDS, ['partialAmount' => $data['partialAmount']]);
            $putBody = $ClientMC->put($url, ['externalCode' => $externalCode]);
            $return = ['status' => true, 'Data' => 'Успешно'];
        } catch (BadResponseException $e) {
            $return = ['status' => false, 'Data' => $e->getResponse()->getBody()->getContents()];
        }
        return response()->json($return);
    }
}
