<?php

namespace App\Services\Operation;

use App\Components\MsClient;
use App\Components\UdsClient;
use App\Http\Controllers\Config\getSettingVendorController;
use App\Http\Controllers\getData\getSetting;
use App\Http\Controllers\mainURL;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;
use JetBrains\PhpStorm\ArrayShape;

class WidgetInfo
{
    public function getInformation($accountId, $entity, $objectId): array
    {

        $Setting = new getSettingVendorController($accountId);
        $SettingBD = app(getSetting::class)->getSendSettingOperations($accountId);
        $urlAll = new mainURL();


        $urlCounterparty = $urlAll->url_ms() . "$entity/$objectId";

        $Client = new MsClient($Setting->TokenMoySklad);
        $Client_UDS = new UdsClient($Setting->companyId, $Setting->TokenUDS);

        try {
            $BodyMC = $Client->get($urlCounterparty);
        } catch (BadResponseException $e) {
            $StatusCode = 'error';
            return ['StatusCode' => $StatusCode, 'message' => $e->getMessage(),];
        }

        $externalCode = $BodyMC->externalCode;
        $body_agentId = $Client->get($BodyMC->agent->meta->href);
        $agentId = $body_agentId;

        if (is_numeric($agentId->externalCode) && ctype_digit($agentId->externalCode) && $agentId->externalCode > 10000) {
            if (property_exists($agentId, 'phone')) $agentId = ['externalCode' => $agentId->externalCode, 'phone' => $agentId->phone, 'dontPhone' => true,];
            else  $agentId = ['externalCode' => $agentId->externalCode, 'phone' => null, 'dontPhone' => true,];
        } else {
            if (property_exists($agentId, 'phone')) {
                $phone = $this->AgentMCPhone($agentId, $Setting);
                if (mb_strlen($phone) > 14) {
                    $StatusCode = 'error';
                    $message = 'Некорректный номер телефона: ' . $agentId->phone;
                    return ['StatusCode' => $StatusCode, 'message' => $message,];
                }
                $agentId = ['externalCode' => $agentId->externalCode, 'phone' => '+' . $phone,];
            } else {
                $StatusCode = "error";
                $message = 'Отсутствует номер телефона у данного контрагента';
                return ['StatusCode' => $StatusCode, 'message' => $message,];
            }
        }

        try {
            if (is_numeric($externalCode) && ctype_digit($externalCode) && $externalCode > 10000) {
                try {
                    $goods_orders = $this->goods_orders($externalCode, $Client_UDS);
                    $StatusCode = "orders";
                    $message = $goods_orders['message'];
                } catch (BadResponseException) {
                    $data = $this->newPostOperations($Client_UDS, $externalCode, $agentId);
                    $StatusCode = "successfulOperation";
                    $message = $data['data'];
                }
            } else {


                $operation = $this->operation_to_post($Client_UDS, $externalCode, $agentId, $BodyMC, $Client, $body_agentId, $Setting, $SettingBD);
                $StatusCode = "operation";
                $message = $operation['message'];
            }
        } catch (ClientException $e) {
            $StatusCode = 'error';
            return ['StatusCode' => $StatusCode, 'message' => $e->getMessage(),];
        }

        return [
            'StatusCode' => $StatusCode,
            'message' => $message,
        ];

    }





    public function AgentMCPhone($bodyMC, getSettingVendorController $Setting): string
    {
        $phone = null;
        if (property_exists($bodyMC, 'phone')) {
            $phone = "+7" . mb_substr(str_replace('+7', '', str_replace(" ", '', $bodyMC->phone)), -10);
        } else {
            if ((int)$bodyMC->externalCode > 1000) {
                $UdsClient = new UdsClient($Setting->companyId, $Setting->TokenUDS);
                $body = $UdsClient->get('https://api.uds.app/partner/v2/customers/' . $bodyMC->externalCode);
                if ($body->phone != null) $phone = $body->phone;
            }
        }
        return $phone;
    }
    #[ArrayShape(['StatusCode' => "int", 'message' => "array"])] private function goods_orders($externalCode, UdsClient $Client_UDS): array
    {
        $UDSURL = "https://api.uds.app/partner/v2/goods-orders/";
        $body = $Client_UDS->get($UDSURL . $externalCode);
        $StatusCode = 200;
        $state = $body->state;
        $icon = "";
        if ($state == "NEW") $icon = '<i class="fa-solid fa-circle-exclamation text-primary">  <span class="text-dark">НОВЫЙ</span> </i>';
        if ($state == "COMPLETED") $icon = '<i class="fa-solid fa-circle-check text-success"> <span class="text-dark">Завершённый</span> </i>';
        if ($state == "DELETED") $icon = '<i class="fa-solid fa-circle-xmark text-danger"> <span class="text-dark">Отменённый</span> </i>';

        $message = [
            'id' => $body->id,
            'BonusPoint' => $body->purchase->cashBack,
            'points' => $body->purchase->points,
            'state' => $state,
            'icon' => $icon,
            'info' => 'Order',
        ];
        return [
            'StatusCode' => $StatusCode,
            'message' => $message,
        ];
    }
    #[ArrayShape(['status' => "bool", 'data' => "array|null"])] public function newPostOperations($ClientUDS, $externalCode, $agentId): array
    {
        $url = 'https://api.uds.app/partner/v2/operations/' . $externalCode;
        try {
            $body = $ClientUDS->get($url);
            if ($body->points < 0) $points = $body->points * -1; else $points = $body->points;
            $status = true;
            $data = [
                'id' => $body->id,
                'BonusPoint' => $this->Calc($ClientUDS, $body, $agentId),
                'points' => $points,
                'state' => "COMPLETED",
                'icon' => '<i class="fa-solid fa-circle-check text-success"> <span class="text-dark">Проведённая операция</span> </i>',
                'info' => 'Operations',
            ];
        } catch (BadResponseException) {
            $status = false;
            $data = null;
        }
        return [
            'status' => $status,
            'data' => $data,
        ];
    }
    private function operation_to_post(UdsClient $Client_UDS, $externalCode, array $agentId, mixed $BodyMC, MsClient $Client, mixed $body_agentId, getSettingVendorController $Setting, $SettingBD): array
    {
        $data = $this->newPostOperations($Client_UDS, $externalCode, $agentId);
        if ($data['status']) { $StatusCode = 200; $message = $data['data'];
        } else {
            $StatusCode = 404;
            $info_total_and_SkipLoyaltyTotal = $this->TotalAndSkipLoyaltyTotal($BodyMC, $Client);

            $availablePoints = $this->AgentMCID($body_agentId, $Client_UDS);
            $phone = $this->AgentMCPhone($body_agentId, $Setting);
            $operationsAccrue = $SettingBD->operationsAccrue;
            $operationsCancellation = $SettingBD->operationsCancellation;
            if ($SettingBD->operationsAccrue == null) $operationsAccrue = 0;
            if ($SettingBD->operationsCancellation == 1) {
                $availablePoints = 0;
            } else  $operationsCancellation = 0;

            dd($info_total_and_SkipLoyaltyTotal, $availablePoints, $phone, $operationsAccrue, $operationsCancellation, $SettingBD);
            $message = [
                'total' => $info_total_and_SkipLoyaltyTotal['total'],
                'SkipLoyaltyTotal' => $info_total_and_SkipLoyaltyTotal['SkipLoyaltyTotal'],
                'availablePoints' => $availablePoints,
                'points' => 0,
                'phone' => $phone,
                'operationsAccrue' => (int)$operationsAccrue,
                'operationsCancellation' => (int)$operationsCancellation,
            ];
        }

        return [
            'StatusCode' => $StatusCode,
            'message' => $message,
        ];
    }


