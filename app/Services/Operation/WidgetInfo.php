<?php

namespace App\Services\Operation;

use App\Components\MsClient;
use App\Components\UdsClient;
use App\Http\Controllers\Config\getSettingVendorController;
use App\Http\Controllers\getData\getSetting;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Config;

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

        $urlCounterparty = Config::get("Global.entity") . "$entity/$objectId?expand=agent,positions.assortment" ;
        try {
            $BodyMC = $this->msClient->get($urlCounterparty);
        } catch (BadResponseException $e) {
            return ['StatusCode' => 'error', 'message' => $e->getMessage()];
        }

        $externalCode = $BodyMC->externalCode;
        $agentId = $BodyMC->agent;


        if ($this->setting->operationsAccrue == '0') {
            if (is_numeric($agentId->externalCode) && ctype_digit($agentId->externalCode) && $agentId->externalCode > 10000) {
                $phone = $this->AgentMCPhone($agentId);
                $agentId = [ 'externalCode' => $agentId->externalCode, 'phone' => $phone, 'dontPhone' => true ];
            }
            else {
                if (property_exists($agentId, 'phone')) {
                    $phone = $this->AgentMCPhone($agentId);
                    if (mb_strlen($phone) > 14) return [ 'StatusCode' => 'error', 'message' => 'Некорректный номер телефона: ' . $agentId->phone ];
                    $agentId = ['externalCode' => $agentId->externalCode, 'phone' => $phone,];
                }
                else {
                    return ['StatusCode' => 'error', 'message' => 'Отсутствует номер телефона у данного контрагента'];
                }
            }
        }

        if ($entity == 'salesreturn' and property_exists($BodyMC, 'demand')) {
            $demand = $this->msClient->get($BodyMC->demand->meta->href);
            if (is_numeric($demand->externalCode) && ctype_digit($demand->externalCode) && $demand->externalCode > 10000) {
                $externalCode = $demand->externalCode;
            } elseif (property_exists($demand, 'customerOrder')) {
                $customerOrder = $this->msClient->get($demand->customerOrder->meta->href);
                if (is_numeric($customerOrder->externalCode) && ctype_digit($customerOrder->externalCode) && $customerOrder->externalCode > 10000) {
                    $externalCode = $customerOrder->externalCode;
                } else  return ['StatusCode' => 'error', 'message' => 'У данного документа отсутствуют связанные документы на которых была операция'];
            }
        }
        elseif ($entity == 'salesreturn') return ['StatusCode' => 'error', 'message' => 'У данного документа отсутствуют связанные документы на которых была операция'];





        try {
            if (is_numeric($externalCode) && ctype_digit($externalCode) && $externalCode > 10000) {
                $body = $this->udsClient->newGET("https://api.uds.app/partner/v2/goods-orders/" . $externalCode);

                if ($body->status) {
                    $goods_orders = $this->goods_orders($body->data, $externalCode);
                    $StatusCode = 'orders';
                    $message = $goods_orders['message'];
                } else {
                    $data = $this->newPostOperations($externalCode, $agentId, $BodyMC);
                    $StatusCode = 'successfulOperation';
                    $message = $data;
                }
            } else {
                $StatusCode = 'operation';
                $message = $this->operation_to_post($agentId, $BodyMC);
            }
        }
        catch (ClientException $e) {
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
                $body = $this->udsClient->newGET('https://api.uds.app/partner/v2/customers/' . $bodyMC->externalCode);
                //dd($body);
                if ($body->status) $phone = $body->data->phone;
            }
        }
        return $phone;
    }

    private function goods_orders(mixed $body, mixed $externalCode): array
    {

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
        $body = $this->udsClient->newGET('https://api.uds.app/partner/v2/operations/' . $externalCode);
        if (!$body->status) return [ 'status' => false, 'data' => null ];
        $body = $body->data;

        $points = 0;
        $BonusPoint = 0;
        if ($body->points < 0) $points = $body->points * -1;
        else foreach ($BodyMC->attributes as $item){
            if ($item->name == "Количество списанных баллов (UDS)") $points = $item->value;
            if ($item->name == "Количество начисленных баллов (UDS)") $BonusPoint = $item->value;
        }


        $parts = explode("=", $BodyMC->externalCode);
        if (count($parts) > 1) $result = end($parts); else $result = 0;

        $total = $body->total - $result;

        if ($BonusPoint <= 0) $this->Calc($body, $agentId);

        //dd($body);

        return [
            'status' => true,
            'data' => [
                'id' => $body->id,
                'BonusPoint' => $BonusPoint,
                'points' => $points,
                'total' => $total,
                'state' => "COMPLETED",
                'icon' => '<i class="fa-solid fa-circle-check text-success"> <span class="text-dark">Проведённая операция</span> </i>',
                'info' => 'Operations',
            ],
        ];
    }

    private function operation_to_post($agentId, mixed $BodyMC): array
    {
        $info_total_and_SkipLoyaltyTotal = $this->TotalAndSkipLoyaltyTotal($BodyMC);
        if ($this->setting->operationsAccrue == '0') {
            $infoCustomers = $this->AgentMCID($agentId);
            if ($infoCustomers  === null) {
                $availablePoints = null;
                $uid = null;
                $phone = null;
            } else {
                $availablePoints = $infoCustomers->participant->points;
                $uid = $infoCustomers->uid;
                $phone = $agentId['phone'];
            }
        } else {
            $availablePoints = null;
            $uid = null;
            $phone = null;
        }


        $operationsAccrue = (int) $this->setting->operationsAccrue ?? 0;
        $operationsCancellation = (int) $this->setting->operationsCancellation?? 0;
        return [
            'total' => $info_total_and_SkipLoyaltyTotal['total'],
            'SkipLoyaltyTotal' => $info_total_and_SkipLoyaltyTotal['SkipLoyaltyTotal'],
            'availablePoints' => $availablePoints,
            'points' => 0,
            'phone' => $phone,
            'uid' =>  $uid,
            'operationsAccrue' => $operationsAccrue,
            'operationsCancellation' => $operationsCancellation,
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

        //dd($bodyOrder);

        $sum = $bodyOrder->sum / 100;
        $SkipLoyaltyTotal = 0;
        $BodyPositions = $bodyOrder->positions->rows;

        foreach ($BodyPositions as $item) {
            $body = $item->assortment;
            $BonusProgramm = false;

            if (property_exists($body, 'attributes')) {
                foreach ($body->attributes as $body_item) {
                    if ('Не применять бонусную программу (UDS)' == $body_item->name) $BonusProgramm = $body_item->value;

                    if ('Процент начисления (UDS)' == $body_item->name) {
                        $minPrice = 0;
                        if (property_exists($body, "minPrice")) { $minPrice = $body->minPrice->value; }
                        if ($this->setting->customOperation == 1) { $BonusProgramm = $body_item->value;
                        } elseif ($this->setting->customOperation == 0) {
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

    public function AgentMCID($AgentForPhoneAndCode)
    {

        if (is_numeric($AgentForPhoneAndCode['externalCode']) && ctype_digit($AgentForPhoneAndCode['externalCode']) && $AgentForPhoneAndCode['externalCode'] > 10000) {
            $url_UDS = 'https://api.uds.app/partner/v2/customers/' . $AgentForPhoneAndCode['externalCode'];
            $body = $this->udsClient->newGET($url_UDS);
            if ($body->status) return $body->data;
            else {
                $e164PhoneNumber = str_replace('+', '', $AgentForPhoneAndCode['phone']); // Удаляем символ "+"
                $urlEncodedPhoneNumber = urlencode('%2b' . $e164PhoneNumber);
                $url = 'https://api.uds.app/partner/v2/customers/find?phone=' . $urlEncodedPhoneNumber;
                $body = $this->udsClient->newGET($url);
                if ($body->status) return $body->data->user;
                else return null;
            }
        } else {
            $e164PhoneNumber = str_replace('+', '', $AgentForPhoneAndCode['phone']); // Удаляем символ "+"
            $urlEncodedPhoneNumber = urlencode('%2b' . $e164PhoneNumber);
            $url = 'https://api.uds.app/partner/v2/customers/find?phone=' . $urlEncodedPhoneNumber;
            $body = $this->udsClient->newGET($url);
            if ($body->status) return $body->data->user;
            else return null;
        }

    }
}
