<?php

use App\Http\Controllers\Web\SettingController;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\Web\indexController;
use \App\Http\Controllers\Config\DeleteVendorApiController;


Route::post('/CheckSave/{accountId}', [indexController::class, 'CheckSave'])->name('CheckSave');


Route::get('/', [indexController::class, 'index'])->name('index');
Route::get('/{accountId}', [indexController::class, 'show'])->name("indexMain");
Route::get('/Setting/{accountId}', [SettingController::class, 'index'])->name('indexSetting');
Route::get('/Setting/Document/{accountId}', [SettingController::class, 'indexDocument'])->name('indexDocument');
Route::get('/Setting/Add/{accountId}', [SettingController::class, 'indexAdd'])->name('indexAdd');


Route::post('/setSetting/{accountId}', [SettingController::class, 'postSettingIndex'])->name('setSettingIndex');


Route::get('DeleteVendorApi/{appId}/{accountId}', [DeleteVendorApiController::class, 'Delete'])->name('Delete');
