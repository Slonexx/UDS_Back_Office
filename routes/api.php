<?php

use App\Http\Controllers\Controller\V1\InputMcController;
use App\Http\Controllers\Controller\V1\getApi;
use Illuminate\Support\Facades\Route;


Route::get('/Input', [InputMcController::class, 'inputJsonMc']);
Route::get('/Goods', [getApi::class, 'index']);


