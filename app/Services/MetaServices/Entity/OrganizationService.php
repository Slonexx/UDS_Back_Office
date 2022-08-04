<?php

namespace App\Services\MetaServices\Entity;

use App\Components\MsClient;

class OrganizationService
{
    public function getOrganization($nameOrganization,$apiKey)
    {
        $uri = "https://online.moysklad.ru/api/remap/1.2/entity/organization?search=".$nameOrganization;
        $client = new MsClient($apiKey);
        $json = $client->get($uri);
        $foundedMeta = null;
        foreach($json->rows as $row){
            $foundedMeta = [
                "meta" => [
                    "href" => $row->meta->href,
                    "metadataHref" =>$row->meta->metadataHref,
                    "type" => $row->meta->type,
                    "mediaType" => $row->meta->mediaType,
                    "uuidHref" => $row->meta->uuidHref,
                ],
            ];
            break;
        }
        if (is_null($foundedMeta)){
            return $this->createOrganization($nameOrganization,$apiKey);
        } else return $foundedMeta;
    }

    public function createOrganization($nameOrganization,$apiKey)
    {
        $uri = "https://online.moysklad.ru/api/remap/1.2/entity/organization";
        $client = new MsClient($apiKey);
        $organization = [
            "name" => $nameOrganization,
        ];
        $createdMeta = $client->post($uri,$organization)->meta;

        return [
            "meta" => [
                "href" => $createdMeta->href,
                "metadataHref" =>$createdMeta->metadataHref,
                "type" => $createdMeta->type,
                "mediaType" => $createdMeta->mediaType,
                "uuidHref" => $createdMeta->uuidHref,
            ],
        ];
    }

    public function getOrganizationNameById($organizationId,$apiKey)
    {
        $uri = "https://online.moysklad.ru/api/remap/1.2/entity/organization/".$organizationId;
        $client = new MsClient($apiKey);
        $json = $client->get($uri);
        return $json->name;
    }

    public function getOrganizationAccountByNumber($organizationId,$number,$apiKey)
    {
        $uri = "https://online.moysklad.ru/api/remap/1.2/entity/organization/".$organizationId."/accounts";
        $client = new MsClient($apiKey);
        $json = $client->get($uri);
        $foundedMeta = null;
        foreach($json->rows as $row){
            if($row->accountNumber == $number){
                $foundedMeta = $row->meta;
                break;
            }
        }
        return [
            "meta" => $foundedMeta,
        ];
    }
}
