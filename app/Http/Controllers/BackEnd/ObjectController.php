<?php

namespace App\Http\Controllers\BackEnd;

use App\Components\MsClient;
use App\Components\UdsClient;
use App\Http\Controllers\Config\getSettingVendorController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\getData\getSetting;
use App\Http\Controllers\GuzzleClient\ClientMC;
use App\Services\counterparty\widgetCounterparty;
use App\Services\Operation\WidgetInfo;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ObjectController extends Controller
{

    public function CounterpartyObject($accountId, $objectId): JsonResponse
    {
        return ((new widgetCounterparty())->getInformation($accountId, $objectId));
    }

    public function CustomerOrderEditObject($accountId, $entity, $objectId): array
    {
        $object = new WidgetInfo();
        return $object->getInformation($accountId, $entity, $objectId);
    }


    public function CompletesOrder($accountId, $objectId): array
    {
        $Setting = new getSettingVendorController($accountId);
        $Client = new UdsClient($Setting->companyId, $Setting->TokenUDS);
        try {
            $url = 'https://api.uds.app/partner/v2/goods-orders/' . $objectId . '/complete';
            $Client->post($url, null);
            $StatusCode = "200";
            $message = "Заказ завершён";
            return [
                'StatusCode' => $StatusCode,
                'message' => $message,
            ];
        } catch (BadResponseException $exception) {
            return [
                'StatusCode' => $exception->getCode(),
                'message' => $exception->getMessage(),
            ];
        }
    }

    public function customers(Request $request): JsonResponse
    {
        $data = $request->validate([
            "accountId" => 'required|string',
            "code" => 'required|string',
        ]);

        $Setting = new getSettingVendorController($data['accountId']);
        $url = 'https://api.uds.app/partner/v2/customers/find?code=' . $data['code'];
        try {
            $Client = new UdsClient($Setting->companyId, $Setting->TokenUDS);
            $Body = $Client->get($url)->user;
            $result = [
                'id' => $Body->participant->id,
                'availablePoints' => $Body->participant->points,
                'displayName' => $Body->displayName,
            ];
        } catch (BadResponseException) {
            $result = [
                'id' => 0,
                'availablePoints' => 0,
                'displayName' => 0,
            ];
        }

        return response()->json($result);

    }

    public function operationsCalc(Request $request): JsonResponse
    {
        $data = $request->validate([
            "accountId" => 'required|string',
            "user" => "required|string",
            "total" => "required|string",
            "SkipLoyaltyTotal" => "required|string",
            "points" => "required|string",
            "entity_type" => "required|string",
            "object_Id" => "required|string",
        ]);

        if (strlen(str_replace(' ', '', $data['user'])) > 6) {
            $data['code'] = null;
            $data['phone'] = $data['user'];
        } else {
            $data['code'] = $data['user'];
            $data['phone'] = null;
        }

        if ($data['SkipLoyaltyTotal'] == '0') {
            $data['SkipLoyaltyTotal'] = null;
        }

        //$data['SkipLoyaltyTotal'] =  $data['SkipLoyaltyTotal'] * 100 / 90;

        $Setting = new getSettingVendorController($data['accountId']);
        $ClientMS = new MsClient($Setting->TokenMoySklad);
        $unredeemableTotal = $this->unredeemableTotal($ClientMS, $data['entity_type'], $data['object_Id']);

        $Client = new UdsClient($Setting->companyId, $Setting->TokenUDS);
        $url = 'https://api.uds.app/partner/v2/operations/calc';
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

        if ($body['receipt']['unredeemableTotal'] == null){
            unset($body['receipt']['unredeemableTotal']);
        }

        $postBody = $Client->post($url, $body);
        if (property_exists($postBody, 'purchase')) {
            return response()->json($postBody->purchase);
        } else {
            return response()->json(['Status' => "", 'Message' => "Ошибка попробуйте позже"]);
        }


    }

    public function operations(Request $request): JsonResponse
    {
        $data = $request->validate([
            "accountId" => 'required|string',
            "objectId" => 'required|string',
            "entity" => 'required|string',
            "user" => "required|string",
            "cashier_id" => "required|string",
            "cashier_name" => "required|string",
            "receipt_total" => "required|string",
            "receipt_cash" => "required|string",
            "receipt_points" => "required|string",
            "receipt_skipLoyaltyTotal" => "required|string",
        ]);
        if (strlen(str_replace(' ', '', $data['user'])) > 6) {
            $data['code'] = null;
            $data['phone'] = str_replace("+7", '', $data['user']);
            $data['phone'] = '+7' . str_replace(" ", '', $data['phone']);
        } else {
            $data['code'] = $data['user'];
            $data['phone'] = null;
        }

        if ($data['receipt_points'] == "undefined") $data['receipt_points'] = '0';
        if ($data['receipt_skipLoyaltyTotal'] == "undefined" or $data['receipt_skipLoyaltyTotal'] == "0") $data['receipt_skipLoyaltyTotal'] = null;

        $url = 'https://api.uds.app/partner/v2/operations';
        $Setting = new getSettingVendorController($data['accountId']);
        $SettingBD = new getSetting();
        $SettingBD = $SettingBD->getSendSettingOperations($data['accountId']);
        $Client = new UdsClient($Setting->companyId, $Setting->TokenUDS);
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
                'total' => $data['receipt_total'],
                'cash' => (string)round($data['receipt_cash'], 2),
                'points' => $data['receipt_points'],
                'number' => null,
                'skipLoyaltyTotal' => $data['receipt_skipLoyaltyTotal'],
            ],
            'tags' => null
        ];

        //try {
        $post = $Client->post($url, $body);

        $urlMC = 'https://online.moysklad.ru/api/remap/1.2/entity/' . $data['entity'] . '/' . $data['objectId'];
        $ClientMC = new ClientMC($urlMC, $Setting->TokenMoySklad);
        $OldBody = $ClientMC->requestGet();

        $setPositions = $this->Positions($post, $data['receipt_skipLoyaltyTotal'], $OldBody, $Setting);
        $setAttributes = $this->Attributes($data, $post, $Setting);

        $OldBody->externalCode = $post->id;
        $putBody = $ClientMC->requestPut([
            'externalCode' => (string)$post->id,
            'positions' => $setPositions,
            'attributes' => $setAttributes,
        ]);
        if ($data['entity'] == 'customerorder') {
            $this->createDemands($Setting, $SettingBD, $putBody, (string)$post->id);
        }
        $this->createPaymentDocument($Setting, $SettingBD, $putBody);
        $post = [
            'code' => 200,
            'id' => $post->id,
            'points' => $post->points,
            'total' => $post->total,
            'message' => 'The operation was successful',
        ];

        /* } catch (BadResponseException $e) {
             $post = [
                 'code' => $e->getCode(),
                 'message' => $e->getMessage(),
             ];
         }*/

        return response()->json($post);
    }

    public function Positions($postUDS, $skipLoyaltyTotal, $OldBody, $Setting): array
    {
        $Positions = [];
        $ClientMCPositions = new ClientMC($OldBody->positions->meta->href, $Setting->TokenMoySklad);
        $OldPositions = $ClientMCPositions->requestGet()->rows;

        $sumMC = $OldBody->sum - $skipLoyaltyTotal;
        if ($sumMC > 0) $pointsPercent = ($postUDS->points * -1) * 100 / ($sumMC / 100); else $pointsPercent = 0;
        foreach ($OldPositions as $item) {
            $Positions[] = [
                'id' => $item->id,
                'accountId' => $item->accountId,
                'quantity' => $item->quantity,
                'price' => $item->price,
                'discount' => $item->discount + $pointsPercent,
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
        $Attributes = null;
        foreach ($metadata as $item) {
            if ($item->name == "Списание баллов (UDS)") {
                if (($postUDS->points * -1) > 0) {
                    $Attributes[] = [
                        'meta' => [
                            'href' => $item->meta->href,
                            'type' => $item->meta->type,
                            'mediaType' => $item->meta->mediaType,
                        ],
                        'value' => true,
                    ];
                } else {
                    $Attributes[] = [
                        'meta' => [
                            'href' => $item->meta->href,
                            'type' => $item->meta->type,
                            'mediaType' => $item->meta->mediaType,
                        ],
                        'value' => false,
                    ];
                }
            }
            if ($item->name == "Начисление баллов (UDS)") {
                if ($postUDS->cash > 0) {
                    $Attributes[] = [
                        'meta' => [
                            'href' => $item->meta->href,
                            'type' => $item->meta->type,
                            'mediaType' => $item->meta->mediaType,
                        ],
                        'value' => true,
                    ];
                } else {
                    $Attributes[] = [
                        'meta' => [
                            'href' => $item->meta->href,
                            'type' => $item->meta->type,
                            'mediaType' => $item->meta->mediaType,
                        ],
                        'value' => false,
                    ];
                }
            }
        }
        return $Attributes;
    }

    public function createDemands($Setting, $SettingBD, $OldBody, $externalCode): void
    {
        if ($SettingBD->operationsDocument != 0 and $SettingBD->operationsDocument != null) {
            $client = new MsClient($Setting->TokenMoySklad);
            $attributes = null;
            $positions = null;
            $attributes_value = null;
            $Store = $Setting->Store;
            $bodyStore = $client->get('https://online.moysklad.ru/api/remap/1.2/entity/store?filter=name=' . $Store)->rows;
            $Store = $bodyStore[0]->id;
            $bodyAttributes = $client->get("https://online.moysklad.ru/api/remap/1.2/entity/demand/metadata/attributes/")->rows;
            foreach ($OldBody->attributes as $item) {
                if ($item->name == "Начисление баллов (UDS)") {
                    $attributes_value[$item->name] = [
                        'value' => $item->value
                    ];
                }
                if ($item->name == "Списание баллов (UDS)") {
                    $attributes_value[$item->name] = [
                        'value' => $item->value
                    ];
                }
            }
            foreach ($bodyAttributes as $item) {
                if ($item->name == "Начисление баллов (UDS)") {
                    $attributes[] = [
                        'meta' => [
                            'href' => $item->meta->href,
                            'type' => $item->meta->type,
                            'mediaType' => $item->meta->mediaType,
                        ],
                        'value' => $attributes_value[$item->name]['value']
                    ];
                }
                if ($item->name == "Списание баллов (UDS)") {
                    $attributes[] = [
                        'meta' => [
                            'href' => $item->meta->href,
                            'type' => $item->meta->type,
                            'mediaType' => $item->meta->mediaType,
                        ],
                        'value' => $attributes_value[$item->name]['value']
                    ];
                }
            }
            $href_positions = $OldBody->positions->meta->href;
            $bodyPositions = $client->get($href_positions)->rows;
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
                    ]],
            ];
            try {
                $postBodyCreateDemand = $client->post($url, $body);
                if ($SettingBD->operationsDocument == '2' or $SettingBD->operationsDocument == 2) {
                    $body = [
                        'demands' => [0 => ['meta' => [
                            'href' => $postBodyCreateDemand->meta->href,
                            'metadataHref' => $postBodyCreateDemand->meta->metadataHref,
                            'type' => $postBodyCreateDemand->meta->type,
                            'mediaType' => $postBodyCreateDemand->meta->mediaType,
                        ]]]];

                    $urlFacture = 'https://online.moysklad.ru/api/remap/1.2/entity/factureout';
                    $client = new MsClient($Setting->TokenMoySklad);
                    $client->post($urlFacture, $body);
                }
            } catch (BadResponseException) {

            }
        }
    }

    public function createPaymentDocument($Setting, $SettingBD, $OldBody): void
    {
        if ($SettingBD->operationsPaymentDocument == 0 or $SettingBD->operationsPaymentDocument == null) {

        } else {
            $client = new MsClient($Setting->TokenMoySklad);
            if ($SettingBD->operationsPaymentDocument == 1 or $SettingBD->operationsPaymentDocument == "1") {
                $url = 'https://online.moysklad.ru/api/remap/1.2/entity/cashin';

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
                            'linkedSum' => 0
                        ],]
                ];
                $client->post($url, $body);
            }
            if ($SettingBD->operationsPaymentDocument == 2) {
                $url = 'https://online.moysklad.ru/api/remap/1.2/entity/paymentin';

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
                            'linkedSum' => 0
                        ],]
                ];
                $client->post($url, $body);
            }
        }
    }

    private function unredeemableTotal(MsClient $ClientMS, mixed $entity_type, mixed $object_Id): float
    {
        $bodyOrder = $ClientMS->get('https://online.moysklad.ru/api/remap/1.2/entity/' . $entity_type . '/' . $object_Id);
        $unredeemableTotal = null;
        $href = $bodyOrder->positions->meta->href;
        $BodyPositions = $ClientMS->get($href)->rows;
        foreach ($BodyPositions as $id => $item) {
            $url_item = $item->assortment->meta->href;
            $body = $ClientMS->get($url_item);

            $BonusProgramm = false;
            if (property_exists($body, 'attributes')) {
                foreach ($body->attributes as $body_item) {
                    if ('Не применять бонусную программу (UDS)' == $body_item->name) {
                        $BonusProgramm = $body_item->value;
                    }
                    if ('Процент списания (UDS)' == $body_item->name) {
                        $minPrice = 0;
                        if (property_exists($body, "minPrice")) {
                            $minPrice = $body->minPrice->value;
                        }
                        if ($body_item->value < 100) {
                            $PresentBonus = (($item->price - ($item->price * $body_item->value / 90)) / 100);
                            $unredeemableTotal = $unredeemableTotal + round($PresentBonus, 2);
                        } else $unredeemableTotal = $unredeemableTotal + (($item->price - $minPrice) / 100);
                    }
                }
            }

            if ($BonusProgramm) {
                $price = ($item->quantity * $item->price - ($item->quantity * $item->price * ($item->discount / 100))) / 100;
                $unredeemableTotal = $unredeemableTotal + $price;
            }

        }
        if ($unredeemableTotal!=null) round($unredeemableTotal, 2);
        return $unredeemableTotal;
    }
}
