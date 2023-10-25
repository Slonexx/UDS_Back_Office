<?php

namespace App\Services\newProductService;

use App\Components\MsClient;
use App\Components\UdsClient;
use App\Models\ProductFoldersByAccountID;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;
use stdClass;

class createProductForUDS
{

    private mixed $setting;
    private MsClient $msClient;
    private UdsClient $udsClient;

    public function __construct($data, $ms, $uds)
    {
        $this->setting = json_decode(json_encode($data));
        $this->msClient = $ms;
        $this->udsClient = $uds;
    }

    public function initialization(): array
    {
        $createProduct = new applicationCreatingProductForUDS($this->setting, $this->msClient, $this->udsClient);

        $ARR_PRODUCT = [];
        $find = ProductFoldersByAccountID::query()->where('accountId', $this->setting->accountId);
        $productCreatingForCheck = [];

        foreach ($find->get() as $itemFolderModel) {
            $folderName = $itemFolderModel->getAttributes()['FolderName'];
            if ($folderName == "Корневая папка") {
                $folderName = '';
            }
            $this->addCategoriesToUds($folderName);
            $productsMs = $this->getMs($folderName);

            foreach ($productsMs->rows as $item) {
                $create = $this->shouldCreateProduct($item);

                if ($create && strpos($item->pathName, $folderName) === 0 && substr_count($item->pathName, '/') < 3) {
                    if (property_exists($item, "attributes")) {
                        foreach ($item->attributes as $attribute) {
                            if ($attribute->name == "id (UDS)") {
                                $productCreatingForCheck[] = $item;
                                $create = false;
                            } elseif ($attribute->name == "Не выгружать товар в UDS ? (UDS)" && $attribute->value) {
                                $create = false;
                            }
                        }
                    }

                    if ($create) {
                        try {
                            $createdProduct = $createProduct->createProductUds($item);
                            if ($createdProduct) {
                                $ARR_PRODUCT[] = $createdProduct;
                            }
                        } catch (BadResponseException) {
                            continue;
                        }
                    }
                }
            }

            foreach ($productCreatingForCheck as $item) {
                if ($this->shouldCreateProductForCheck($item)) {
                    try {
                        $createdProduct = $createProduct->createProductUds($item);
                        if ($createdProduct) {
                            $ARR_PRODUCT[] = $createdProduct;
                        }
                    } catch (BadResponseException) {
                        continue;
                    }
                }
            }
        }

        return [
            "message" => "Successful export products to UDS",
            'Массив товаров' => $ARR_PRODUCT
        ];
    }


    private function shouldCreateProduct($item): bool
    {
        if ($this->setting->StoreRecord == '1') {
            $count = 0;
            foreach ($this->msClient->get("https://api.moysklad.ru/api/remap/1.2/report/stock/all?filter=store=https://api.moysklad.ru/api/remap/1.2/entity/store/" . $this->setting->Store . ";search=" . $item->name)->rows as $itemStock) {
                $count += $itemStock->quantity;
            }
            return $count > 0;
        }
        return true;
    }

    private function shouldCreateProductForCheck($item): bool
    {
        $create = true;
        foreach ($item->attributes as $attribute) {
            if ($attribute->name == "id (UDS)") {
                try {
                    $this->udsClient->get('https://api.uds.app/partner/v2/goods/' . $attribute->value);
                    $create = false;
                } catch (BadResponseException) {
                    $create = true;
                }
            } elseif ($attribute->name == "Не выгружать товар в UDS ? (UDS)") {
                if ($attribute->value) {
                    $create = false;
                }
            }
        }
        return $create;
    }



    private function getMs($folderName): stdClass
    {
        $urls = [
            '' => "https://api.moysklad.ru/api/remap/1.2/entity/product",
            'service' => "https://api.moysklad.ru/api/remap/1.2/entity/service"
        ];

        $result = new stdClass();
        $result->rows = [];

        foreach ($urls as $baseUrl) {
            $url = $baseUrl;
            if ($folderName !== '') {
                $url .= "?filter=pathName~" . $folderName;
            }

            $response = $this->msClient->get($url);
            $result->rows = array_merge($result->rows, $response->rows);
        }
        return $result;

    }

