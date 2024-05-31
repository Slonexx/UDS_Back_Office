<?php

namespace App\Services\Operation;

use App\Components\MsClient;
use App\Components\UdsClient;
use App\Http\Controllers\Config\getSettingVendorController;
use App\Http\Controllers\getData\getSetting;
use App\Http\Controllers\GuzzleClient\ClientMC;
use App\Http\Controllers\mainURL;
use App\Http\Controllers\Web\RewardController;
use App\Models\Automation_new_update_MODEL;
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

        if ($data['receipt_points'] == "undefined") $data['receipt_points'] = '0';

        if ($data['receipt_skipLoyaltyTotal'] == "undefined" || $data['receipt_skipLoyaltyTotal'] == "0") $data['receipt_skipLoyaltyTotal'] = null;


        $url = 'https://api.uds.app/partner/v2/operations';
        $Setting = new getSettingVendorController($data['accountId']);
        $SettingBD = (new getSetting())->getSendSettingOperations($data['accountId']);
        $Client = new UdsClient($Setting->companyId, $Setting->TokenUDS);

        $receipt_points = $SettingBD->customOperation == 1 ? "0" : $data['receipt_points'];

        $body = [
            'code' => $data['code'],
            'participant' => [
                'uid' => $data['user_uid'] ?? null,
                'phone' => $data['phone'],
            ],
            'nonce' => null,
            'cashier' => [
                'externalId' => $data['cashier_id'],
                'name' => $data['cashier_name'],
            ],
            'receipt' => [
                'total' => (float)$data['receipt_total'],
                'cash' => (float)$data['receipt_total'] - $receipt_points,
                'points' => (float)$receipt_points,
                'number' => null,
                'skipLoyaltyTotal' => $data['receipt_skipLoyaltyTotal'],
            ],
            'tags' => null,
        ];

        if ($receipt_points > 0) $body['participant']['uid'] = null;

        try {
            $post = json_decode(json_encode($Client->postHttp_errorsNo($url, $body)), true);
        } catch (BadResponseException $e) {
            if ($e->getResponse()->getBody()->getContents() == '{"errorCode":"purchaseByPhoneDisabled","message":"Regular purchase via a phone number is not available"}') {
                return ['status' => false, 'message' => 'Проведение операции по номеру телефона не разрешено настройками UDS.'];
            }
            return ['status' => false, 'message' => $e->getResponse()->getBody()->getContents()];
        }
        if ($post['points'] < 0) $post['points'] = -$post['points'];

        if ($SettingBD->customOperation == 1) {
            if ($data['receipt_points'] > 0) $post['points'] = $data['receipt_points'];
            (new RewardController())->Accrue($data['accountId'], $data['cashBack'], $post['customer']['id']);
            (new RewardController())->Cancellation($data['accountId'], $data['receipt_points'], $post['customer']['id']);
        }

        $post = json_decode(json_encode($post));

        $ClientMC = new MsClient($Setting->TokenMoySklad);
        $OldBody = $ClientMC->get('https://api.moysklad.ru/api/remap/1.2/entity/' . $data['entity'] . '/' . $data['objectId'] . '?expand=positions.assortment');

        $setPositions = $this->Positions($post, $data['receipt_skipLoyaltyTotal'], $OldBody, $receipt_points);
        $setAttributes = $this->Attributes($data, $post, $Setting, $receipt_points);

        $OldBody->externalCode = $post->id;
        $putBodyEntity = [
            'externalCode' => (string)$post->id,
            'positions' => $setPositions,
            'attributes' => $setAttributes,
        ];

        if ($putBodyEntity['attributes'] == null) unset($putBodyEntity['attributes']);
        if ($putBodyEntity['positions'] == null) unset($putBodyEntity['positions']);

        $putBody = $ClientMC->newPUT('https://api.moysklad.ru/api/remap/1.2/entity/' . $data['entity'] . '/' . $data['objectId'], $putBodyEntity);
        if ($putBody->status) {
            $putBody = $putBody->data;
            if ($Setting->accountId = '9df72ee9-12a3-11ef-0a80-0f4c0000fb2b') {
                dd($data['entity'] == 'customerorder', (!property_exists($putBody, 'demands')), ($data['entity'] == 'customerorder' and (!property_exists($putBody, 'demands'))) );
            }

            if ($data['entity'] == 'customerorder' and (!property_exists($putBody, 'demands'))) {
                $this->createDemands($Setting, $SettingBD, $putBody, (string)$post->id);
            }
            if (!property_exists($putBody, 'payments')) $this->createPaymentDocument($ClientMC, $SettingBD->operationsPaymentDocument, $putBody);
        }

        return [
            'status' => true,
            'code' => 200,
            'id' => $post->id,
            'points' => $post->points,
            'total' => $post->total,
            'message' => 'The operation was successful',
        ];
    }

    public function Positions($postUDS, $skipLoyaltyTotal, $OldBody, $receipt_points): array
    {
        $Positions = [];
        $OldPositions = $OldBody->positions->rows;
        if ($postUDS->points > 0) $points = $postUDS->points;
        else $points = $receipt_points;


        $sumMC = $OldBody->sum - $points * 100;
        $pointsPercent = $sumMC > 0 ? ($points * 100) / ($OldBody->sum) * 100 : 0;
        foreach ($OldPositions as $item) {
            $Positions[] = [
                'id' => $item->id,
                'accountId' => $item->accountId,
                'quantity' => $item->quantity,
                'price' => $item->price,
                'discount' => $pointsPercent,
                'vat' => $item->vat,
                'vatEnabled' => $item->vatEnabled,
                'assortment' => ['meta' => $item->assortment->meta,],
                'reserve' => 0,
            ];
        }
        return $Positions;
    }

    public function Attributes($data, $postUDS, $Setting, $receipt_points): ?array
    {
        $url = 'https://api.moysklad.ru/api/remap/1.2/entity/' . $data['entity'] . '/metadata/attributes';

        $Client = new MsClient($Setting->TokenMoySklad);
        $metadata = $Client->newGet($url);
        if ($metadata->status) $metadata = $metadata->data->rows;
        else return null;
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
            } elseif ($item->name == "Количество списанных баллов (UDS)") {
                $point = 0;
                if ($postUDS->points > 0) $point = $receipt_points;
                else $point = $postUDS->points;

                $Attributes[] = [
                    'meta' => [
                        'href' => $item->meta->href,
                        'type' => $item->meta->type,
                        'mediaType' => $item->meta->mediaType,
                    ],
                    'value' => (float)$point,
                ];
            } elseif ($item->name == "Количество начисленных баллов (UDS)") {
                $Attributes[] = [
                    'meta' => [
                        'href' => $item->meta->href,
                        'type' => $item->meta->type,
                        'mediaType' => $item->meta->mediaType,
                    ],
                    'value' => (float)$data['cashBack'],
                ];
            } else continue;
        }
        return $Attributes;
    }

    public function createDemands(getSettingVendorController $Setting, $SettingBD, $newBodyMS, $externalCode): void
    {
        if ($SettingBD->operationsDocument != 0 && $SettingBD->operationsDocument != null) {
            $Store = '';


            $client = new MsClient($Setting->TokenMoySklad);
            $attributes = [];
            $positions = [];
            $attributesValue = [];

            if (property_exists($newBodyMS, 'store')){
                $Store = basename($newBodyMS->store->meta->href);
            } else {
                if ($Setting->Store != null and $Setting->creatDocument == '1') {
                    $bodyStore = $client->get('https://api.moysklad.ru/api/remap/1.2/entity/store?filter=name=' . $Setting->Store)->rows;
                    $Store = $bodyStore[0]->id;
                } else {
                    $auto = Automation_new_update_MODEL::query()->where('accountId', $Setting->accountId)->get(['add_automationStore'])->first();
                    if ($auto != null) if ($auto->add_automationStore != null) $Store = $auto->add_automationStore;
                }
            }


            if ($Store != '') return;

            foreach ($newBodyMS->attributes as $item) $attributesValue[$item->name] = ['value' => $item->value];
            foreach ($client->get("https://api.moysklad.ru/api/remap/1.2/entity/demand/metadata/attributes/")->rows as $item)
                if (in_array($item->name, $attributesValue)) {
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

            $hrefPositions = $newBodyMS->positions->meta->href;
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

            $url = 'https://api.moysklad.ru/api/remap/1.2/entity/demand';
            $body = [
                'organization' => ['meta' => [
                    'href' => $newBodyMS->organization->meta->href,
                    'type' => $newBodyMS->organization->meta->type,
                    'mediaType' => $newBodyMS->organization->meta->mediaType,
                ]],
                'agent' => ['meta' => [
                    'href' => $newBodyMS->agent->meta->href,
                    'type' => $newBodyMS->agent->meta->type,
                    'mediaType' => $newBodyMS->agent->meta->mediaType,
                ]],
                'store' => ['meta' => [
                    'href' => 'https://api.moysklad.ru/api/remap/1.2/entity/store/' . $Store,
                    'type' => 'store',
                    'mediaType' => 'application/json',
                ]],
                'externalCode' => $externalCode,
                'attributes' => $attributes,
                'positions' => $positions,
                'customerOrder' => [
                    'meta' => [
                        'href' => $newBodyMS->meta->href,
                        'metadataHref' => $newBodyMS->meta->metadataHref,
                        'type' => $newBodyMS->meta->type,
                        'mediaType' => $newBodyMS->meta->mediaType,
                        'uuidHref' => $newBodyMS->meta->uuidHref,
                    ],
                ],
            ];

            try {
                $postBodyCreateDemand = $client->post($url, $body);
                if ($SettingBD->operationsDocument == '2' || $SettingBD->operationsDocument == 2) {
                    $client->post('https://api.moysklad.ru/api/remap/1.2/entity/factureout', [
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
                    ]);
                }
            } catch (BadResponseException $e) {
                if ($Setting->accountId = '9df72ee9-12a3-11ef-0a80-0f4c0000fb2b') {
                    dd($e);
                }
            }
        }
    }

    public function createPaymentDocument(MsClient $client, mixed $operationsPaymentDocument, mixed $newBodyMS): void
    {
        if ($operationsPaymentDocument == 0 || $operationsPaymentDocument == null) return;

        $url = '';
        $body = [
            'organization' => ['meta' => [
                'href' => $newBodyMS->organization->meta->href,
                'type' => $newBodyMS->organization->meta->type,
            ]],
            'agent' => ['meta' => [
                'href' => $newBodyMS->agent->meta->href,
                'type' => $newBodyMS->agent->meta->type,
            ]],
            'sum' => $newBodyMS->sum,
            'operations' => [
                0 => [
                    'meta' => [
                        'href' => $newBodyMS->meta->href,
                        'type' => $newBodyMS->meta->type,
                    ],
                    'linkedSum' => $newBodyMS->sum,
                ],
            ],
        ];

        if ($operationsPaymentDocument == 1) $url = 'https://api.moysklad.ru/api/remap/1.2/entity/cashin';
        if ($operationsPaymentDocument == 2) $url = 'https://api.moysklad.ru/api/remap/1.2/entity/paymentin';

        if ($url != '') $client->post($url, $body);
    }
}
