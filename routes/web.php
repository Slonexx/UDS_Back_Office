<?php

use App\Http\Controllers\BackEnd\ObjectController;
use App\Http\Controllers\Web\SettingController;
use App\Http\Controllers\Web\SupportController;
use App\Http\Controllers\Web\RewardController;
use App\Http\Controllers\Web\WhatsappController;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\Web\indexController;
use \App\Http\Controllers\Config\DeleteVendorApiController;


Route::post('/CheckSave/{accountId}', [indexController::class, 'CheckSave'])->name('CheckSave');


Route::get('/Counterparty', [indexController::class, 'counterparty']);
Route::get('/CustomerOrderEdit', [indexController::class, 'CustomerOrderEdit']);


Route::get('/CounterpartyObject/{accountId}/{entity}/{objectId}', [ObjectController::class, 'CounterpartyObject']);
Route::get('/Accrue/{accountId}/{points}/{participants}', [RewardController::class, 'Accrue']);
Route::get('/Cancellation/{accountId}/{points}/{participants}', [RewardController::class, 'Cancellation']);


Route::get('/CustomerOrderEditObject/{accountId}/{entity}/{objectId}', [ObjectController::class, 'CustomerOrderEditObject']);
Route::get('/CompletesOrder/{accountId}/{objectId}', [ObjectController::class, 'CompletesOrder']);


Route::get('/', [indexController::class, 'index'])->name('index');
Route::get('/{accountId}/{isAdmin}', [indexController::class, 'show'])->name("indexMain");


Route::get('/Setting/{accountId}/{isAdmin}', [SettingController::class, 'index'])->name('indexSetting');
Route::get('/Setting/Document/{accountId}/{isAdmin}', [SettingController::class, 'indexDocument'])->name('indexDocument');
Route::get('/Setting/Add/{accountId}/{isAdmin}', [SettingController::class, 'indexAdd'])->name('indexAdd');

Route::get('/Setting/Error/{accountId}/{isAdmin}/{message}', [SettingController::class, 'indexError'])->name('indexError');
Route::get('/Setting/noAdmin/{accountId}/{isAdmin}/', [SettingController::class, 'indexNoAdmin'])->name('indexNoAdmin');

Route::post('/setSetting/{accountId}/{isAdmin}', [SettingController::class, 'postSettingIndex'])->name('setSettingIndex');
Route::post('/setSetting/Document/{accountId}/{isAdmin}', [SettingController::class, 'postSettingDocument'])->name('setSettingDocument');
Route::post('/setSetting/Add/{accountId}/{isAdmin}', [SettingController::class, 'postSettingAdd'])->name('setSettingAdd');


//Route::get('/Help/Support/{accountId}', [SupportController::class, 'index'])->name('indexSupport');
//Route::get('/Help/Support/Whatsapp/{accountId}', [WhatsappController::class, 'index'])->name('indexWhatsapp');

//Route::post('/Help/Support/Send/{accountId}', [SupportController::class, 'postSendSupport'])->name('indexSendSupport');
//Route::post('/Help/Support/WhatsappSend/{accountId}', [WhatsappController::class, 'postWhatsappSend'])->name('indexSendWhatsapp');







Route::get('DeleteVendorApi/{appId}/{accountId}', [DeleteVendorApiController::class, 'Delete'])->name('Delete');
