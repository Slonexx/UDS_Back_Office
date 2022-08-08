<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Mail\supportMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SupportController extends Controller
{
    public function index(Request $request, $accountId){

        return view('web.Help.support', ['accountId' => $accountId] );
    }

}
