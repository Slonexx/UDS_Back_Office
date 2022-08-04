<?php

namespace App\Http\Controllers;

use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    private OrderService $orderService;

    /**
     * @param OrderService $orderService
     */
    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
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
        $request->validate([
            "tokenMs" => 'required|string',
            "companyId" => "required|string",
            "apiKeyUds" => "required|string",
        ]);
    }

}
