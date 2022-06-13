<?php

namespace App\Http\Controllers\Controller\V1;

use App\Http\Controllers\Controller;
use App\Models\goods;

class getApi extends Controller
{
    public function index()
    {
        return response()->json([
             "" => goods::all()
        ],200);
    }
}
