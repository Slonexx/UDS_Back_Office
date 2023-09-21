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
            $MSCounterparty = $ClientMS->get('https://api.moysklad.ru/api/remap/1.2/entity/counterparty/' . $objectId);
        } catch (BadResponseException) {
            return response()->json(['Bool' => false]);
        }

        if (is_numeric($MSCounterparty->externalCode) && ctype_digit($MSCounterparty->externalCode) && $MSCounterparty->externalCode > 10000) {
            try {
                return response()->json([
                    'Bool' => true,
                    'customers' => $ClientUDS->get('https://api.uds.app/partner/v2/customers/' . $MSCounterparty->externalCode)
                ]);
            } catch (BadResponseException) {

                if (property_exists($MSCounterparty, 'phone')) {
                    $phone = "+7" . mb_substr(str_replace('+7', '', str_replace(" ", '', $MSCounterparty->phone)), -10);

                    try {
                        return response()->json([
                            'Bool' => true,
                            'customers' => $ClientMS->get('https://api.uds.app/partner/v2/customers/find?phone=' . $phone)->user
                        ]);

                    } catch (BadResponseException $e) {
                        return response()->json(['Bool' => false]);
                    }

                } else {
                    return response()->json(['Bool' => false]);
                }

            }

        } else {

            if (property_exists($MSCounterparty, 'phone')) {
                $phone = "%2b7" . mb_substr(str_replace('+7', '', str_replace(" ", '', $MSCounterparty->phone)), -10);
                try {
                    return response()->json([
                        'Bool' => true,
                        'customers' => $ClientUDS->get('https://api.uds.app/partner/v2/customers/find?phone=' . $phone)->user
                    ]);
                } catch (BadResponseException $e) {
                    return response()->json(['Bool' => false]);
                }

            } else {
                return response()->json(['Bool' => false]);
            }
        }
    }

}
