<?php

namespace App\Services\MetaServices\MetaHook;

use App\Components\MsClient;

class AttributeHook
{
    private MsClient $msClient;

    public function __construct(MsClient $ms)
    {
        $this->msClient = $ms;
    }

    private function getAttribute($entityType, $nameAttribute)
    {
        $url = "https://api.moysklad.ru/api/remap/1.2/entity/{$entityType}/metadata/attributes";
        $json = $this->msClient->get($url);

        foreach ($json->rows as $row) {
            if ($row->name == $nameAttribute) {
                return $row->meta;
            }
        }

        return null;
    }

    public function getProductAttribute($nameAttribute)
    {
        return $this->getAttribute('product', $nameAttribute);
    }

    public function getOrderAttribute($nameAttribute)
    {
        return $this->getAttribute('customerorder', $nameAttribute);
    }

    public function getDemandAttribute($nameAttribute)
    {
        return $this->getAttribute('demand', $nameAttribute);
    }

    public function getPaymentInAttribute($nameAttribute)
    {
        return $this->getAttribute('paymentin', $nameAttribute);
    }

    public function getCashInAttribute($nameAttribute)
    {
        return $this->getAttribute('cashin', $nameAttribute);
    }

    public function getFactureOutAttribute($nameAttribute)
    {
        return $this->getAttribute('factureout', $nameAttribute);
    }
}
