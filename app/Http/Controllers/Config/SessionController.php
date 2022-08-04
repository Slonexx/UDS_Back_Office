<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\AgentAttributesController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\OrderAttributesController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\PriceTypeController;
use App\Http\Controllers\ProductAttributesController;
use App\Http\Controllers\SalesChannelController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\UomController;
use Illuminate\Http\Request;

class SessionController extends Controller
{

    private $ApiKey;

    public function SessionInitialization($ApiKey){
        session_start();
        
        $_SESSION["store"] = app(StoreController::class)->getKaspiStore($ApiKey);
        $_SESSION["pricetype"] = app(PriceTypeController::class)->getPriceType($ApiKey);
        $_SESSION["uom"] = app(UomController::class)->getUom($ApiKey);
        $_SESSION["salechannel"] = app(SalesChannelController::class)->getSaleChannel($ApiKey);
        $_SESSION["organization"] = app(OrganizationController::class)->getKaspiOrganization($ApiKey);
        $_SESSION["currency"] = app(CurrencyController::class)->getKzCurrency($ApiKey);
        $_SESSION["gos_attribute"] = app(AgentAttributesController::class)->getAttributeGos($ApiKey);
        $_SESSION["brand"] = app(ProductAttributesController::class)->getAttribute('brand (KASPI)',$ApiKey);
        $_SESSION["export"] = app(ProductAttributesController::class)->getAttribute('Добавлять товар на Kaspi',$ApiKey);

        $this->ApiKey = $ApiKey;
        /*$Organization = app(OrganizationController::class)->getKaspiOrganization($ApiKey);
        $result = session(["Store" => $store, "Organization" => $Organization, ]);
        session()->save();*/

        //return $result;
    }

    public function getCookie($name_cookie){
        session_start();

        if(isset($_SESSION[$name_cookie])) {
           return $_SESSION[$name_cookie];
        } else {
            $createdMeta = $this->createMetaByName($name_cookie);
            $_SESSION[$name_cookie] = $createdMeta;
            return $createdMeta;
        }
    }

    private function createMetaByName($metaName)
    {
        switch ($metaName) {
            case 'store':
                return  app(StoreController::class)->getKaspiStore($this->ApiKey);
            break;
            case 'pricetype':
                return app(PriceTypeController::class)->getPriceType($this->ApiKey);
            break;
            case 'uom':
                return app(UomController::class)->getUom($this->ApiKey);
            break;
            case 'salechannel':
                return app(SalesChannelController::class)->getSaleChannel($this->ApiKey);
            break;
            case 'organization':
                return app(OrganizationController::class)->getKaspiOrganization($this->ApiKey);
            break;
            case 'currency':
                return app(CurrencyController::class)->getKzCurrency($this->ApiKey);
            break;
            case 'gos_attribute':
                return app(AgentAttributesController::class)->getAttributeGos($this->ApiKey);
            break;
            case 'brand':
                return app(ProductAttributesController::class)->getAttribute('brand (KASPI)',$this->ApiKey);
            break;
            case 'export':
                return app(ProductAttributesController::class)->getAttribute('Добавлять товар на Kaspi',$this->ApiKey);
            break;
        }
    }



}
