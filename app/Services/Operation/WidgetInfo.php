<?php

namespace App\Services\Operation;

use App\Components\MsClient;
use App\Components\UdsClient;
use App\Http\Controllers\Config\getSettingVendorController;
use App\Http\Controllers\getData\getSetting;
use App\Http\Controllers\mainURL;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;

class WidgetInfo
{

    private getSettingVendorController $settingVendorController;
    private mixed $setting;
    private MsClient $msClient;
    private UdsClient $udsClient;

    public function getInformation($accountId, $entity, $objectId): array
    {

        $this->settingVendorController = new getSettingVendorController($accountId);
        $this->setting = (new getSetting())->getSendSettingOperations($accountId);
        $this->msClient = new MsClient($this->settingVendorController->TokenMoySklad);
        $this->udsClient = new UdsClient($this->settingVendorController->companyId, $this->settingVendorController->TokenUDS);

        $urlAll = new mainURL();
        $urlCounterparty = $urlAll->url_ms() . "$entity/$objectId";
        try {
            $BodyMC = $this->msClient->get($urlCounterparty);
        } catch (BadResponseException $e) {
            return ['StatusCode' => 'error', 'message' => $e->getMessage()];
        }

        $externalCode = $BodyMC->externalCode;
        $body_agentId = $this->msClient->get($BodyMC->agent->meta->href);
        $agentId = $body_agentId;

        if (is_numeric($agentId->externalCode) && ctype_digit($agentId->externalCode) && $agentId->externalCode > 10000) {
            $agentId = [
                'externalCode' => $agentId->externalCode,
                'phone' => property_exists($agentId, 'phone') ? $agentId->phone : null,
                'dontPhone' => true,
            ];
        } else {
            if (property_exists($agentId, 'phone')) {
                $phone = $this->AgentMCPhone($agentId);
                if (mb_strlen($phone) > 14) {
                    return [
                        'StatusCode' => 'error',
                        'message' => 'Некорректный номер телефона: ' . $agentId->phone,
                    ];
                }
                $agentId = [
                    'externalCode' => $agentId->externalCode,
                    'phone' => '+' . $phone,
                ];
            } else {
                return [
                    'StatusCode' => 'error',
                    'message' => 'Отсутствует номер телефона у данного контрагента',
                ];
            }
        }

        try {
            if (is_numeric($externalCode) && ctype_digit($externalCode) && $externalCode > 10000) {
                try {
                    $goods_orders = $this->goods_orders($externalCode);
                    $StatusCode = 'orders';
                    $message = $goods_orders['message'];
                } catch (BadResponseException) {
                    $data = $this->newPostOperations($externalCode, $agentId, $BodyMC);
                    $StatusCode = 'successfulOperation';
                    $message = $data['data'];
                }
            } else {
                $operation = $this->operation_to_post($externalCode, $agentId, $BodyMC, $body_agentId);
                $StatusCode = 'operation';
                $message = $operation['message'];
            }
        } catch (ClientException $e) {
            return ['StatusCode' => 'error', 'message' => $e->getMessage()];
        }

        return [
            'StatusCode' => $StatusCode,
            'message' => $message,
        ];

    }


    public function AgentMCPhone(mixed $bodyMC): string
    {
        $phone = null;
        if (property_exists($bodyMC, 'phone')) {
            $phone = "+7" . mb_substr(str_replace('+7', '', str_replace(" ", '', $bodyMC->phone)), -10);
        } else {
            if ((int)$bodyMC->externalCode > 1000) {
                $UdsClient = new UdsClient($this->settingVendorController->companyId, $this->settingVendorController->TokenUDS);
                $body = $UdsClient->get('https://api.uds.app/partner/v2/customers/' . $bodyMC->externalCode);
                if ($body->phone != null) $phone = $body->phone;
            }
        }
        return $phone;
    }