    public function Calc(UdsClient $ClientUDS, $body, $agentId)
    {
        $url = 'https://api.uds.app/partner/v2/operations/calc';
        if ($agentId['phone'] != null) {
            $participant = [
                'uid' => null,
                'phone' => "+7" . mb_substr(str_replace('+7', '', str_replace(" ", '', $agentId['phone'])), -10),
            ];
        } else {
            $infoClientByExternalCode = $ClientUDS->get('https://api.uds.app/partner/v2/customers/' . $agentId['externalCode']);
            if ($infoClientByExternalCode->uid != null) {
                $participant = [
                    'uid' => $infoClientByExternalCode->uid,
                    'phone' => null,
                ];
            } else {
                $participant = [
                    'uid' => null,
                    'phone' => $infoClientByExternalCode->phone,
                ];
            }
        }

        $body = [
            'code' => null,
            'participant' => $participant,
            'receipt' => [
                'total' => $body->total,
                'points' => ($body->points * -1),
                'skipLoyaltyTotal' => null,
            ],
        ];
        try {
            return $ClientUDS->post($url, $body)->purchase->cashBack;
        } catch (BadResponseException) {
            return null;
        }
    }

    private function TotalAndSkipLoyaltyTotal($bodyOrder, $Client): array
    {
        $sum = $bodyOrder->sum / 100;
        $SkipLoyaltyTotal = 0;
        $href = $bodyOrder->positions->meta->href;
        $BodyPositions = $Client->get($href)->rows;
        //ВОЗМОЖНОСТЬ СДЕЛАТЬ КОСТОМНЫЕ НАЧИСЛЕНИЕ
        foreach ($BodyPositions as $item) {
            $url_item = $item->assortment->meta->href;
            $body = $Client->get($url_item);

            $BonusProgramm = false;
            if (property_exists($body, 'attributes')) {
                foreach ($body->attributes as $body_item) {
                    if ('Не применять бонусную программу (UDS)' == $body_item->name) {
                        $BonusProgramm = $body_item->value;
                        break;
                    }
                }
            }

            if ($BonusProgramm) {
                $price = ($item->quantity * $item->price - ($item->quantity * $item->price * ($item->discount / 100))) / 100;
                $SkipLoyaltyTotal = $SkipLoyaltyTotal + $price;
            }

        }

        return [
            'total' => $sum,
            'SkipLoyaltyTotal' => $SkipLoyaltyTotal,
        ];
    }
    public function AgentMCID($bodyMC, UdsClient $Client_UDS): int
    {

        if ((int)$bodyMC->externalCode > 1000) {
            $url_UDS = 'https://api.uds.app/partner/v2/customers/' . $bodyMC->externalCode;
            $body = $Client_UDS->get($url_UDS)->participant;
            return $body->points;
        } else {
            $result = 0;
            if (property_exists($bodyMC, 'phone')) {
                $phone = urlencode('+' . $this->phone_number($bodyMC->phone));
                $url = 'https://api.uds.app/partner/v2/customers/find?phone=' . $phone;
                try {
                    $Body = $Client_UDS->get($url)->user;
                    $result = $Body->participant->points;
                } catch (BadResponseException) {
                }
                return $result;
            }

            return $result;
        }

    }
    public function phone_number($phone): array|string|null
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if ($phone[0] == 8) $phone[0] = 7;
        return $phone;
    }
}
