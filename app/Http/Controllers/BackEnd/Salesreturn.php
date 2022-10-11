<?php

namespace App\Http\Controllers\BackEnd;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class Salesreturn extends Controller
{
    public function SalesreturnObject($accountId, $entity, $objectId){
        dd($accountId, $entity, $objectId);
    }
}
