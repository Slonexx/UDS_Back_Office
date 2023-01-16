<?php

namespace App\Http\Controllers\BD;

use App\Http\Controllers\Controller;
use App\Models\SettingMain;
use Illuminate\Http\Request;

class update extends Controller
{
    public function SettingMainUpdate($accountId, $TokenMS, $companyId, $TokenUDS, $ProductFolder, $UpdateProduct, $Store){
        $SettingMain_update = SettingMain::query()->where('accountId', $accountId);
        $SettingMain_update->update([
            'TokenMoySklad' => $TokenMS,
            'companyId' => $companyId,
            'TokenUDS' => $TokenUDS,
            'ProductFolder' => $ProductFolder,
            'UpdateProduct' => $UpdateProduct,
            'Store' => $Store,
        ]);
    }
}