    private function goods_orders(mixed $externalCode): array
    {
        $UDSURL = "https://api.uds.app/partner/v2/goods-orders/";
        $body =  $this->udsClient->get($UDSURL . $externalCode);
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

    public function newPostOperations(mixed $externalCode, mixed $agentId, mixed $BodyMC): array
    {
        $url = 'https://api.uds.app/partner/v2/operations/' . $externalCode;
        try {
            $body = $this->udsClient->get($url);
            if ($body->points < 0) $points = $body->points * -1; else {
                foreach ($BodyMC->attributes as $item){
                    if ($item->name == "Количество списанных баллов (UDS)") {
                        $points = $item->value;
                    }
                }
            }

            $status = true;
            $data = [
                'id' => $body->id,
                'BonusPoint' => $this->Calc($body, $agentId),
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

    private function operation_to_post(mixed $externalCode, array $agentId, mixed $BodyMC, mixed $body_agentId): array
    {
        $data = $this->newPostOperations($externalCode, $agentId, $BodyMC);

        if ($data['status']) {
            $StatusCode = 200;
            $message = $data['data'];
        } else {
            $StatusCode = 404;
            $info_total_and_SkipLoyaltyTotal = $this->TotalAndSkipLoyaltyTotal($BodyMC);
            $availablePoints = $this->AgentMCID($body_agentId);
            $phone = $this->AgentMCPhone($body_agentId);
            $operationsAccrue = (int) $this->setting->operationsAccrue ?? 0;
            $operationsCancellation = (int) $this->setting->operationsCancellation == 1 ? 0 : 0;

            $message = [
                'total' => $info_total_and_SkipLoyaltyTotal['total'],
                'SkipLoyaltyTotal' => $info_total_and_SkipLoyaltyTotal['SkipLoyaltyTotal'],
                'availablePoints' => $availablePoints,
                'points' => 0,
                'phone' => $phone,
                'operationsAccrue' => $operationsAccrue,
                'operationsCancellation' => $operationsCancellation,
            ];
        }

        return [
            'StatusCode' => $StatusCode,
            'message' => $message,
        ];
    }

    public function Calc($body, $agentId)
    {
        $url = 'https://api.uds.app/partner/v2/operations/calc';
        if ($agentId['phone'] != null) {
            $participant = [
                'uid' => null,
                'phone' => "+7" . mb_substr(str_replace('+7', '', str_replace(" ", '', $agentId['phone'])), -10),
            ];
        } else {
            $infoClientByExternalCode = $this->udsClient->get('https://api.uds.app/partner/v2/customers/' . $agentId['externalCode']);
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
            return $this->udsClient->post($url, $body)->purchase->cashBack;
        } catch (BadResponseException) {
            return null;
        }
    }

    private function TotalAndSkipLoyaltyTotal(mixed $bodyOrder): array
    {
        $sum = $bodyOrder->sum / 100;
        $SkipLoyaltyTotal = 0;
        $href = $bodyOrder->positions->meta->href;
        $BodyPositions = $this->msClient->get($href)->rows;
        //ВОЗМОЖНОСТЬ СДЕЛАТЬ КОСТОМНЫЕ НАЧИСЛЕНИЕ
        foreach ($BodyPositions as $item) {
            $url_item = $item->assortment->meta->href;
            $body =  $this->msClient->get($url_item);

            $BonusProgramm = false;
            if (property_exists($body, 'attributes')) {
                foreach ($body->attributes as $body_item) {
                    if ('Не применять бонусную программу (UDS)' == $body_item->name) {
                        $BonusProgramm = $body_item->value;
                    }
                    if ('Процент начисления (UDS)' == $body_item->name) {
                        $minPrice = 0;
                        if (property_exists($body, "minPrice")) { $minPrice = $body->minPrice->value; }
                        if ($this->setting->customOperation == 1) {
                            $BonusProgramm = $body_item->value;
                        } else {
                            if ($body_item->value < 100) {
                                $SkipLoyaltyTotalSum = (($item->price - ($item->price * $body_item->value / 90)) / 100);
                                $SkipLoyaltyTotal = $SkipLoyaltyTotal + round($SkipLoyaltyTotalSum, 2);
                            } else $SkipLoyaltyTotal = $SkipLoyaltyTotal + ($item->price - $minPrice) / 100;
                        }

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

    public function AgentMCID($bodyMC): int
    {

        if ((int)$bodyMC->externalCode > 1000) {
            $url_UDS = 'https://api.uds.app/partner/v2/customers/' . $bodyMC->externalCode;
            $body = $this->udsClient->get($url_UDS)->participant;
            return $body->points;
        } else {
            $result = 0;
            if (property_exists($bodyMC, 'phone')) {
                $phone = urlencode('+' . $this->phone_number($bodyMC->phone));
                $url = 'https://api.uds.app/partner/v2/customers/find?phone=' . $phone;
                try {
                    $Body = $this->udsClient->get($url)->user;
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
