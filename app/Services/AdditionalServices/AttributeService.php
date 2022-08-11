<?php

namespace App\Services\AdditionalServices;

use App\Components\MsClient;

class AttributeService
{


    private function createProductAttributes($apiKeyMs): void
    {
        $bodyAttributes = [
            0 => [
                "name" => "% списания (UDS)",
                "type" => "double",
                "required" => false,
                "description" => "Процент списания (UDS)",
            ],
            1 => [
                "name" => "% начисления (UDS)",
                "type" => "double",
                "required" => false,
                "description" => "Процент начисления (UDS)",
            ],
            2 => [
                "name" => "Акционный товар (UDS)",
                "type" => "boolean",
                "required" => false,
                "description" => "Акционный товар (UDS)",
            ],
            3 => [
                "name" => "Не применять бонусную программу (UDS)",
                "type" => "boolean",
                "required" => false,
                "description" => "Не применять бонусную программу (UDS)",
            ],
            4 => [
                "name" => "Дробное значение товара (UDS)",
                "type" => "boolean",
                "required" => false,
                "description" => "Дробное значение товара (UDS)",
            ],
            5 => [
                "name" => "Шаг дробного значения (UDS)",
                "type" => "double",
                "required" => false,
                "description" => "Шаг дробного значения (UDS)",
            ],
            6 => [
                "name" => "Минимальный размер заказа дробного товара (UDS)",
                "type" => "double",
                "required" => false,
                "description" => "Минимальный размер заказа дробного товара (UDS)",
            ],
            7 => [
                "name" => "Цена минимального размера заказа дробного товара (UDS)",
                "type" => "double",
                "required" => false,
                "description" => "Цена минимального размера заказа дробного товара (UDS)",
            ],
            8 => [
                "name" => "id (UDS)",
                "type" => "string",
                "required" => false,
                "description" => "id (UDS)",
            ],
            9 => [
                "name" => "Ограничен товар (UDS)",
                "type" => "boolean",
                "required" => false,
                "description" => "Ограничен товар (UDS)",
            ],
        ];

        $url = "https://online.moysklad.ru/api/remap/1.2/entity/product/metadata/attributes";
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

        $url = "https://online.moysklad.ru/api/remap/1.2/entity/counterparty/metadata/attributes";
        $client = new MsClient($apiKeyMs);
        $this->getBodyToAdd($client, $url, $bodyAttributes);
    }

    private function createOrderAttributes($apiKeyMs): void
    {
        $bodyAttributes = $this->getDocAttributes();
        $bodyAttributes[5] = [
            "name" => "Ссылка на заказ (UDS)",
            "type" => "link",
            "required" => false,
            "description" => "Ссылка на заказ (UDS)",
        ];

        $url = "https://online.moysklad.ru/api/remap/1.2/entity/customerorder/metadata/attributes";
        $client = new MsClient($apiKeyMs);
        $this->getBodyToAdd($client, $url, $bodyAttributes);
    }

    private function createDemandAttributes($apiKeyMs): void
    {
        $bodyAttributes = $this->getDocAttributes();
        $url = "https://online.moysklad.ru/api/remap/1.2/entity/demand/metadata/attributes";
        $client = new MsClient($apiKeyMs);
        $this->getBodyToAdd($client, $url, $bodyAttributes);
    }

    private function createPaymentInAttributes($apiKeyMs):void
    {
        $bodyAttributes = $this->getDocAttributes();
        $url = "https://online.moysklad.ru/api/remap/1.2/entity/paymentin/metadata/attributes";
        $client = new MsClient($apiKeyMs);
        $this->getBodyToAdd($client, $url, $bodyAttributes);
    }

    private function createCashInAttributes($apiKeyMs):void
    {
        $bodyAttributes = $this->getDocAttributes();
        $url = "https://online.moysklad.ru/api/remap/1.2/entity/cashin/metadata/attributes";
        $client = new MsClient($apiKeyMs);
        $this->getBodyToAdd($client, $url, $bodyAttributes);
    }

    private function createInvoiceOutAttributes($apiKeyMs):void
    {
        $bodyAttributes = $this->getDocAttributes();
        $url = "https://online.moysklad.ru/api/remap/1.2/entity/invoiceout/metadata/attributes";
        $client = new MsClient($apiKeyMs);
        $this->getBodyToAdd($client, $url, $bodyAttributes);
    }

    public function setAllAttributesMs($apiKeyMs): void
    {
        $this->createProductAttributes($apiKeyMs);
        //$this->createAgentAttributes($apiKeyMs);
        //$this->createOrderAttributes($apiKeyMs);
       // $this->createDemandAttributes($apiKeyMs);
       // $this->createPaymentInAttributes($apiKeyMs);
      //  $this->createCashInAttributes($apiKeyMs);
       // $this->createInvoiceOutAttributes($apiKeyMs);
    }

    //returns doc attribute values
    public function getDocAttributes(): array
    {
        return [
            0 => [
                "name" => "Списание баллов (UDS)",
                "type" => "boolean",
                "required" => false,
                "description" => "Списание баллов (UDS)",
            ],
            1 => [
                "name" => "Начисление баллов (UDS)",
                "type" => "boolean",
                "required" => false,
                "description" => "Начисление баллов (UDS)",
            ],
            2 => [
                "name" => "Покупка сертификата (UDS)",
                "type" => "boolean",
                "required" => false,
                "description" => "Покупка сертификата (UDS)",
            ],
            3 => [
                "name" => "Использование сертификата (UDS)",
                "type" => "boolean",
                "required" => false,
                "description" => "Использование сертификата (UDS)",
            ],
            4 => [
                "name" => "Ссылка на операцию (UDS)",
                "type" => "link",
                "required" => false,
                "description" => "Ссылка на операцию (UDS)",
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

        //dd($bodyToAdd);

//        if (count($bodyToAdd) > 0) {
//            $client->multiPost($url, $bodyToAdd);
//        }
        //return $bodyToAdd;
    }

}
