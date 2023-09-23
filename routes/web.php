<?php

use App\Http\Controllers\BackEnd\ObjectController;
use App\Http\Controllers\BackEnd\Salesreturn;
use App\Http\Controllers\Web\ADD\ApplicationController;
use App\Http\Controllers\Web\employees;
use App\Http\Controllers\Web\GET\getAutomationController;
use App\Http\Controllers\Web\GET\getController;
use App\Http\Controllers\Web\POST\postAutomationController;
use App\Http\Controllers\Web\POST\postController;
use App\Http\Controllers\Web\sendOperations;
use App\Http\Controllers\Web\Setting\agentController;
use App\Http\Controllers\Web\Setting\productController;
use App\Http\Controllers\Web\SettingController;
use App\Http\Controllers\Web\RewardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\indexController;
use App\Http\Controllers\Config\DeleteVendorApiController;


//TEST
Route::get('time/{accountId}', [indexController::class, 'time']);
Route::get('web/CheckSave/web/{accountId}', [indexController::class, 'CheckSave'])->name('CheckSave');

//Search
Route::get('/search/employee/byName/{login}', [indexController::class, 'searchEmployeeByID']);
Route::get('/Check/Main/{accountId}/{isAdmin}', [getController::class, 'CheckSetting']);



//Widget
Route::get('/Counterparty', [indexController::class, 'counterparty']);

Route::get('/CustomerOrderEdit', [indexController::class, 'CustomerOrderEdit']);
Route::get('/DemandEdit', [indexController::class, 'DemandEdit']);
Route::get('/SalesreturnEdit', [indexController::class, 'SalesreturnEdit']);

Route::get('/Product', [indexController::class, 'product']);
Route::get('/Product/Info', [\App\Http\Controllers\Web\ADD\ProductController::class, 'infoProduct']);


Route::get('/CounterpartyObject/{accountId}/{objectId}', [ObjectController::class, 'CounterpartyObject']);
Route::get('/Accrue/{accountId}/{points}/{participants}', [RewardController::class, 'Accrue']);
Route::get('/Cancellation/{accountId}/{points}/{participants}', [RewardController::class, 'Cancellation']);


Route::get('/CustomerOrder/EditObject/{accountId}/{entity}/{objectId}', [ObjectController::class, 'CustomerOrderEditObject']);
Route::get('/CompletesOrder/{accountId}/{objectId}', [ObjectController::class, 'CompletesOrder']);
Route::get('/Completes/Order/operationsCalc/', [ObjectController::class, 'operationsCalc']);
Route::get('/Completes/Order/operations/', [ObjectController::class, 'operations']);
Route::get('/customers/find', [ObjectController::class, 'customers']);

Route::get('/Salesreturn/{accountId}/{entity}/{objectId}', [Salesreturn::class, 'SalesreturnObject']);
Route::get('/postSalesreturn/operations', [Salesreturn::class, 'sReturn']);



//Index
Route::get('/', [indexController::class, 'index'])->name('index');
Route::get('/{accountId}/{isAdmin}', [indexController::class, 'show'])->name("indexMain");


//Setting
Route::get('/Setting/Main/{accountId}/{isAdmin}', [getController::class, 'mainSetting'])->name('indexSetting');
Route::post('/setSetting/Main/{accountId}/{isAdmin}', [postController::class, 'postSettingIndex'])->name('setSettingIndex');

Route::get('/Setting/createProduct/{accountId}/{isAdmin}', [productController::class, 'indexProduct'])->name('productIndex');
Route::post('/Setting/createProduct/{accountId}/{isAdmin}', [productController::class, 'postProduct'])->name('setProductIndex');

Route::get('/Setting/createAgent/{accountId}/{isAdmin}', [agentController::class, 'getAgent'])->name('agent');
Route::post('/Setting/createAgent/{accountId}/{isAdmin}', [agentController::class, 'postAgent'])->name('setAgent');

Route::get('/Setting/Document/{accountId}/{isAdmin}', [getController::class, 'indexDocument'])->name('indexDocument');
Route::post('/setSetting/Document/{accountId}/{isAdmin}', [postController::class, 'postSettingOrder'])->name('setSettingDocument');

Route::get('/Setting/Add/{accountId}/{isAdmin}', [SettingController::class, 'indexAdd'])->name('indexAdd');
Route::post('/setSetting/Add/{accountId}/{isAdmin}', [SettingController::class, 'postSettingAdd'])->name('setSettingAdd');

Route::get('/Setting/Automation/{accountId}/{isAdmin}', [getAutomationController::class, 'getAutomation'])->name('getAutomation');
Route::post('/setSetting/Automation/{accountId}/{isAdmin}', [postAutomationController::class, 'postSettingAdd'])->name('postAutomation');

Route::get('/Setting/sendOperations/{accountId}/{isAdmin}', [sendOperations::class, 'index']);
Route::post('/Setting/sendOperations/{accountId}/{isAdmin}', [sendOperations::class, 'postOperations']);

Route::get('/Setting/Employees/{accountId}/{isAdmin}', [employees::class, 'index']);

Route::get('/Setting/Error/{accountId}/{isAdmin}/{message}', [SettingController::class, 'indexError'])->name('indexError');
Route::get('/Setting/noAdmin/{accountId}/{isAdmin}/', [SettingController::class, 'indexNoAdmin'])->name('indexNoAdmin');


//OLD Count product for product index
Route::post('/CountProduct/', [ApplicationController::class, 'CountProduct']);

//Delete web dev base
Route::get('Web/DeleteVendorApi/{accountId}', [DeleteVendorApiController::class, 'Delete']);
