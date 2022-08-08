<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WhatsappController extends Controller
{
    public function index($accountId){
        return view('web.Help.whatsapp', ['accountId' => $accountId] );
    }

    public function postWhatsappSend(Request $request, $accountId)
    {
        $request->validate([
            'name' => 'required|max:100',
            'message' => 'required|max:500',
        ]);

        $name = "Здравствуйте меня зовут " . $request->name . ". ";
        $inputName = str_ireplace(" ", "%20", $name);
        $inputMessage = str_ireplace(" ", "%20", $request->message);
        $message = "https://wa.me/87750498821?text=" . $inputName . $inputMessage;

        $time_url = "https://api.whatsapp.com/send/?phone=77232400545&text=$inputName $inputMessage";

        return redirect()->to($time_url);
    }
}
