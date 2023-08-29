<?php

namespace App\Services\Operation;

use App\Components\MsClient;
use App\Components\UdsClient;
use App\Http\Controllers\Config\getSettingVendorController;
use App\Http\Controllers\getData\getSetting;
use App\Http\Controllers\GuzzleClient\ClientMC;
use App\Http\Controllers\mainURL;
use App\Http\Controllers\Web\RewardController;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;

class sendOperations
{
    public function initiation($data): array
    {
        if (strlen(str_replace(' ', '', $data['user'])) > 6) {
            $data['code'] = null;
            $data['phone'] = '+7' . str_replace(" ", '', str_replace("+7", '', $data['user']));
        } else {
            $data['code'] = $data['user'];
            $data['phone'] = null;
        }

        if ($data['receipt_points'] == "undefined") {
            $data['receipt_points'] = '0';
        }
        if ($data['receipt_skipLoyaltyTotal'] == "undefined" || $data['receipt_skipLoyaltyTotal'] == "0") {
            $data['receipt_skipLoyaltyTotal'] = null;
        }

        $url = 'https://api.uds.app/partner/v2/operations';
        $Setting = new getSettingVendorController($data['accountId']);
        $SettingBD = (new getSetting())->getSendSettingOperations($data['accountId']);
        $Client = new UdsClient($Setting->companyId, $Setting->TokenUDS);

        $receipt_points = $SettingBD->customOperation == 1 ? "0" : $data['receipt_points'];

        $body = [
            'code' => $data['code'],
            'participant' => [
                'uid' => null,
                'phone' => $data['phone'],
            ],
            'nonce' => null,
            'cashier' => [
                'externalId' => $data['cashier_id'],
                'name' => $data['cashier_name'],
            ],
            'receipt' => [
                'total' => (float) $data['receipt_total'],
                'cash' => (float) $data['receipt_total'] - $receipt_points,
                'points' => (float) $receipt_points,
                'number' => null,
                'skipLoyaltyTotal' => $data['receipt_skipLoyaltyTotal'],
            ],
            'tags' => null,
        ];

        $post = json_decode(json_encode($Client->post($url, $body)), true);
        if ( $post['points'] < 0 ) $post['points'] = -$post['points'];
        if ($SettingBD->customOperation == 1) {
            $post['points'] = $data['receipt_cash'];
            (new RewardController())->Accrue($data['accountId'], $data['cashBack'], $post->customer->id);
            (new RewardController())->Cancellation($data['accountId'], $data['receipt_cash'], $post->customer->id);
        }

        $post = json_decode(json_encode($post));

        $ClientMC = new MsClient($Setting->TokenMoySklad);
        $OldBody = $ClientMC->get('https://online.moysklad.ru/api/remap/1.2/entity/' . $data['entity'] . '/' . $data['objectId']);

        $setPositions = $this->Positions($post, $data['receipt_skipLoyaltyTotal'], $OldBody, $Setting);
        $setAttributes = $this->Attributes($data, $post, $Setting);

        $OldBody->externalCode = $post->id;

        $putBody = $ClientMC->put('https://online.moysklad.ru/api/remap/1.2/entity/' . $data['entity'] . '/' . $data['objectId'], [
            'externalCode' => (string) $post->id,
            'positions' => $setPositions,
            'attributes' => $setAttributes,
        ]);
        if ($data['entity'] == 'customerorder') {
            $this->createDemands($Setting, $SettingBD, $putBody, (string) $post->id);
        }
        $this->createPaymentDocument($Setting, $SettingBD, $putBody);

        return [
            'code' => 200,
            'id' => $post->id,
            'points' => $post->points,
            'total' => $post->total,
            'message' => 'The operation was successful',
        ];
    }

    public function Positions($postUDS, $skipLoyaltyTotal, $OldBody, $Setting): array
    {
        $Positions = [];
        $ClientMCPositions = new ClientMC($OldBody->positions->meta->href, $Setting->TokenMoySklad);
        $OldPositions = $ClientMCPositions->requestGet()->rows;

        $sumMC = $OldBody->sum - $postUDS->points * 100;
        $pointsPercent = $sumMC > 0 ? ($postUDS->points * 100) / ($OldBody->sum) * 100 : 0;
        foreach ($OldPositions as $item) {
            $Positions[] = [
                'id' => $item->id,
                'accountId' => $item->accountId,
                'quantity' => $item->quantity,
                'price' => $item->price,
                'discount' => $pointsPercent,
                'vat' => $item->vat,
                'vatEnabled' => $item->vatEnabled,
                'assortment' => $item->assortment,
                'reserve' => 0,
            ];
        }
        return $Positions;
    }

    public function Attributes($data, $postUDS, $Setting): array
    {
        $url = 'https://online.moysklad.ru/api/remap/1.2/entity/' . $data['entity'] . '/metadata/attributes';
        $Client = new ClientMC($url, $Setting->TokenMoySklad);
        $metadata = $Client->requestGet()->rows;
        $Attributes = [];

        foreach ($metadata as $item) {
            if ($item->name == "Списание баллов (UDS)" && ($postUDS->points * -1) > 0) {
                $Attributes[] = [
                    'meta' => [
                        'href' => $item->meta->href,
                        'type' => $item->meta->type,
                        'mediaType' => $item->meta->mediaType,
                    ],
                    'value' => true,
                ];
            } elseif ($item->name == "Начисление баллов (UDS)" && $postUDS->cash > 0) {
                $Attributes[] = [
                    'meta' => [
                        'href' => $item->meta->href,
                        'type' => $item->meta->type,
                        'mediaType' => $item->meta->mediaType,
                    ],
                    'value' => true,
                ];
            } elseif ($item->name == "Количество списанных баллов (UDS)"){
                $Attributes[] = [
                    'meta' => [
                        'href' => $item->meta->href,
                        'type' => $item->meta->type,
                        'mediaType' => $item->meta->mediaType,
                    ],
                    'value' => (float) $postUDS->points,
                ];
            }
            else continue;
        }
        return $Attributes;
    }

