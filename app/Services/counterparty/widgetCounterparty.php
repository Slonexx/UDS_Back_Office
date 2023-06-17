<?php

namespace App\Services\counterparty;

use App\Components\MsClient;
use App\Components\UdsClient;
use App\Http\Controllers\BD\getMainSettingBD;
use GuzzleHttp\Exception\BadResponseException;

class widgetCounterparty
{
    public function getInformation(string $accountId, string $objectId): \Illuminate\Http\JsonResponse
    {
        $Setting = new getMainSettingBD($accountId);
        $ClientMS = new MsClient($Setting->tokenMs);
        $ClientUDS = new UdsClient($Setting->companyId, $Setting->TokenUDS);

        try {
            $MSCounterparty = $ClientMS->get('https://online.moysklad.ru/api/remap/1.2/entity/counterparty/' . $objectId);
        } catch (BadResponseException $e) {
            return response()->json(['Bool' => false]);
        }
        if (is_numeric($MSCounterparty->externalCode) && ctype_digit($MSCounterparty->externalCode) && $MSCounterparty->externalCode > 10000) {

            try {
                return response()->json([
                    'Bool' => true,
                    'customers' => $ClientUDS->get('https://api.uds.app/partner/v2/customers/' . $MSCounterparty->externalCode)
                ]);
            } catch (BadResponseException) {
                return response()->json(['Bool' => false]);
            }

        } else return response()->json(['Bool' => false]);
    }

}
