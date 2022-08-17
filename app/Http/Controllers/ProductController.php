<?php

namespace App\Http\Controllers;

use App\Services\product\ProductCreateMsService;
use App\Services\product\ProductCreateUdsService;
use App\Services\product\ProductUpdateMsService;
use App\Services\product\ProductUpdateUdsService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    private ProductCreateMsService $productCreateMsService;
    private ProductCreateUdsService $productCreateUdsService;

    private ProductUpdateMsService $productUpdateMsService;
    private ProductUpdateUdsService $productUpdateUdsService;

    /**
     * @param ProductCreateMsService $productCreateMsService
     * @param ProductCreateUdsService $productCreateUdsService
     * @param ProductUpdateMsService $productUpdateMsService
     * @param ProductUpdateUdsService $productUpdateUdsService
     */
    public function __construct(
        ProductCreateMsService $productCreateMsService,
        ProductCreateUdsService $productCreateUdsService,
        ProductUpdateMsService $productUpdateMsService,
        ProductUpdateUdsService $productUpdateUdsService
    )
    {
        $this->productCreateMsService = $productCreateMsService;
        $this->productCreateUdsService = $productCreateUdsService;
        $this->productUpdateMsService = $productUpdateMsService;
        $this->productUpdateUdsService = $productUpdateUdsService;
    }


    public function insertMs(Request $request)
    {
        $data = $request->validate([
            "tokenMs" => 'required|string',
            "companyId" => "required|string",
            "apiKeyUds" => "required|string",
        ]);

       // dd(100/10.0);

        return response(
            $this->productCreateMsService->insertToMs($data)
        );
    }

    public function insertUds(Request $request)
    {
        $data = $request->validate([
            "tokenMs" => 'required|string',
            "companyId" => "required|string",
            "apiKeyUds" => "required|string",
        ]);

       return response(
           $this->productCreateUdsService->insertToUds($data)
       );
    }

    public function updateMs(Request $request)
    {
        $data = $request->validate([
            "tokenMs" => 'required|string',
            "companyId" => "required|string",
            "apiKeyUds" => "required|string",
        ]);

       // dd(100/10.0);

        return response(
            $this->productUpdateMsService->updateProductsMs($data)
        );
    }

    public function updateUds(Request $request)
    {
        $data = $request->validate([
            "tokenMs" => 'required|string',
            "companyId" => "required|string",
            "apiKeyUds" => "required|string",
        ]);

       return response(
           $this->productUpdateUdsService->updateProductsUds($data)
       );
    }
}
