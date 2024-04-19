<?php

namespace App\Services\newProductService;

use App\Components\MsClient;
use App\Components\UdsClient;
use App\Http\Controllers\BD\getMainSettingBD;
use App\Models\ProductFoldersByAccountID;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Str;
use stdClass;

class folderCreating
{
    private MsClient $msClient;
    private UdsClient $udsClient;

    public function __construct(MsClient $msClient, udsClient $udsClient)
    {
        $this->msClient = $msClient;
        $this->udsClient = $udsClient;
    }

    public function addCategoriesToUds($pathName): void
    {
        $arrProductFolders = [];
        if ($pathName == null) {
            $tmp = $this->msClient->get('https://api.moysklad.ru/api/remap/1.2/entity/productfolder/')->rows;
            foreach ($tmp as $item) if (substr_count($item->pathName, '/') < 2) $arrProductFolders[] = $item;
        } else {
            $tmp = $this->msClient->get('https://api.moysklad.ru/api/remap/1.2/entity/productfolder?filter=pathName~' . $pathName)->rows;
            foreach ($tmp as $item) if (strpos($item->pathName, $pathName) === 0 and substr_count($item->pathName, '/') < 3) $arrProductFolders[] = $item;
        }
        usort($arrProductFolders, function ($a, $b) {
            $countA = substr_count($a->pathName, '/');
            $countB = substr_count($b->pathName, '/');

            if ($a->pathName === "") return -1;
            elseif ($b->pathName === "") return 1;
            elseif ($countA === 0 && $countB !== 0) return -1;
            elseif ($countA !== 1 && $countB === 1) return 1;
            elseif ($countA === 2 && $countB !== 2) return -1;
            elseif ($countA !== 3 && $countB === 3) return 1;
            else return strcmp($a->pathName, $b->pathName);
        });

        //dd($arrProductFolders);


        foreach ($arrProductFolders as $item) {
            //$this->msClient->newPUT("https://api.moysklad.ru/api/remap/1.2/entity/productfolder/" .  $item->id, ["externalCode" => Str::uuid()->toString()]);

            $nameCategory = $item->name;
            $idCategory = $item->id;
            if (preg_match('/^[0-9]+$/', $item->externalCode)) {
                $getCategory = $this->udsClient->newGET('https://api.uds.app/partner/v2/goods/' . $item->externalCode);
                if ($getCategory->status === false) {
                    $this->processCategory($item, $nameCategory, $idCategory);
                }
                else continue;
            }
            else {
                $this->processCategory($item, $nameCategory, $idCategory);
            }
        }
    }

    private function processCategory($item, $nameCategory, $idCategory): void
    {
        if (property_exists($item, 'productFolder')) {
            $idNodeCategory = $this->msClient->get($item->productFolder->meta->href);
            //Проверка на ветку
            if (preg_match('/^[0-9]+$/', $idNodeCategory->externalCode)) {
                $getCategory = $this->udsClient->newGET('https://api.uds.app/partner/v2/goods/' . $idNodeCategory->externalCode);
                if ($getCategory->status === false) {

                    if ($getCategory->code === 404) {
                        $this->createCategoryUdsAndUpdateProductFolderForMS($idNodeCategory->name, $idNodeCategory->id, '');
                        $idNodeCategory = $this->msClient->get($item->productFolder->meta->href);
                        $this->createCategoryUdsAndUpdateProductFolderForMS($nameCategory, $idCategory, $idNodeCategory->externalCode);
                    }
                }
                else $this->createCategoryUdsAndUpdateProductFolderForMS($nameCategory, $idCategory, $idNodeCategory->externalCode);// раб
            } //если нет, то создаем ветку и под ветку
            else {
                $this->createCategoryUdsAndUpdateProductFolderForMS($idNodeCategory->name, $idNodeCategory->externalCode);
                $idNodeCategory = $this->msClient->get($item->productFolder->meta->href);
                $this->createCategoryUdsAndUpdateProductFolderForMS($nameCategory, $idCategory, $idNodeCategory->externalCode);
            }
        }
        else {
            $idNodeCategory = $this->msClient->get($item->productFolder->meta->href);
            //Проверка на ветку
            if (preg_match('/^[0-9]+$/', $idNodeCategory->externalCode)) {
                $getCategory = $this->udsClient->newGET('https://api.uds.app/partner/v2/goods/' . $idNodeCategory->externalCode);
                if ($getCategory->status === false) {

                    if ($getCategory->code === 404) {
                        $this->createCategoryUdsAndUpdateProductFolderForMS($idNodeCategory->name, $idNodeCategory->id, '');
                        $idNodeCategory = $this->msClient->get($item->productFolder->meta->href);
                        $this->createCategoryUdsAndUpdateProductFolderForMS($nameCategory, $idCategory, $idNodeCategory->externalCode);
                    }
                }
                else $this->createCategoryUdsAndUpdateProductFolderForMS($nameCategory, $idCategory, $idNodeCategory->externalCode);// раб
            } //если нет, то создаем ветку и под ветку
            else {
                $this->createCategoryUdsAndUpdateProductFolderForMS($idNodeCategory->name, $idNodeCategory->externalCode);
                $idNodeCategory = $this->msClient->get($item->productFolder->meta->href);
                $this->createCategoryUdsAndUpdateProductFolderForMS($nameCategory, $idCategory, $idNodeCategory->externalCode);
            }

        }
    }


    private function createCategoryUdsAndUpdateProductFolderForMS($nameCategory, $idMsProductFolder, $nodeId = ""): void
    {
        $body = [
            "name" => $nameCategory,
            "data" => [
                "type" => "CATEGORY",
            ],
            "externalId" => $idMsProductFolder,
        ];
        if (intval($nodeId) > 0 || $nodeId != "") $body["nodeId"] = intval($nodeId);
        $udsBodyPost = $this->udsClient->newPOST('https://api.uds.app/partner/v2/goods', $body);
        if ($udsBodyPost->status) $this->msClient->newPUT("https://api.moysklad.ru/api/remap/1.2/entity/productfolder/" . $idMsProductFolder, ["externalCode" => "" . $udsBodyPost->data->id]);
    }
}