    public function createDemands($Setting, $SettingBD, $OldBody, $externalCode): void
    {
        if ($SettingBD->operationsDocument != 0 && $SettingBD->operationsDocument != null) {
            $client = new MsClient($Setting->TokenMoySklad);
            $attributes = [];
            $positions = [];
            $attributesValue = [];
            $Store = $Setting->Store;
            $bodyStore = $client->get('https://online.moysklad.ru/api/remap/1.2/entity/store?filter=name=' . $Store)->rows;
            $Store = $bodyStore[0]->id;
            $bodyAttributes = $client->get("https://online.moysklad.ru/api/remap/1.2/entity/demand/metadata/attributes/")->rows;

            foreach ($OldBody->attributes as $item) {
                $attributesValue[$item->name] = [
                    'value' => $item->value,
                ];
            }
            foreach ($bodyAttributes as $item) {
                $attributeValue = $attributesValue[$item->name]['value'];
                $attributes[] = [
                    'meta' => [
                        'href' => $item->meta->href,
                        'type' => $item->meta->type,
                        'mediaType' => $item->meta->mediaType,
                    ],
                    'value' => $attributeValue,
                ];
            }
            $hrefPositions = $OldBody->positions->meta->href;
            $bodyPositions = $client->get($hrefPositions)->rows;

            foreach ($bodyPositions as $id => $item) {
                $positions[$id] = [
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'discount' => $item->discount,
                    'vat' => $item->vat,
                    'assortment' => ['meta' => [
                        'href' => $item->assortment->meta->href,
                        'type' => $item->assortment->meta->type,
                        'mediaType' => $item->assortment->meta->mediaType,
                    ]],
                ];
            }

            $url = 'https://online.moysklad.ru/api/remap/1.2/entity/demand';
            $body = [
                'organization' => ['meta' => [
                    'href' => $OldBody->organization->meta->href,
                    'type' => $OldBody->organization->meta->type,
                    'mediaType' => $OldBody->organization->meta->mediaType,
                ]],
                'agent' => ['meta' => [
                    'href' => $OldBody->agent->meta->href,
                    'type' => $OldBody->agent->meta->type,
                    'mediaType' => $OldBody->agent->meta->mediaType,
                ]],
                'store' => ['meta' => [
                    'href' => 'https://online.moysklad.ru/api/remap/1.2/entity/store/' . $Store,
                    'type' => 'store',
                    'mediaType' => 'application/json',
                ]],
                'externalCode' => $externalCode,
                'attributes' => $attributes,
                'positions' => $positions,
                'customerOrder' => [
                    'meta' => [
                        'href' => $OldBody->meta->href,
                        'metadataHref' => $OldBody->meta->metadataHref,
                        'type' => $OldBody->meta->type,
                        'mediaType' => $OldBody->meta->mediaType,
                        'uuidHref' => $OldBody->meta->uuidHref,
                    ],
                ],
            ];

            try {
                $postBodyCreateDemand = $client->post($url, $body);

                if ($SettingBD->operationsDocument == '2' || $SettingBD->operationsDocument == 2) {
                    $body = [
                        'demands' => [
                            0 => [
                                'meta' => [
                                    'href' => $postBodyCreateDemand->meta->href,
                                    'metadataHref' => $postBodyCreateDemand->meta->metadataHref,
                                    'type' => $postBodyCreateDemand->meta->type,
                                    'mediaType' => $postBodyCreateDemand->meta->mediaType,
                                ],
                            ],
                        ],
                    ];

                    $urlFacture = 'https://online.moysklad.ru/api/remap/1.2/entity/factureout';
                    $client = new MsClient($Setting->TokenMoySklad);
                    $client->post($urlFacture, $body);
                }
            } catch (BadResponseException) {
                // Handle exception
            }
        }
    }

    public function createPaymentDocument($Setting, $SettingBD, $OldBody): void
    {
        if ($SettingBD->operationsPaymentDocument == 0 || $SettingBD->operationsPaymentDocument == null) {
            return;
        }

        $client = new MsClient($Setting->TokenMoySklad);
        $url = '';
        $body = [
            'organization' => ['meta' => [
                'href' => $OldBody->organization->meta->href,
                'type' => $OldBody->organization->meta->type,
                'mediaType' => $OldBody->organization->meta->mediaType,
            ]],
            'agent' => ['meta' => [
                'href' => $OldBody->agent->meta->href,
                'type' => $OldBody->agent->meta->type,
                'mediaType' => $OldBody->agent->meta->mediaType,
            ]],
            'sum' => $OldBody->sum,
            'operations' => [
                0 => [
                    'meta' => [
                        'href' => $OldBody->meta->href,
                        'metadataHref' => $OldBody->meta->metadataHref,
                        'type' => $OldBody->meta->type,
                        'mediaType' => $OldBody->meta->mediaType,
                        'uuidHref' => $OldBody->meta->uuidHref,
                    ],
                    'linkedSum' => 0,
                ],
            ],
        ];

        if ($SettingBD->operationsPaymentDocument == 1 || $SettingBD->operationsPaymentDocument == "1") {
            $url = 'https://online.moysklad.ru/api/remap/1.2/entity/cashin';
        }

        if ($SettingBD->operationsPaymentDocument == 2) {
            $url = 'https://online.moysklad.ru/api/remap/1.2/entity/paymentin';
        }

        $client->post($url, $body);
    }
}
