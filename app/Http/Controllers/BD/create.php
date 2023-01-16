<?php

namespace App\Http\Controllers\BD;

use App\Http\Controllers\Controller;
use App\Models\SettingMain;
use Illuminate\Http\Request;

class create extends Controller
{
    public function SettingMainCreate($accountId, $TokenMS, $companyId, $TokenUDS, $ProductFolder, $UpdateProduct, $Store){
        SettingMain::create([
            'accountId' => $accountId,
            'TokenMoySklad' => $TokenMS,
            'companyId' => $companyId,
            'TokenUDS' => $TokenUDS,
            'ProductFolder' => $ProductFolder,
            'UpdateProduct' => $UpdateProduct,
            'Store' => $Store,
        ]);
    }
}
