<?php

namespace App\Services\Operation;

use App\Components\MsClient;
use App\Components\UdsClient;
use App\Http\Controllers\Config\getSettingVendorController;
use App\Http\Controllers\getData\getSetting;
use App\Http\Controllers\mainURL;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;

class OperationsCalc
{
    public function initiation($data)
    {
        $SettingBD = app(getSetting::class)->getSendSettingOperations($data['accountId']);

        $data['code'] = strlen(str_replace(' ', '', $data['user'])) > 6 ? null : $data['user'];
        $data['phone'] = strlen(str_replace(' ', '', $data['user'])) > 6 ? $data['user'] : null;

        $data['SkipLoyaltyTotal'] = $data['SkipLoyaltyTotal'] == '0' ? null : $data['SkipLoyaltyTotal'];

        $Setting = new getSettingVendorController($data['accountId']);
        $ClientMS = new MsClient($Setting->TokenMoySklad);
        $unredeemableTotal = $this->unredeemableTotal($ClientMS, $data['entity_type'], $data['object_Id']);

        $Client = new UdsClient($Setting->companyId, $Setting->TokenUDS);
        $body = [
            'code' => $data['code'],
            'participant' => [
                'uid' => null,
                'phone' => $data['phone'],
            ],
            'receipt' => [
                'total' => $data['total'],
                'points' => $data['points'],
                'skipLoyaltyTotal' => $data['SkipLoyaltyTotal'],
                'unredeemableTotal' => $unredeemableTotal,
            ],
        ];

        if ($body['receipt']['unredeemableTotal'] == null) {
            unset($body['receipt']['unredeemableTotal']);
        }
        //dd($body);
        $postBody = $Client->post('https://api.uds.app/partner/v2/operations/calc', $body);

        if (property_exists($postBody, 'purchase')) {
            if ($SettingBD->customOperation == 1) {
                $postBody->purchase->cashBack = $this->customOperation($ClientMS, $data['entity_type'], $data['object_Id'], 'accrue');
                $postBody->purchase->maxPoints = $this->customOperation($ClientMS, $data['entity_type'], $data['object_Id'], 'cancellation');
            }
            return $postBody->purchase;
        } else {
            return ['Status' => "", 'Message' => "Ошибка попробуйте позже"];
        }
    }

    private function unredeemableTotal(MsClient $ClientMS, mixed $entity_type, mixed $object_Id)
    {
        $bodyOrder = $ClientMS->get('https://api.moysklad.ru/api/remap/1.2/entity/' . $entity_type . '/' . $object_Id);
        $unredeemableTotal = null;
        $href = $bodyOrder->positions->meta->href;
        $BodyPositions = $ClientMS->get($href)->rows;

        foreach ($BodyPositions as $id => $item) {
            $url_item = $item->assortment->meta->href;
            $body = $ClientMS->get($url_item);

            $BonusProgramm = false;

            if (property_exists($body, 'attributes')) {
                foreach ($body->attributes as $body_item) {
                    if ('Не применять бонусную программу (UDS)' == $body_item->name) { $BonusProgramm = $body_item->value; }
                    if ('Процент списания (UDS)' == $body_item->name) { $minPrice = property_exists($body, "minPrice") ? $body->minPrice->value : 0;
                        if ($body_item->value < 100) {
                            $PresentBonus = (($item->price - ($item->price * $body_item->value / 100)) / 100);
                            $unredeemableTotal = $unredeemableTotal + round($PresentBonus, 2);
                        } else {
                            $unredeemableTotal = $unredeemableTotal + (($item->price - $minPrice) / 100);
                        }
                    }
                }
            }

            if ($BonusProgramm) {
                $price = ($item->quantity * $item->price - ($item->quantity * $item->price * ($item->discount / 100))) / 100;
                $unredeemableTotal = $unredeemableTotal + $price;
            }
        }

        if ($unredeemableTotal != null) {
            $unredeemableTotal = round($unredeemableTotal, 2);
        }

        return $unredeemableTotal;
    }

    private function customOperation(MsClient $ClientMS, mixed $entity_type, mixed $object_Id, string $operationType)
    {
        $bodyOrder = $ClientMS->get('https://api.moysklad.ru/api/remap/1.2/entity/' . $entity_type . '/' . $object_Id);
        $value = 0;
        $href = $bodyOrder->positions->meta->href;
        $BodyPositions = $ClientMS->get($href)->rows;

        foreach ($BodyPositions as $id => $item) {
            $url_item = $item->assortment->meta->href;
            $body = $ClientMS->get($url_item);

            if (property_exists($body, 'attributes')) {
                foreach ($body->attributes as $body_item) {
                    if ('Не применять бонусную программу (UDS)' == $body_item->name) {
                        $value = $value + 0;
                    }
                    if ($operationType == 'accrue' && 'Процент начисления (UDS)' == $body_item->name) {
                        $minPrice = property_exists($body, "minPrice") ? $body->minPrice->value : 0;

                        if ($body_item->value < 100) {
                            $valueTotal = (($item->price * $body_item->value / 100)) / 100;
                            $value = $value + $valueTotal;
                        } else {
                            $value = $value + (($item->price - $minPrice) / 100);
                        }
                    }
                    if ($operationType == 'cancellation' && 'Процент списания (UDS)' == $body_item->name) {
                        $minPrice = property_exists($body, "minPrice") ? $body->minPrice->value : 0;

                        if ($body_item->value < 100) {
                            $valueTotal = (($item->price * $body_item->value / 100)) / 100;
                            $value = $value + $valueTotal;
                        } else {
                            $value = $value + (($item->price - $minPrice) / 100);
                        }
                    }
                }
            }
        }

        return $value;
    }

}
