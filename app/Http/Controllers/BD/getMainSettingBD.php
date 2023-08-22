<?php

namespace App\Http\Controllers\BD;

use App\Http\Controllers\Controller;
use App\Models\SettingMain;
use GuzzleHttp\Exception\BadResponseException;

class getMainSettingBD extends Controller
{
    public mixed $accountId;
    public mixed $tokenMs;
    public mixed $companyId;
    public mixed $TokenUDS;



    public function __construct($accountId)
    {
        $this->accountId = $accountId;

        $find = SettingMain::query()->where('accountId', $accountId)->first();
        try {

            if ($find != []){
                $result = $find->getAttributes();
                $this->tokenMs = $result['TokenMoySklad'];
                $this->companyId = $result['companyId'];
                $this->TokenUDS = $result['TokenUDS'];
            } else {
                $this->tokenMs = null;
                $this->companyId = null;
                $this->TokenUDS = null;
            }
        } catch (BadResponseException $e) {
            $this->tokenMs = null;
            $this->companyId = null;
            $this->TokenUDS = null;
        }
    }

}
