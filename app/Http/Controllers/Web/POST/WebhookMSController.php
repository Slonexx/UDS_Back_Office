<?php

namespace App\Http\Controllers\Web\POST;

use App\Components\MsClient;
use App\Components\UdsClient;
use App\Http\Controllers\Config\getSettingVendorController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\getData\getSetting;
use App\Http\Controllers\Web\RewardController;
use App\Models\Automation_new_update_MODEL;
use App\Services\MyWarehouse\Сounterparty\getAgentByHrefService\getAgentByHrefService;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookMSController extends Controller
{
    private getAgentByHrefService $getAgentByHrefService;
    private getSettingVendorController $Setting;

    private mixed $setting;
    private MsClient $msClient;
    private UdsClient $udsClient;

    public function __construct()
    {
        $this->getAgentByHrefService = new getAgentByHrefService();
    }
    private function returnMessage(string $State, $moment, string $Message): array|string
    {
        $result = '';
        switch ($State) {
            case 'ERROR':{
                $result = [
                    "ERROR ==========================================",
                    "[".$moment."] - Начала выполнение скрипта",
                    "[".date('Y-m-d H:i:s')."] - Конец выполнение скрипта",
                    "===============================================",
                    $Message,
                ];
                break;
            }
            case 'SUCCESS': {
                $result = [
                    "[".$moment."] - Начала выполнение скрипта",
                    "[".date('Y-m-d H:i:s')."] - Конец выполнение скрипта",
                    "===============================================",
                    $Message,
                ];
                break;
            }
        }
        return $result;
    }
    public function customerorder(Request $request): JsonResponse
    {

        if (empty($request->auditContext)) {
            return response()->json([
                'code' => 203,
                'message' => $this->returnMessage("ERROR", "2023-00-00 00:00:00", "Отсутствует auditContext, (изменений не было), скрипт прекращён!"),
            ]);
        }

        if (isset($request['events'][0]['updatedFields'])){
            if (!in_array('state', $request['events'][0]['updatedFields'])) {
                return response()->json([
                    'code' => 203,
                    'message' => $this->returnMessage("ERROR", $request['auditContext']['moment'], "Не было изменений статуса, скрипт прекращён!"),
                ]);
            }
        } else {
            return response()->json([
                'code' => 203,
                'message' => $this->returnMessage("ERROR", $request['auditContext']['moment'], "Отсутствует updatedFields, (изменений не было), скрипт прекращён!"),
            ]);
        }

        $find = Automation_new_update_MODEL::query()->where('accountId', $request['events'][0]['accountId'])->first();
        if ($find == [] or $find == null) {
            return response()->json([
                'code' => 203,
                'message' => $this->returnMessage("ERROR", $request['auditContext']['moment'], "Отсутствует настройки автоматизации, скрипт прекращён!"),
            ]);
        }

        $this->Setting = new getSettingVendorController($request['events'][0]['accountId']);
        $this->setting = (new getSetting())->getSendSettingOperations($this->Setting->accountId);
        $this->msClient = new MsClient($this->Setting->TokenMoySklad);
        $this->udsClient = new UdsClient($this->Setting->companyId, $this->Setting->TokenUDS);

        try {
            $ObjectBODY = $this->msClient->get($request['events'][0]['meta']['href']);
            $this->udsClient->get('https://api.uds.app/partner/v2/settings');
        } catch (BadResponseException $e) {
            return response()->json([
                'code' => 203,
                'message' => $this->returnMessage("ERROR", $request['auditContext']['moment'], $e->getMessage()),
            ]);
        }

        try {
            $state = $this->msClient->get($ObjectBODY->state->meta->href)->name;
            if ($state == $find->getAttributes()['statusAutomation']) {
                $boolProject = false;
                $boolSaleschannel= false;
               if ($find->getAttributes()['projectAutomation'] != "0") {
                    if (property_exists($ObjectBODY, 'project')){
                        if ($this->msClient->get($ObjectBODY->project->meta->href)->name == $find->getAttributes()['projectAutomation']) {
                            $boolProject = true;
                        }
                    }
               } else $boolProject = true;
                if ($find->getAttributes()['saleschannelAutomation'] != "0") {
                    if (property_exists($ObjectBODY, 'salesChannel')){
                        if ($this->msClient->get($ObjectBODY->salesChannel->meta->href)->name == $find->getAttributes()['saleschannelAutomation']) {
                            $boolSaleschannel = true;
                        }
                    }
                } else $boolSaleschannel = true;

                if ((int) $ObjectBODY->externalCode > 10000) {
                    return response()->json([
                        'code' => 203,
                        'message' => $this->returnMessage("ERROR", $request['auditContext']['moment'], "Внешний код был изменён, операция ранее уже проводилась, скрипт прекращён!"),
                    ]);
                }


                if ($boolProject and $boolSaleschannel) return response()->json([
                    'code' => 200,
                    'message' => $this->WebHookUpdateState($ObjectBODY, $request['events'][0]['meta'],  $request['auditContext']['moment'], $request['auditContext']['uid'], $find->getAttributes() ),
                ]);
                else {
                    return response()->json([
                        'code' => 203,
                        'message' => $this->returnMessage("ERROR", $request['auditContext']['moment'], "Проект или Канал продажи не соответствует настройкам, скрипт прекращён!"),
                    ]);
                }
            } else  return response()->json([
                'code' => 203,
                'message' => $this->returnMessage("ERROR", $request['auditContext']['moment'], "Статус не соответствует настройкам, скрипт прекращён!"),
            ]);
        } catch (BadResponseException $e) {
            return response()->json([
                'code' => 203,
                'message' => $this->returnMessage("ERROR", $request['auditContext']['moment'], $e->getMessage()),
            ]);
        }
    }




    private function WebHookUpdateState(mixed $ObjectBODY, mixed $meta, string $moment, string $uid, mixed $BDFFirst): array|string
    {
        $postBODY = [
            'code' =>null,
            'participant' => [
                'uid' => null,
                'phone' => null,
            ],
            'nonce' => null,
            'cashier' => [
                'externalId' => null,
                'name' => null,
            ],
            'receipt' => [
                'total' => null,
                'cash' => null,
                'points' => null,
                'number' => null,
                'skipLoyaltyTotal' => null,
            ],
            'tags' => null
        ];


        $Agent = $this->getAgentByHrefService->getAgent($this->Setting->TokenMoySklad, $ObjectBODY->agent->meta->href);
        $externalCodeAgent = $this->getAgentByHrefService->getAgentToObject($this->Setting->TokenMoySklad, $ObjectBODY->agent->meta->href, 'externalCode');
        if ((int) $externalCodeAgent > 0) { $UDSExternalCodeBool = $this->externalCodeToBoolean($externalCodeAgent); } else $UDSExternalCodeBool = false ;
        if ($UDSExternalCodeBool) { $postBODY['participant'] = $this->AgentCheckUIDUDS($externalCodeAgent, $Agent->phone);
        } else { $postBODY['participant']['phone'] = $this->PhoneConverted($Agent->phone); }

        $postBODY['cashier'] = $this->BodyCashierUDS($uid);
        $postBODY['receipt'] = $this->BodyReceiptUDS($ObjectBODY);


        $postUDS = $this->udsClient->post('https://api.uds.app/partner/v2/operations', $postBODY);
        if ($this->setting->customOperation == 1) {
            (new RewardController())->Accrue($this->setting->accountId, $this->pointsAccrue($ObjectBODY), $postUDS->customer->id);
        }


        $setAttributes = $this->Attributes($postUDS, $meta['type']);
        $this->msClient->put($meta['href'], [ 'externalCode'=>(string) $postUDS->id, 'attributes' => $setAttributes, ]);


        if ($BDFFirst['documentAutomation'] == '1' or $BDFFirst['documentAutomation'] == 1){
            $this->createPaymentDocument($BDFFirst['add_automationPaymentDocument'],$ObjectBODY);
        } else
        if ($BDFFirst['automationDocument'] != 1 and $BDFFirst['automationDocument'] != null) {
            $this->createDemands($BDFFirst,$ObjectBODY, $postUDS->id);
            $this->createPaymentDocument($BDFFirst['add_automationPaymentDocument'],$ObjectBODY);
        }

        return $this->returnMessage("SUCCESS", $moment, "Успешное выполнение, все данные обновлены");
    }

    private function createPaymentDocument( string $paymentDocument, mixed $OldBody): void
    {
        switch ($paymentDocument){
            case "1": {
                $url = 'https://online.moysklad.ru/api/remap/1.2/entity/cashin';
                $body = [
                    'organization' => [  'meta' => [
                        'href' => $OldBody->organization->meta->href,
                        'type' => $OldBody->organization->meta->type,
                        'mediaType' => $OldBody->organization->meta->mediaType,
                    ] ],
                    'agent' => [ 'meta'=> [
                        'href' => $OldBody->agent->meta->href,
                        'type' => $OldBody->agent->meta->type,
                        'mediaType' => $OldBody->agent->meta->mediaType,
                    ] ],
                    'sum' => $OldBody->sum,
                    'operations' => [
                        0 => [
                            'meta'=> [
                                'href' => $OldBody->meta->href,
                                'metadataHref' => $OldBody->meta->metadataHref,
                                'type' => $OldBody->meta->type,
                                'mediaType' => $OldBody->meta->mediaType,
                                'uuidHref' => $OldBody->meta->uuidHref,
                            ],
                            'linkedSum' => $OldBody->sum,
                        ], ]
                ];
                $this->msClient->post($url, $body);
                break;
            }
            case "2": {
                $url = 'https://online.moysklad.ru/api/remap/1.2/entity/paymentin';

                $rate_body = $this->msClient->get("https://online.moysklad.ru/api/remap/1.2/entity/currency/")->rows;
                $rate = null;
                foreach ($rate_body as $item){
                    if ($item->name == "тенге" or $item->fullName == "Казахстанский тенге"){
                        $rate =
                            ['meta'=> [
                                'href' => $item->meta->href,
                                'metadataHref' => $item->meta->metadataHref,
                                'type' => $item->meta->type,
                                'mediaType' => $item->meta->mediaType,
                            ],
                            ];
                    }
                }

                $body = [
                    'organization' => [  'meta' => [
                        'href' => $OldBody->organization->meta->href,
                        'type' => $OldBody->organization->meta->type,
                        'mediaType' => $OldBody->organization->meta->mediaType,
                    ] ],
                    'agent' => [ 'meta'=> [
                        'href' => $OldBody->agent->meta->href,
                        'type' => $OldBody->agent->meta->type,
                        'mediaType' => $OldBody->agent->meta->mediaType,
                    ] ],
                    'sum' => $OldBody->sum,
                    'operations' => [
                        0 => [
                            'meta'=> [
                                'href' => $OldBody->meta->href,
                                'metadataHref' => $OldBody->meta->metadataHref,
                                'type' => $OldBody->meta->type,
                                'mediaType' => $OldBody->meta->mediaType,
                                'uuidHref' => $OldBody->meta->uuidHref,
                            ],
                            'linkedSum' => 0
                        ], ],
                    'rate' => $rate
                ];
                if ($body['rate'] == null) unlink($body['rate']);
                $this->msClient->post($url, $body);
                break;
            }
            default:{
                break;
            }
        }

    }


    public function createDemands(mixed $BDFFirst, mixed $OldBody, string $externalCode): void
    {
        $attributes = null;
        $positions = null;

        foreach ($this->msClient->get("https://online.moysklad.ru/api/remap/1.2/entity/demand/metadata/attributes/")->rows as $item) {
            if ($item->name == "Начисление баллов (UDS)") {
                $attributes[] = [
                    'meta' => [
                        'href' => $item->meta->href,
                        'type' => $item->meta->type,
                        'mediaType' => $item->meta->mediaType,
                    ],
                    'value' => true
                ];
            }
        }
        foreach ($this->msClient->get($OldBody->positions->meta->href)->rows as $id=>$item) {
            $positions[$id] = [
                'quantity' => $item->quantity,
                'price' => $item->price,
                'discount' => $item->discount,
                'vat' => $item->vat,
                'assortment' => ['meta'=> [
                    'href' => $item->assortment->meta->href,
                    'type' => $item->assortment->meta->type,
                    'mediaType' => $item->assortment->meta->mediaType,
                ] ],
            ];
        }

        if ($BDFFirst['add_automationStore'] == 0 or $BDFFirst['add_automationStore'] == null) {
            $store = $OldBody->store->meta->href;
        } else $store = 'https://online.moysklad.ru/api/remap/1.2/entity/store/'.$BDFFirst['add_automationStore'];

        $body = [
            'organization' => [
                'meta' => [
                    'href' => $OldBody->organization->meta->href,
                    'type' => "organization",
                    'mediaType' => "application/json",
                    ]
            ],
            'organizationAccount' => [
                'meta' => [
                    'href' => null,
                    'type' => "account",
                    'mediaType' => "application/json",
                    ]
            ],
            'agent' => [
                'meta'=> [
                    'href' => $OldBody->agent->meta->href,
                    'type' => $OldBody->agent->meta->type,
                    'mediaType' => $OldBody->agent->meta->mediaType,
                    ]
            ],
            'store' => [
                'meta'=> [
                    'href' => $store,
                    'type' => 'store',
                    'mediaType' => 'application/json',
                    ]
            ],
            'externalCode' => $externalCode,
            'attributes' => $attributes,
            'positions' => $positions,
            'project' => [
                'meta'=> [
                    'href' => null,
                    'type' => 'project',
                    'mediaType' => 'application/json',
                ]
            ],
            'salesChannel' =>  [
                'meta'=> [
                    'href' => null,
                    'type' => 'saleschannel',
                    'mediaType' => 'application/json',
                ]
            ],
            'customerOrder' => [
                'meta'=> [
                    'href' => $OldBody->meta->href,
                    'metadataHref' => $OldBody->meta->metadataHref,
                    'type' => $OldBody->meta->type,
                    'mediaType' => $OldBody->meta->mediaType,
                    'uuidHref' => $OldBody->meta->uuidHref,
                ] ],
        ];

        if (property_exists($OldBody, 'organizationAccount')) $body['organizationAccount']['meta']['href'] = $OldBody->organizationAccount->meta->href;
        else unset($body['organizationAccount']);
        if (property_exists($OldBody, 'salesChannel')) $body['salesChannel']['meta']['href'] = $OldBody->salesChannel->meta->href;
        else unset($body['salesChannel']);
        if (property_exists($OldBody, 'project')) $body['project']['meta']['href'] = $OldBody->project->meta->href;
        else unset($body['project']);

        try {
            $Demands = $this->msClient->post("https://online.moysklad.ru/api/remap/1.2/entity/demand", $body);
            if ($BDFFirst['automationDocument'] == '3'){
                $this->msClient->post('https://online.moysklad.ru/api/remap/1.2/entity/factureout', [
                    'demands' => [
                        '0' => [
                            'meta'=> [
                                'href' => "https://online.moysklad.ru/api/remap/1.2/entity/demand/".$Demands->id,
                                'metadataHref' => "https://online.moysklad.ru/api/remap/1.2/entity/demand/metadata",
                                'type' => "demand",
                                'mediaType' => "application/json",
                            ]
                        ]
                    ]
                ]);
            }

        } catch (BadResponseException $e){
            dd($body,$e->getMessage(), $e->getResponse()->getBody()->getContents());
        }
    }


    private function PhoneConverted(string $phone): string
    {
        return "+7".mb_substr(str_replace('+7', '', str_replace(" ", '', $phone)), -10);
    }

    private function externalCodeToBoolean($externalCodeAgent): bool
    {
        $int =  str_replace("0", '', $externalCodeAgent);
        for ($integer = 1; $integer < 10; $integer++) {
            $int =  str_replace($integer, '', $int);
        }
        if ($int == "") { return true; } else return false;
    }

    private function BodyCashierUDS($uid): array
    {
        $employee = $this->msClient->get('https://online.moysklad.ru/api/remap/1.2/entity/employee')->rows;
        foreach ($employee as $item){
            if ($item->uid == $uid) {
                 return [
                     'externalId' => $item->id,
                     'name' => $item->fullName,
                 ];
            }
        }

        return [
            'externalId' => $employee[0]->id,
            'name' => $employee[0]->fullNamem,
            ];
    }

    private function AgentCheckUIDUDS($externalCodeAgent, $phone): array
    {
        $body = $this->udsClient->get('https://api.uds.app/partner/v2/customers/'.$externalCodeAgent);
        if ($body->uid != null){
            return [
                'uid' => $body->uid,
                'phone' => null,
            ];
        } else
        return [
            'uid' => null,
            'phone' => $this->PhoneConverted($phone),
        ];
    }

    private function BodyReceiptUDS(mixed $ObjectBODY): array
    {
        $sum = $ObjectBODY->sum / 100;
        $SkipLoyaltyTotal = 0;
        $BodyPositions = $this->msClient->get($ObjectBODY->positions->meta->href)->rows;

        foreach ($BodyPositions as $item) {
            $url_item = $item->assortment->meta->href;
            $body =  $this->msClient->get($url_item);

            $BonusProgramm = false;
            if (property_exists($body, 'attributes')) {
                foreach ($body->attributes as $body_item) {
                    if ('Не применять бонусную программу (UDS)' == $body_item->name) { $BonusProgramm = $body_item->value; }

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


        if ($SkipLoyaltyTotal <= 0 ) $SkipLoyaltyTotal = null;

        return [
            'total' => $sum,
            'cash' => $sum,
            'points' => 0,
            'number' => null,
            'skipLoyaltyTotal' => $SkipLoyaltyTotal,
        ];
    }


    public function Attributes(mixed $postUDS, string $type): array
    {
        $metadata = $this->msClient->get('https://online.moysklad.ru/api/remap/1.2/entity/'.$type.'/metadata/attributes')->rows;
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
            elseif ($item->name == "Начисление баллов (UDS)") {
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
            elseif ($item->name == "Количество начисленных баллов (UDS)"){
                $Attributes[] = [
                    'meta' => [
                        'href' => $item->meta->href,
                        'type' => $item->meta->type,
                        'mediaType' => $item->meta->mediaType,
                    ],
                    'value' => (float) $postUDS->points,
                ];
            } else continue;
        }
        return $Attributes;
    }

    private function pointsAccrue(mixed $ObjectBODY): float|int
    {
        $sum = 0;
        $BodyPositions = $this->msClient->get($ObjectBODY->positions->meta->href)->rows;

        foreach ($BodyPositions as $item) {
            $url_item = $item->assortment->meta->href;
            $body =  $this->msClient->get($url_item);

            if (property_exists($body, 'attributes')) {
                foreach ($body->attributes as $body_item) {
                    if ('Не применять бонусную программу (UDS)' == $body_item->name) { continue; }
                    if ('Процент начисления (UDS)' == $body_item->name) {
                        $minPrice = 0;
                        if (property_exists($body, "minPrice")) { $minPrice = $body->minPrice->value; }
                        $price = $item->price * ($body_item->value / 100);
                        if ($price > $minPrice) {
                            $sum = $sum + $price ;
                        } else {
                            $sum = $sum + $minPrice;
                        }

                    }
                }
            }
        }
        return $sum / 100;
    }


}
