<?php

use App\Http\Controllers\Web\SettingController;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\Web\indexController;


Route::get('/', [indexController::class, 'index'])->name('index');
Route::get('/{id}', [indexController::class, 'index'])->name("indexMain");
Route::get('/Setting/{id}', [SettingController::class, 'index'])->name('indexSetting');
Route::get('/Setting/Document/{id}', [SettingController::class, 'indexDocument'])->name('indexDocument');
Route::get('/Setting/Add/{id}', [SettingController::class, 'indexAdd'])->name('indexAdd');


Route::post('/setSetting/{id}', [SettingController::class, 'index'])->name('setSetting');
