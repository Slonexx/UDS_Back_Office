<?php

namespace App\Services\AdditionalServices;

use App\Components\MsClient;
use App\Http\Controllers\BackEnd\BDController;
use GuzzleHttp\Exception\ClientException;

class AttributeService
{


    private function createProductAttributes($apiKeyMs): void
    {
        $bodyAttributes = [
            0 => [
                "name" => "Акционный товар (UDS)",
                "type" => "boolean",
                "required" => false,
                "show" => false,
                "description" => "Акционный товар (UDS)",
            ],

            1 => [
                "name" => "Процент начисления (UDS)",
                "type" => "long",
                "required" => false,
                "show" => false,
                "description" => "Это поле будет использоваться для расчета бонуса клиента, который будет зависеть от суммы товара (UDS)",
            ],

            2 => [
                "name" => "Процент списания (UDS)",
                "type" => "long",
                "required" => false,
                "show" => false,
                "description" => "Это поле будет использоваться для расчета бонуса клиента, который будет зависеть от суммы товара (UDS)",
            ],

            3 => [
                "name" => "Не применять бонусную программу (UDS)",
                "type" => "boolean",
                "required" => false,
                "description" => "Этот товар не будете участвовать в бонусной программе (UDS).",
            ],

            4 => [
                "name" => "Товар неограничен (UDS)",
                "type" => "boolean",
                "required" => false,
                "description" => "Товар неограничен (UDS)",
            ],

            5 => [
                "name" => "Не выгружать товар в UDS ? (UDS)",
                "type" => "boolean",
                "required" => false,
                "description" => "данный товар не будет выгружаться в UDS)",
            ],

            6 => [
                "name" => "Дробное значение товара (UDS)",
                "type" => "boolean",
                "required" => false,
                "description" => "Дробное значение товара (UDS)",
            ],
            7 => [
                "name" => "Шаг дробного значения (UDS)",
                "type" => "double",
                "required" => false,
                "description" => "Шаг дробного значения (UDS)",
            ],
            8 => [
                "name" => "Минимальный размер заказа дробного товара (UDS)",
                "type" => "double",
                "required" => false,
                "description" => "Минимальный размер заказа дробного товара (UDS)",
            ],
            9 => [
                "name" => "Цена минимального размера заказа дробного товара (UDS)",
                "type" => "double",
                "required" => false,
                "description" => "Цена минимального размера заказа дробного товара (UDS)",
            ],
            10 => [
                "name" => "id (UDS)",
                "type" => "string",
                "required" => false,
                "description" => "id (UDS)",
            ],
        ];

        $url = "https://api.moysklad.ru/api/remap/1.2/entity/product/metadata/attributes";
        $client = new MsClient($apiKeyMs);
        $this->getBodyToAdd($client, $url, $bodyAttributes);

    }

    private function createAgentAttributes($apiKeyMs): void
    {
        $bodyAttributes = [
            0 => [
                "name" => "id (UDS)",
                "type" => "string",
                "required" => false,
                "description" => "id (UDS)",
            ]
        ];

        $url = "https://api.moysklad.ru/api/remap/1.2/entity/counterparty/metadata/attributes";
        $client = new MsClient($apiKeyMs);
        $this->getBodyToAdd($client, $url, $bodyAttributes);
    }

    private function createOrderAttributes($apiKeyMs): void
    {
        $bodyAttributes = $this->getDocAttributes();
        $url = "https://api.moysklad.ru/api/remap/1.2/entity/customerorder/metadata/attributes";
        $client = new MsClient($apiKeyMs);
        $this->getBodyToAdd($client, $url, $bodyAttributes);
    }

    private function createDemandAttributes($apiKeyMs): void
    {
        $bodyAttributes = $this->getDocAttributes();
        $url = "https://api.moysklad.ru/api/remap/1.2/entity/demand/metadata/attributes";
        $client = new MsClient($apiKeyMs);
        $this->getBodyToAdd($client, $url, $bodyAttributes);
    }

