<?php

use App\Http\Controllers\BackEnd\Demand;
use App\Http\Controllers\BackEnd\ObjectController;
use App\Http\Controllers\BackEnd\Salesreturn;
use App\Http\Controllers\Web\ADD\ApplicationController;
use App\Http\Controllers\Web\employees;
use App\Http\Controllers\Web\GET\getController;
use App\Http\Controllers\Web\POST\postController;
use App\Http\Controllers\Web\sendOperations;
use App\Http\Controllers\Web\SettingController;
use App\Http\Controllers\Web\RewardController;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\Web\indexController;
use \App\Http\Controllers\Config\DeleteVendorApiController;


Route::post('/CheckSave/{accountId}', [indexController::class, 'CheckSave'])->name('CheckSave');


Route::get('/Counterparty', [indexController::class, 'counterparty']);
Route::get('/CustomerOrderEdit', [indexController::class, 'CustomerOrderEdit']);
Route::get('/DemandEdit', [indexController::class, 'DemandEdit']);
Route::get('/SalesreturnEdit', [indexController::class, 'SalesreturnEdit']);


Route::get('/CounterpartyObject/{accountId}/{entity}/{objectId}', [ObjectController::class, 'CounterpartyObject']);
Route::get('/Accrue/{accountId}/{points}/{participants}', [RewardController::class, 'Accrue']);
Route::get('/Cancellation/{accountId}/{points}/{participants}', [RewardController::class, 'Cancellation']);


Route::get('/CustomerOrderEditObject/{accountId}/{entity}/{objectId}', [ObjectController::class, 'CustomerOrderEditObject']);
Route::get('/CompletesOrder/{accountId}/{objectId}', [ObjectController::class, 'CompletesOrder']);
Route::get('/CompletesOrder/operationsCalc/', [ObjectController::class, 'operationsCalc']);
Route::get('/CompletesOrder/operations/', [ObjectController::class, 'operations']);
Route::get('/customers/find', [ObjectController::class, 'customers']);


Route::get('/Demand/{accountId}/{entity}/{objectId}', [Demand::class, 'DemandObject']);
Route::get('/postDemand/operations/', [Demand::class, 'operations']);


Route::get('/Salesreturn/{accountId}/{entity}/{objectId}', [Salesreturn::class, 'SalesreturnObject']);
Route::get('/postSalesreturn/operations', [Salesreturn::class, 'sReturn']);


Route::get('/', [indexController::class, 'index'])->name('index');
Route::get('/{accountId}/{isAdmin}', [indexController::class, 'show'])->name("indexMain");


Route::get('/Setting/Main/{accountId}/{isAdmin}', [getController::class, 'mainSetting'])->name('indexSetting');
Route::post('/setSetting/Main/{accountId}/{isAdmin}', [postController::class, 'postSettingIndex'])->name('setSettingIndex');


Route::get('/Setting/Document/{accountId}/{isAdmin}', [getController::class, 'indexDocument'])->name('indexDocument');
Route::post('/setSetting/Document/{accountId}/{isAdmin}', [postController::class, 'postSettingOrder'])->name('setSettingDocument');


Route::get('/Setting/Add/{accountId}/{isAdmin}', [SettingController::class, 'indexAdd'])->name('indexAdd');
Route::post('/setSetting/Add/{accountId}/{isAdmin}', [SettingController::class, 'postSettingAdd'])->name('setSettingAdd');


Route::get('/Setting/Employees/{accountId}/{isAdmin}', [employees::class, 'index']);


Route::get('/Setting/sendOperations/{accountId}/{isAdmin}', [sendOperations::class, 'index']);
Route::post('/Setting/sendOperations/{accountId}/{isAdmin}', [sendOperations::class, 'postOperations']);


Route::get('/Setting/Error/{accountId}/{isAdmin}/{message}', [SettingController::class, 'indexError'])->name('indexError');
Route::get('/Setting/noAdmin/{accountId}/{isAdmin}/', [SettingController::class, 'indexNoAdmin'])->name('indexNoAdmin');


Route::get('/CountProduct/', [ApplicationController::class, 'CountProduct']);





Route::get('DeleteVendorApi/{accountId}', [DeleteVendorApiController::class, 'Delete']);