    private function addCategoriesToUds($pathName): void
    {
        $arrProductFolders = [];
        if ($pathName == null) {
            $tmp = $this->msClient->get('https://api.moysklad.ru/api/remap/1.2/entity/productfolder/')->rows;
            foreach ($tmp as $item) {
                if (substr_count($item->pathName, '/') < 2) {
                    $arrProductFolders[] = $item;
                }
            }
        } else {
            $tmp = $this->msClient->get('https://api.moysklad.ru/api/remap/1.2/entity/productfolder?filter=pathName~' . $pathName)->rows;
            foreach ($tmp as $item) {
                if (strpos($item->pathName, $pathName) === 0 and substr_count($item->pathName, '/') < 3) {
                    $arrProductFolders[] = $item;
                }
            }
        }
        usort($arrProductFolders, function ($a, $b) {
            $countA = substr_count($a->pathName, '/');
            $countB = substr_count($b->pathName, '/');

            if ($a->pathName === "") {
                return -1;
            } elseif ($b->pathName === "") {
                return 1;
            } elseif ($countA === 0 && $countB !== 0) {
                return -1;
            } elseif ($countA !== 1 && $countB === 1) {
                return 1;
            } elseif ($countA === 2 && $countB !== 2) {
                return -1;
            } elseif ($countA !== 3 && $countB === 3) {
                return 1;
            } else {
                return strcmp($a->pathName, $b->pathName);
            }
        });
        foreach ($arrProductFolders as $item) {
            $nameCategory = $item->name;

            if (preg_match('/^[0-9]+$/', $item->externalCode)) {
                try {
                    $this->udsClient->get('https://api.uds.app/partner/v2/goods/' . $item->externalCode);
                } catch (BadResponseException) {
                    if (property_exists($item, 'productFolder')) {
                        $idNodeCategory = $this->msClient->get($item->productFolder->meta->href)->externalCode;
                        if (preg_match('/^[0-9]+$/', $item->externalCode)) {
                            try {
                                $this->udsClient->get('https://api.uds.app/partner/v2/goods/' . $idNodeCategory);
                                $this->createCategoryUdsAndUpdateProductFolderForMS($nameCategory, $item->id, $idNodeCategory);
                            } catch (BadResponseException) {
                                $this->createCategoryUdsAndUpdateProductFolderForMS($nameCategory, $item->id);
                            }
                        } else {
                            $this->createCategoryUdsAndUpdateProductFolderForMS($nameCategory, $item->id );
                        }
                    } else {
                        $this->createCategoryUdsAndUpdateProductFolderForMS($nameCategory, $item->id);
                    }
                }
            } else {
                if (property_exists($item, 'productFolder')) {
                    $idNodeCategory = $this->msClient->get($item->productFolder->meta->href)->externalCode;
                    if (preg_match('/^[0-9]+$/', $item->externalCode)) {
                        try {
                            $this->udsClient->get('https://api.uds.app/partner/v2/goods/' . $idNodeCategory);
                            $this->createCategoryUdsAndUpdateProductFolderForMS($nameCategory, $item->id, $idNodeCategory);
                        } catch (BadResponseException) {
                            $this->createCategoryUdsAndUpdateProductFolderForMS($nameCategory, $item->id);
                        }
                    } else {
                        $this->createCategoryUdsAndUpdateProductFolderForMS($nameCategory, $item->id);
                    }
                } else {
                    $this->createCategoryUdsAndUpdateProductFolderForMS($nameCategory, $item->id);
                }
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
        ];
        if (intval($nodeId) > 0 || $nodeId != "") {
            $body["nodeId"] = intval($nodeId);
        }

        try {
            $udsBodyPost = $this->udsClient->postHttp_errorsNo('https://api.uds.app/partner/v2/goods', $body);
                if (property_exists($udsBodyPost, 'id')) {

                } else return;
        } catch (BadResponseException) {
            return;
        }


        try {
            $this->msClient->put("https://api.moysklad.ru/api/remap/1.2/entity/productfolder/" . $idMsProductFolder, [
                "externalCode" => "" . $udsBodyPost->id ,
            ]);
        } catch (ClientException) {
            return;
        }
    }

}
