<?php

namespace App\Http\Controllers\BackEnd;

use App\Components\MsClient;
use App\Components\UdsClient;
use App\Http\Controllers\Config\getSettingVendorController;
use App\Http\Controllers\Controller;
use App\Jobs\webhookUDS;
use App\Services\MetaServices\MetaHook\AttributeHook;
use App\Services\MetaServices\MetaHook\PriceTypeHook;
use App\Services\MetaServices\MetaHook\UomHook;
use App\Services\WebhookMS\WebHookNewClientMS;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class postController extends Controller
{
    private AttributeHook $attributeHook;
    private MsClient $msClient;


    public function postClint(Request $request, $accountId): JsonResponse
    {
        $Setting = new getSettingVendorController($accountId);
        $this->attributeHook = new AttributeHook(new MsClient($Setting->TokenMoySklad));

        try {
            $Client = new MsClient($Setting->TokenMoySklad);
            $search = $Client->get('https://api.moysklad.ru/api/remap/1.2/entity/counterparty?search=' . $request->phone);
            (new UdsClient($Setting->companyId, $Setting->TokenUDS))->get('https://api.uds.app/partner/v2/settings');
            return response()->json((new WebHookNewClientMS())->initiation($Client, $search, $request));
        } catch (BadResponseException $e) {
            return response()->json($e);
        }

    }


    public function setJob(Request $request, $accountId)
    {

        $params = [
            "headers" => [
                'Content-Type' => 'application/json'
            ],
            'json' => $request->all(),
        ];

        webhookUDS::dispatch($params, 'https://smartuds.kz/api/webhook/'.$accountId.'/order')->onConnection('database')->onQueue("high");

        return response('',200);

    }

    public function postOrder(Request $request, $accountId): JsonResponse
    {

        $Setting = new getSettingVendorController($accountId);
        $this->attributeHook = new AttributeHook(new MsClient($Setting->TokenMoySklad));
        $this->msClient = new MsClient($Setting->TokenMoySklad);


        if ($Setting->creatDocument != "1") {
            return response()->json([
                'status' => false,
                'data' => [
                    'message' => 'Нет настроек приложения',
                    'Setting' => $Setting,
                ],
            ]);
        }


        try {
            $this->msClient->get('https://api.moysklad.ru/api/remap/1.2/entity/employee');
        } catch (BadResponseException $e) {
            return response()->json([
                'status' => false,
                'data' => [
                    'BadResponseException' => $e->getResponse()->getBody()->getContents(),
                    'message' => $e->getMessage(),
                ],
            ]);
        }

        $externalCode = $this->CheckExternalCode($request->id);
        if ($externalCode) {
            return response()->json([
                'status' => false,
                'data' => [
                    'message' => "Заказ уже создан!",
                    'Setting' => $Setting,
                    'request' => $request->all(),
                ],
            ]);
        }

        $BD = new BDController();
        $BD->createOrderID($accountId, $request->id);


        $organization = $this->metaOrganization($Setting->Organization);
        $organizationAccount = $this->metaOrganizationAccount($Setting->PaymentAccount, $Setting->Organization);
        $agent = $this->metaAgent((object) $request->customer);
        $state = $this->metaState($Setting->NEW);
        $store = $this->metaStore($Setting->Store);
        $salesChannel = $this->metaSalesChannel($Setting->Saleschannel);
        $project = $this->metaProject($Setting->Project);
        $shipmentAddress = $this->ShipmentAddress($request->delivery);
        $attributes = $this->metaAttributes($request->purchase);


        $description = $request->delivery['userComment'] ?? '';
        $description = $description . PHP_EOL . 'Клиент: ' . $request->delivery['receiverName'] . ' ' . $request->delivery['receiverPhone'];

        $positions = $this->metaPositions($request->items, $request->purchase, $request->delivery);


        $body = [
            "organization" => $organization,
            "organizationAccount" => $organizationAccount,
            "agent" => $agent,
            "state" => $state,
            "store" => $store,
            "salesChannel" => $salesChannel,
            "project" => $project,
            "shipmentAddress" => $shipmentAddress,
            "description" => $description,

            "attributes" => $attributes,
            "positions" => $positions,
            "externalCode" => (string) $request->id,
        ];
        $body = array_filter($body, function ($value) {
            return $value !== null;
        });



        try {
            $response = $this->msClient->post('https://api.moysklad.ru/api/remap/1.2/entity/customerorder', $body);
        } catch (BadResponseException $e) {
            return response()->json([
                'status' => false,
                'data' => [
                    'BadResponseException' => $e->getResponse()->getBody()->getContents(),
                    'message' => $e->getMessage(),
                    'body' => $body,
                ],
            ]);
        }

        return response()->json([
            'status' => true,
            'data' => [
                'body' => $body,
                'response' => $response,
            ],
        ]);


    }



    private function metaOrganization($Organization): array
    {
        return [
            'meta' => [
                'href' => "https://api.moysklad.ru/api/remap/1.2/entity/organization/" . $Organization,
                'type' => 'organization',
                'mediaType' => 'application/json',
            ]
        ];
    }

    private function metaOrganizationAccount($PaymentAccount, $Organization): ?array
    {
        try {
            $Body = $this->msClient->get("https://api.moysklad.ru/api/remap/1.2/entity/organization/" . $Organization . "/accounts");
        } catch (BadResponseException) {
            return null;
        }

        if ($Body->meta->size == 0) return null;

        foreach ($Body->rows as $item) {
            if ($item->accountNumber == $PaymentAccount) {
                return [
                    'meta' => [
                        'href' => $item->meta->href,
                        'type' => $item->meta->type,
                        'mediaType' => $item->meta->mediaType,
                    ]
                ];
            }
        }

        return null;
    }

    private function getEntityByName($entityType, $name): ?array
    {
        try {
            $response = $this->msClient->get("https://api.moysklad.ru/api/remap/1.2/entity/{$entityType}");
            $rows = $response->rows;
            if (empty($rows)) { return null; }

            foreach ($rows as $item) {
                if ($item->name == $name) {
                    return [
                        'meta' => [
                            'href' => $item->meta->href,
                            'type' => $item->meta->type,
                            'mediaType' => $item->meta->mediaType,
                        ],
                    ];
                }
            }

           return null;
        } catch (BadResponseException) {
            return null;
        }
    }

    private function metaAgent($agent): array
    {
        $Body = $this->msClient->get("https://api.moysklad.ru/api/remap/1.2/entity/counterparty?filter=externalCode~" . $agent->id)->rows;

        if (empty($Body)) {
            $agent = [
                "name" => $agent->displayName,
                "companyType" => "individual",
                "externalCode" => (string)$agent->id,
            ];
            $Body = $this->msClient->post("https://api.moysklad.ru/api/remap/1.2/entity/counterparty", $agent)->meta;
        } else {
            $Body = $Body[0]->meta;
        }

        return [
            'meta' => [
                'href' => $Body->href,
                'type' => $Body->type,
                'mediaType' => $Body->mediaType,
            ],
        ];
    }

    private function metaState(mixed $Status): ?array
    {
        if ($Status == null) {
            return null;
        }

        try {
            $Body = $this->msClient->get("https://api.moysklad.ru/api/remap/1.2/entity/customerorder/metadata/");
            if (!property_exists($Body, 'states')) {
                return null;
            }
        } catch (BadResponseException) {
            return null;
        }

        foreach ($Body->states as $item) {
            if ($item->name == $Status) {
                return [
                    'meta' => [
                        'href' => $item->meta->href,
                        'type' => $item->meta->type,
                        'mediaType' => $item->meta->mediaType,
                    ]
                ];
            }
        }

        return null;
    }

    private function metaStore($storeName): ?array
    {
        return $this->getEntityByName('store', $storeName);
    }

    private function metaSalesChannel($salesChannelName): ?array
    {
        return $this->getEntityByName('saleschannel', $salesChannelName);
    }

    private function metaProject($project): ?array
    {
        return $this->getEntityByName('project', $project);
    }
    private function metaAttributes($purchase): array
    {
        if ($purchase['points'] >= 0)
            $DeductionOfPoints = [
                'meta' => $this->attributeHook->getOrderAttribute('Списание баллов (UDS)'),
                'value' => true,
            ];
        else  $DeductionOfPoints = [
            'meta' => $this->attributeHook->getOrderAttribute('Списание баллов (UDS)'),
            'value' => false,
        ];

        if ($purchase['cashBack'] > 0)
            $AccrualOfPoints = [
                'meta' => $this->attributeHook->getOrderAttribute('Начисление баллов (UDS)'),
                'value' => true,
            ];
        else  $AccrualOfPoints = [
            'meta' => $this->attributeHook->getOrderAttribute('Начисление баллов (UDS)'),
            'value' => false,
        ];

        if ($purchase['certificatePoints'] > 0)
            $UsingCertificate = [
                'meta' => $this->attributeHook->getOrderAttribute('Использование сертификата (UDS)'),
                'value' => true,
            ];
        else  $UsingCertificate = [
            'meta' => $this->attributeHook->getOrderAttribute('Использование сертификата (UDS)'),
            'value' => false,
        ];

        return [$DeductionOfPoints, $AccrualOfPoints, $UsingCertificate];

    }

    private function ShipmentAddress($delivery)
    {
        if ($delivery["branch"]) {
            return "(САМОВЫВОЗ) " . $delivery["branch"]["displayName"];
        }
        if ($delivery["address"]) {
            //$deliveryCase = $delivery["deliveryCase"];
            return $delivery["address"];
        }
        return null;
    }

    private function CheckExternalCode($externalCode): bool
    {
        try {
            $body = $this->msClient->get("https://api.moysklad.ru/api/remap/1.2/entity/customerorder?filter=externalCode~" . $externalCode);
        } catch (BadResponseException) {
            return false;
        }
        if ($body->meta->size == 0) return false;
        else return true;
    }

    private function delivery($deliveryCase): array
    {

        $body = $this->msClient->get("https://api.moysklad.ru/api/remap/1.2/entity/service?filter=name=Доставка(UDS)")->rows;

        if ($body->meta->size == 0) {
            $bodyService = ['name' => 'Доставка (UDS)'];
            $body = $this->msClient->post("https://api.moysklad.ru/api/remap/1.2/entity/service", $bodyService);
        } else {
            $body = $body->rows[0];
        }

        return [
            'assortment' => [
                'meta' => [
                    'href' => $body->meta->href,
                    'type' => $body->meta->type,
                    'mediaType' => $body->meta->mediaType,
                ]
            ],
            'price' => $deliveryCase['value'],
        ];
    }

    private function metaPositions($UDSitem, $purchase, $delivery): ?array
    {
        $BodyMeta = null;
        $Body = $this->msClient->get("https://api.moysklad.ru/api/remap/1.2/entity/product/metadata/attributes");
        if ($Body->meta->size == 0) return null; else {
            $Body = $Body->rows;
        }


        foreach ($Body as $BodyMeta_item) {
            if ($BodyMeta_item->name == 'id (UDS)') {
                $BodyMeta = $BodyMeta_item->meta->href;
                break;
            }
        }
        if ($BodyMeta == null) return null;


        $total = $purchase["total"] - $purchase["skipLoyaltyTotal"];
        if ($purchase["points"] + $purchase["certificatePoints"] > 0) $pointsPercent = ($purchase["certificatePoints"] + $purchase["points"]) * 100 / $total;
        else $pointsPercent = 0;


        $Result = [];
        foreach ($UDSitem as $item) {
            $body = $this->msClient->get('https://api.moysklad.ru/api/remap/1.2/entity/product?filter=' . $BodyMeta . '=' . $item['id']);
            $discount = 0;
            $bodyIndex = 0;

            if ($body->meta->size == 0) {
                $newProduct = $this->creatingProduct(json_decode(json_encode($item)));
                $body = [0 => $newProduct];
            } else {
                $body = $body->rows;
            }

            if (isset($body[1])) {
                foreach ($body as $bodyCheckID => $bodyCheckItem) {
                    if ($item['variantName'] . "(" . $item['name'] . ")" == $bodyCheckItem->name) {
                        $bodyIndex = $bodyCheckID;
                        break;
                    }
                }
            }

            $body = $body[$bodyIndex];
            if (property_exists($body, 'attributes'))
                foreach ($body->attributes as $attributesItem) {
                    if ('Не применять бонусную программу (UDS)' == $attributesItem->name) {
                        if ($attributesItem->value) $discount = 0; else $discount = $pointsPercent;
                        break;
                    } else $discount = $pointsPercent;
                }
            else $discount = $pointsPercent;

            $Result[] = [
                'quantity' => $item['qty'],
                'price' => $item['price'] * 100,
                'assortment' => ['meta' => [
                    'href' => $body->meta->href,
                    'type' => $body->meta->type,
                    'mediaType' => $body->meta->mediaType,
                ]
                ],
                'discount' => $discount,
                'reserve' => $item['qty'],
            ];
        }

        if ($delivery['deliveryCase'] != null) {
            $deliveryCase = $this->delivery($delivery['deliveryCase']);
            $ArrayItem = [
                'quantity' => 1,
                'price' => $deliveryCase['price'] * 100,
                'assortment' => $deliveryCase['assortment'],
            ];

            $Result[] = $ArrayItem;
        }


        return $Result;
    }

    private function creatingProduct($productUds)
    {
        $bodyProduct["name"] = $productUds->name;

        $bodyProduct["salePrices"] = [
            0 => [
                "value" => $productUds->price * 100,
                "priceType" => (new PriceTypeHook($this->msClient))->getPriceTypeFirst("Цена продажи"),
            ],
        ];
        $bodyProduct["uom"] = (new UomHook($this->msClient))->getUom($this->getUomMsByUds($productUds->measurement));

        if ($productUds->sku != null) {
            $bodyProduct["article"] = $productUds->sku;
        }

        $bodyProduct["externalCode"] = "" . $productUds->id;

        try {
            return $this->msClient->post("https://api.moysklad.ru/api/remap/1.2/entity/product", $bodyProduct);
        } catch (BadResponseException) {
            return null;
        }
    }

    private function getUomMsByUds($nameUom): string
    {
        $nameUomMs = "";
        switch ($nameUom) {
            case "PIECE":
                $nameUomMs = "шт";
                break;
            case "CENTIMETRE":
                $nameUomMs = "см";
                break;
            case "METRE":
                $nameUomMs = "м";
                break;
            case "MILLILITRE":
                $nameUomMs = "мм";
                break;
            case "LITRE":
                $nameUomMs = "л; дм3";
                break;
            case "GRAM":
                $nameUomMs = "г";
                break;
            case "KILOGRAM":
                $nameUomMs = "кг";
                break;
        }
        return $nameUomMs;
    }

}
