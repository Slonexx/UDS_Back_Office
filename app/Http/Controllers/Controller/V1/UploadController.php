<?php

namespace App\Http\Controllers\Controller\V1;
use App\Components\ImportDateHttpClient;
use App\Models\images;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\File;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{

    public function ChangeFileUser($base_url){



    }

    public function DownloadImageMC($login, $password, $image, $imageName){

        $opts = array(
            'http' => array(
                'method' => 'GET',
                'header' =>
                    "Content-Type: application/json\r\n" .
                    "Authorization: Basic ". base64_encode("$login:$password")."\r\n" ,
                'content' => $image,
                'ignore_errors' => true
            )
        );

        $context = stream_context_create($opts);
        $contents = file_get_contents($image, false, $context);
        Storage::put("public/".$imageName, $contents);

        preg_match('/([0-9])\d+/',$http_response_header[0],$matches);
        $responsecode = intval($matches[0]);

        if ($responsecode == 200){
            $message = "image downloaded";
        } else $message = "DONT image downloaded";

        $out["Status code"] = $responsecode;
        $out["Message"] = $message;
        return $out;
    }



}
