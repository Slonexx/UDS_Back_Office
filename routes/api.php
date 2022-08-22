<?php

use App\Http\Controllers\AgentController;
use App\Http\Controllers\AttributeController;
use App\Http\Controllers\BackEnd\postController;
use App\Http\Controllers\Controller\V1\InputMcController;
use App\Http\Controllers\Controller\V1\UploadController;
use App\Http\Controllers\installContoller;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;


Route::get('/Input', [InputMcController::class, 'inputJsonMc']);
    Route::get('/UpLoad/{base_url}', [UploadController::class, 'ChangeFileUser']);

    Route::post('attributes',[AttributeController::class,'setAllAttributes']);

    Route::post('agentMs',[AgentController::class,'insertMs']);
   // Route::post('agentUds',[AgentController::class,'insertUds']);

    Route::post('productMs',[ProductController::class,'insertMs']);
    Route::post('productUds',[ProductController::class,'insertUds']);

    Route::post('updateProductUds',[ProductController::class,'updateUds']);
    Route::post('updateProductMs',[ProductController::class,'updateMs']);

    Route::post('updateOrdersMs',[OrderController::class,'updateMs']);


    Route::get('install',[installContoller::class,'install']);

    Route::post('/webhook/{accountId}/client',[postController::class, 'postClint']);
    Route::post('/webhook/{accountId}/order',[postController::class, 'postOrder']);

//Route::get('/Goods', [getApi::class, 'index']);


