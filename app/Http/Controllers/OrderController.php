<?php

namespace App\Http\Controllers;

use App\Services\order\OrderUpdateMsService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    private OrderUpdateMsService $orderService;

    /**
     * @param OrderUpdateMsService $orderService
     */
    public function __construct(OrderUpdateMsService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function updateMs(Request $request)
    {
       $data = $request->validate([
            "tokenMs" => 'required|string',
            "companyId" => "required|string",
            "apiKeyUds" => "required|string",
            "accountId" => "required|string",
            "paymentOpt" => "required|integer",
            "demandOpt" => "required|integer",
        ]);

       $this->orderService->updateOrdersMs($data);

    }

    public function updateMs_data($data)
    {
        $this->orderService->updateOrdersMs($data);

    }

}
