<?php

namespace App\Http\Controllers;

use App\Services\AttributeService;
use Illuminate\Http\Request;

class AttributeController extends Controller
{
    private AttributeService $attributeService;

    /**
     * @param AttributeService $attributeService
     */
    public function __construct(AttributeService $attributeService)
    {
        $this->attributeService = $attributeService;
    }

    public function setAllAttributes(Request $request)
    {
        $request->validate([
            "tokenMs" => 'required|string',
        ]);

        $this->attributeService->setAllAttributesMs($request->input('tokenMs'));

    }


}
