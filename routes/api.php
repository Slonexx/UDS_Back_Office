<?php

use App\Http\Controllers\AgentController;
use App\Http\Controllers\AttributeController;
use App\Http\Controllers\BackEnd\postController;
use App\Http\Controllers\Controller\V1\InputMcController;
use App\Http\Controllers\Controller\V1\UploadController;
use App\Http\Controllers\installContoller;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\Web\ADD\DeleteALLProductForUDSController;
use App\Http\Controllers\Web\POST\WebhookMSController;
use App\Http\Controllers\Web\POST\WebhookMSProductController;
use App\Services\AdditionalServices\ImgService;
use Illuminate\Support\Facades\Route;


Route::get('/Input', [InputMcController::class, 'inputJsonMc']);
    Route::get('/UpLoad/{base_url}', [UploadController::class, 'ChangeFileUser']);

    Route::post('attributes',[AttributeController::class,'setAllAttributes']);

    Route::post('agentMs',[AgentController::class,'insertMs']);
   // Route::post('agentUds',[AgentController::class,'insertUds']);

    Route::post('productMs',[ProductController::class,'insertMs']);
    Route::post('productUds',[ProductController::class,'insertUds']);

    Route::post('productUdsHidden',[ProductController::class,'productUdsHidden']);

    Route::post('updateProductUds',[ProductController::class,'updateUds']);
    Route::post('updateProductMs',[ProductController::class,'updateMs']);

    Route::post('updateOrdersMs',[OrderController::class,'updateMs']);


    Route::get('install/{accountId}',[installContoller::class,'install']);

    Route::post('/webhook/{accountId}/client',[postController::class, 'postClint']);
    Route::post('/webhook/{accountId}/order',[postController::class, 'postOrder']);



    Route::post('/webhook/customerorder/',[WebhookMSController::class, 'customerorder']);
    Route::post('/webhook/demand/',[WebhookMSController::class, 'customerorder']);
    Route::post('/webhook/product/',[WebhookMSProductController::class, 'productUpdate']);
    Route::post('/webhook/productfolder/',[WebhookMSProductController::class, 'productFolderUpdate']);
    Route::post('/webhook/stock/',[WebhookMSProductController::class, 'productStock']);

    Route::post('/DeleteALLProductForUDSController/{as}/{accountId}',[DeleteALLProductForUDSController::class, 'DeleteALLProductForUDSController']);




