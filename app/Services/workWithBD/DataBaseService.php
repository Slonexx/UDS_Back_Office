<?php

namespace App\Services\workWithBD;

use App\Models\SettingMain;
use GuzzleHttp\Exception\BadResponseException;

class DataBaseService
{

    public static function showMainSetting($accountId): array
    {
        $find = SettingMain::query()->where('accountId', $accountId)->first();
        try {
            $result = $find->getAttributes();
        } catch (BadResponseException $e) {
            $result = [
                "accountId" => $accountId,
                "TokenMoySklad" => null,
                "companyId" => null,
                "TokenUDS" => null,
                "ProductFolder" => null,
                "UpdateProduct" => null,
                "Store" => null,
            ];
        }
        return $result;
    }

}
