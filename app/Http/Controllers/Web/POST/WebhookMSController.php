<?php

namespace App\Http\Controllers\Web\POST;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WebhookMSController extends Controller
{
    public function customerorder(Request $request, $requestId){

        return response('',200)->json();
    }
}
