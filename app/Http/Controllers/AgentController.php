<?php

namespace App\Http\Controllers;

use App\Services\agent\AgentService;
use Illuminate\Http\Request;

class AgentController extends Controller
{
    public function insertMs(Request $request)
    {
        $data = $request->validate([
            "tokenMs" => 'required|string',
            "companyId" => "required|string",
            "apiKeyUds" => "required|string",
            "accountId" => "required|string"
        ]);
        return response(
            app(AgentService::class)->insertToMs($data)
        );
    }

    public function insert($data): \Illuminate\Http\Response|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        return response(
            $this->agentService->insertToMs($data)
        );
    }

}
