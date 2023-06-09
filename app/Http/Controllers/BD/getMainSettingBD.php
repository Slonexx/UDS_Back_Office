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
    public mixed $ProductFolder;
    public mixed $UpdateProduct;
    public mixed $hiddenProduct;

    public function __construct($accountId)
    {
        $this->accountId = $accountId;

        $find = SettingMain::query()->where('accountId', $accountId)->first();
        try {
            $result = $find->getAttributes();
            $this->tokenMs = $result['TokenMoySklad'];
            $this->companyId = $result['companyId'];
            $this->TokenUDS = $result['TokenUDS'];
            $this->ProductFolder = $result['ProductFolder'];
            $this->UpdateProduct = $result['UpdateProduct'];
            $this->Store = $result['Store'];
            $this->hiddenProduct = $result['hiddenProduct'];
        } catch (BadResponseException $e) {
            $result = $find->getAttributes();
            $this->tokenMs = null;
            $this->companyId = null;
            $this->TokenUDS = null;
            $this->ProductFolder = null;
            $this->UpdateProduct = null;
            $this->Store = null;
            $this->hiddenProduct = null;
        }
    }

}
