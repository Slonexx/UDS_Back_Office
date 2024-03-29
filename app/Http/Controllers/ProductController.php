<?php

namespace App\Http\Controllers;

use App\Services\product\ProductCreateMsService;
use App\Services\product\ProductCreateUdsService;
use App\Services\product\ProductUpdateMsService;
use App\Services\product\ProductUpdateUdsHiddenService;
use App\Services\product\ProductUpdateUdsService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    private ProductCreateMsService $productCreateMsService;
    private ProductCreateUdsService $productCreateUdsService;

    private ProductUpdateMsService $productUpdateMsService;
    private ProductUpdateUdsService $productUpdateUdsService;

    private ProductUpdateUdsHiddenService $ProductUpdateUdsHiddenService;



    public function __construct(
        ProductCreateMsService $productCreateMsService,
        ProductCreateUdsService $productCreateUdsService,
        ProductUpdateMsService $productUpdateMsService,
        ProductUpdateUdsService $productUpdateUdsService,
        ProductUpdateUdsHiddenService $ProductUpdateUdsHiddenService
    )
    {
        $this->productCreateMsService = $productCreateMsService;
        $this->productCreateUdsService = $productCreateUdsService;
        $this->productUpdateMsService = $productUpdateMsService;
        $this->productUpdateUdsService = $productUpdateUdsService;
        $this->ProductUpdateUdsHiddenService = $ProductUpdateUdsHiddenService;
    }


    public function insertMs(Request $request): \Illuminate\Http\Response|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        $data = $request->validate([
            "tokenMs" => 'required|string',
            "companyId" => "required|string",
            "apiKeyUds" => "required|string",
            "folder_id" => "required|string",
            "accountId" => "required|string",
        ]);

        return response(
            $this->productCreateMsService->insertToMs($data)
        );
    }

    public function insertUds(Request $request): \Illuminate\Http\Response|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        $data = $request->validate([
            "tokenMs" => 'required|string',
            "companyId" => "required|string",
            "apiKeyUds" => "required|string",
            "folder_id" => "required",
            "store" => "required|string",
            "accountId" => "required|string",
        ]);

        return response(
            $this->productCreateUdsService->insertToUds($data)
        );
    }

    public function insertUds_data($data): \Illuminate\Http\Response|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        return response(
            $this->productCreateUdsService->insertToUds($data)
        );
    }

    public function updateMs(Request $request): \Illuminate\Http\Response|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        $data = $request->validate([
            "tokenMs" => 'required|string',
            "companyId" => "required|string",
            "apiKeyUds" => "required|string",
            "accountId" => "required|string",
        ]);

        return response(
            $this->productUpdateMsService->updateProductsMs($data)
        );
    }

    public function updateMs_data($data): \Illuminate\Http\Response|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        return response(
            $this->productUpdateMsService->updateProductsMs($data)
        );
    }

    public function updateUds(Request $request): \Illuminate\Http\Response|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        $data = $request->validate([
            "tokenMs" => 'required|string',
            "companyId" => "required|string",
            "apiKeyUds" => "required|string",
            "folder_id" => "required|string",
            "store" => "required|string",
            "accountId" => "required|string"
        ]);

        return response(
            $this->productUpdateUdsService->updateProductsUds($data)
        );
    }

    public function updateUds_data($data): \Illuminate\Http\Response|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        return response(
            $this->productUpdateUdsService->updateProductsUds($data)
        );
    }

    public function productUdsHidden(Request $request){
        $data = $request->validate([
            "tokenMs" => 'required|string',
            "companyId" => "required|string",
            "apiKeyUds" => "required|string",
            "folder_id" => "required|string",
            "store" => "required|string",
            "accountId" => "required|string"
        ]);
        $this->ProductUpdateUdsHiddenService->insertUpdate($data);
    }

}
