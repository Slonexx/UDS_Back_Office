<?php

namespace App\Http\Controllers\BD;

use App\Http\Controllers\Controller;
use App\Models\newProductModel;
use App\Models\SettingMain;
use GuzzleHttp\Exception\BadResponseException;

class newProductSettingBD extends Controller
{
    public mixed $accountId;
    public mixed $ProductFolder;
    public mixed $unloading;
    public mixed $salesPrices;
    public mixed $promotionalPrice;
    public mixed $Store;
    public mixed $StoreRecord;
    public mixed $productHidden;
    public mixed $countRound;

    public function __construct($accountId)
    {
        $this->accountId = $accountId;

        $find = newProductModel::query()->where('accountId', $accountId)->first();
        try {

            if ($find != []){
                $result = $find->getAttributes();
                $this->ProductFolder = $result['ProductFolder'];
                $this->unloading = $result['unloading'];
                $this->salesPrices = $result['salesPrices'];
                $this->promotionalPrice = $result['promotionalPrice'];
                $this->Store = $result['Store'];
                $this->StoreRecord = $result['StoreRecord'];
                $this->productHidden = $result['productHidden'];
                $this->countRound = $result['countRound'];
            } else {
                $this->ProductFolder = null;
                $this->unloading = null;
                $this->salesPrices = null;
                $this->promotionalPrice = null;
                $this->Store = null;
                $this->StoreRecord = null;
                $this->productHidden = null;
                $this->countRound = null;
            }
        } catch (BadResponseException $e) {
            $this->ProductFolder = null;
            $this->unloading = null;
            $this->salesPrices = null;
            $this->promotionalPrice = null;
            $this->Store = null;
            $this->StoreRecord = null;
            $this->productHidden = null;
            $this->countRound = null;
        }
    }

}
