<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TestController;
use App\Http\Controllers\Controller\V1\getApi;
use App\Http\Controllers\Controller\V1\UploadController;
use App\Http\Controllers\Controller\V1\InputMcController;


    Route::get('/Input', [InputMcController::class, 'inputJsonMc']);
    Route::get('/UpLoad/{base_url}', [UploadController::class, 'ChangeFileUser']);

    Route::post('test',[TestController::class, 'test']);

//Route::get('/Goods', [getApi::class, 'index']);


