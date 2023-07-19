<?php

namespace App\Services\WebhookMS;


use App\Components\MsClient;
use GuzzleHttp\Exception\BadResponseException;

class WebHookNewClientMS
{
    public function initiation(MsClient $Client, mixed $search, mixed $request)
    {
        try {
            if ($search->rows != []){
                $body = [
                    "name" => $request->displayName,
                    "phone" => (string) $request->phone,
                    "email" =>  $request->email ?? '',
                    "externalCode" => (string) $this->postClintId($Client, $request->participant['id']),
                ];
                return $Client->post( 'https://online.moysklad.ru/api/remap/1.2/entity/counterparty', $body);
            }
        } catch (BadResponseException $e){
            return $e;
        }
        return "200";
    }

    private function postClintId(MsClient $Client, $externalCode){
        $body = $Client->get('https://online.moysklad.ru/api/remap/1.2/entity/counterparty?filter=externalCode='.$externalCode);
        return array_key_exists(1, $body) ? null : $externalCode;
    }

}