    private function createPaymentInAttributes($apiKeyMs):void
    {
        $bodyAttributes = $this->getDocAttributes();
        $url = "https://api.moysklad.ru/api/remap/1.2/entity/paymentin/metadata/attributes";
        $client = new MsClient($apiKeyMs);
        $this->getBodyToAdd($client, $url, $bodyAttributes);
    }

    private function createPaymentOutAttributes($apiKeyMs):void
    {
        $bodyAttributes = $this->getDocAttributes();
        $url = "https://api.moysklad.ru/api/remap/1.2/entity/paymentout/metadata/attributes";
        $client = new MsClient($apiKeyMs);
        $this->getBodyToAdd($client, $url, $bodyAttributes);
    }

    private function createCashInAttributes($apiKeyMs):void
    {
        $bodyAttributes = $this->getDocAttributes();
        $url = "https://api.moysklad.ru/api/remap/1.2/entity/cashin/metadata/attributes";
        $client = new MsClient($apiKeyMs);
        $this->getBodyToAdd($client, $url, $bodyAttributes);
    }

    public function createCashOutAttributes($apiKeyMs)
    {
        $bodyAttributes = $this->getDocAttributes();
        $url = "https://api.moysklad.ru/api/remap/1.2/entity/cashout/metadata/attributes";
        $client = new MsClient($apiKeyMs);
        $this->getBodyToAdd($client, $url, $bodyAttributes);
    }

    private function createInvoiceOutAttributes($apiKeyMs):void
    {
        $bodyAttributes = $this->getDocAttributes();
        $url = "https://api.moysklad.ru/api/remap/1.2/entity/factureout/metadata/attributes";
        $client = new MsClient($apiKeyMs);
        $this->getBodyToAdd($client, $url, $bodyAttributes);
    }

    public function setAllAttributesMs($data): void
    {
        $apiKeyMs = $data['tokenMs'];
        $accountId = $data['accountId'];

        try {
            $this->createProductAttributes($apiKeyMs);
            $this->createOrderAttributes($apiKeyMs);
            $this->createDemandAttributes($apiKeyMs);
            $this->createPaymentInAttributes($apiKeyMs);
            $this->createCashInAttributes($apiKeyMs);
            $this->createInvoiceOutAttributes($apiKeyMs);
        } catch (ClientException $e){
            dd($e, $e->getMessage());
        }
    }

    //returns doc attribute values
    public function getDocAttributes(): array
    {
        return [
            0 => [
                "name" => "Списание баллов (UDS)",
                "type" => "boolean",
                "required" => false,
                "show" => false,
                "description" => "Списание баллов (UDS)",
            ],
            1 => [
                "name" => "Начисление баллов (UDS)",
                "type" => "boolean",
                "required" => false,
                "show" => false,
                "description" => "Начисление баллов (UDS)",
            ],
            2 => [
                "name" => "Использование сертификата (UDS)",
                "type" => "boolean",
                "required" => false,
                "show" => false,
                "description" => "Использование сертификата (UDS)",
            ],
            3 => [
                "name" => "Количество списанных баллов (UDS)",
                "type" => "double",
                "required" => false,
                "show" => false,
                "description" => "Количество списанных баллов (UDS)",
            ],
            4 => [
                "name" => "Количество начисленных баллов (UDS)",
                "type" => "double",
                "required" => false,
                "show" => false,
                "description" => "Количество начисленных баллов (UDS)",
            ],
        ];
    }

    /**
     * @param MsClient $client
     * @param string $url
     * @param array $bodyAttributes
     * @return void
     */
    private function getBodyToAdd(MsClient $client, string $url, array $bodyAttributes): void
    {
        $json = $client->get($url);
        //$bodyToAdd = [];

        foreach ($bodyAttributes as $body) {
            $foundedAttrib = false;
            foreach ($json->rows as $row) {
                if ($body["name"] == $row->name) {
                    $foundedAttrib = true;
                    break;
                }
            }
            if (!$foundedAttrib) {
                $client->post($url,$body);
                //array_push($bodyToAdd, $body);
            }
        }
    }

}
