<?php

namespace App\Http\Controllers;

use App\Services\ProductService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    private ProductService $productService;

    /**
     * @param ProductService $productService
     */
    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }


    public function insertMs(Request $request)
    {
        $request->validate([
            "tokenMs" => 'required|string',
            "companyId" => "required|string",
            "apiKeyUds" => "required|string",
        ]);

    }

    public function insertUds(Request $request)
    {
        $data = $request->validate([
            "tokenMs" => 'required|string',
            "companyId" => "required|string",
            "apiKeyUds" => "required|string",
        ]);

        //$this->productService->insertToUds($data);
    }
}
