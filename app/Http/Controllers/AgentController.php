<?php

namespace App\Http\Controllers;

use App\Services\AgentService;
use Illuminate\Http\Request;

class AgentController extends Controller
{
    private AgentService $agentService;

    /**
     * @param AgentService $agentService
     */
    public function __construct(AgentService $agentService)
    {
        $this->agentService = $agentService;
    }

    public function insertMs(Request $request)
    {
        $data = $request->validate([
            "tokenMs" => 'required|string',
            "companyId" => "required|string",
            "apiKeyUds" => "required|string",
        ]);
        //dd($data);
        return response(
            $this->agentService->insertToMs($data)
        );
    }

//    public function insertUds(Request $request)
//    {
//        $request->validate([
//            "tokenMs" => 'required|string',
//            "companyId" => "required|string",
//            "apiKeyUds" => "required|string",
//        ]);
//    }

}
