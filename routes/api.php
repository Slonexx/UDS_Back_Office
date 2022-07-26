<?php

use App\Http\Controllers\Controller\V1\InputMcController;
use App\Http\Controllers\Controller\V1\getApi;
use App\Http\Controllers\Controller\V1\UploadController;
use Illuminate\Support\Facades\Route;


    Route::get('/Input', [InputMcController::class, 'inputJsonMc']);
    Route::get('/UpLoad/{base_url}', [UploadController::class, 'ChangeFileUser']);

//Route::get('/Goods', [getApi::class, 'index']);


