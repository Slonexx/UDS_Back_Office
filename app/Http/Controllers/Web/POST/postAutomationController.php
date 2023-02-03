<?php

namespace App\Http\Controllers\Web\POST;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class postAutomationController extends Controller
{
    public function postSettingAdd(Request $request,  $accountId, $isAdmin){

       dd($request->all());


        return  redirect()->route('getAutomation', [
            'accountId' => $accountId,
            'isAdmin' => $isAdmin,
        ]);
    }
}
