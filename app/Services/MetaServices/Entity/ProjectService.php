<?php

namespace App\Services\MetaServices\Entity;

use App\Components\MsClient;

class ProjectService
{
    public function getProject($projectName,$apiKey)
    {
        $uri = "https://online.moysklad.ru/api/remap/1.2/entity/project?search=".$projectName;
        $client = new MsClient($apiKey);
        $jsonProjects = $client->get($uri);
        $foundedMeta = null;
        foreach($jsonProjects->rows as $row){
            $foundedMeta = $row->meta;
            break;
        }
        return [
            "meta" => $foundedMeta,
        ];
    }
}
