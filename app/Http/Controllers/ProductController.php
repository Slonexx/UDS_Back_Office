<?php

namespace App\Http\Controllers;

use App\Services\product\ProductCreateMsService;
use App\Services\product\ProductCreateUdsService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    private ProductCreateMsService $productCreateMsService;
    private ProductCreateUdsService $productCreateUdsService;

    /**
     * @param ProductCreateMsService $productCreateMsService
     * @param ProductCreateUdsService $productCreateUdsService
     */
    public function __construct(
        ProductCreateMsService $productCreateMsService,
        ProductCreateUdsService $productCreateUdsService
    )
    {
        $this->productCreateMsService = $productCreateMsService;
        $this->productCreateUdsService = $productCreateUdsService;
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
}
