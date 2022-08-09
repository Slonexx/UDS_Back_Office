<?php

use App\Http\Controllers\Web\SettingController;
use App\Http\Controllers\Web\SupportController;
use App\Http\Controllers\Web\WhatsappController;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\Web\indexController;
use \App\Http\Controllers\Config\DeleteVendorApiController;


Route::post('/CheckSave/{accountId}', [indexController::class, 'CheckSave'])->name('CheckSave');
Route::get('/Counterparty', [indexController::class, 'counterparty'])->name('Counterparty');

Route::get('/CounterpartyObject', [indexController::class, 'CounterpartyObject'])->name('CounterpartyObject');


Route::get('/', [indexController::class, 'index'])->name('index');
Route::get('/{accountId}', [indexController::class, 'show'])->name("indexMain");


Route::get('/Setting/{accountId}', [SettingController::class, 'index'])->name('indexSetting');
Route::get('/Setting/Document/{accountId}', [SettingController::class, 'indexDocument'])->name('indexDocument');
Route::get('/Setting/Add/{accountId}', [SettingController::class, 'indexAdd'])->name('indexAdd');

Route::get('/Setting/Error/{accountId}/{message}', [SettingController::class, 'indexError'])->name('indexError');

Route::post('/setSetting/{accountId}', [SettingController::class, 'postSettingIndex'])->name('setSettingIndex');
Route::post('/setSetting/Document/{accountId}', [SettingController::class, 'postSettingDocument'])->name('setSettingDocument');
Route::post('/setSetting/Add/{accountId}', [SettingController::class, 'postSettingAdd'])->name('setSettingAdd');


Route::get('/Help/Support/{accountId}', [SupportController::class, 'index'])->name('indexSupport');
Route::get('/Help/Support/Whatsapp/{accountId}', [WhatsappController::class, 'index'])->name('indexWhatsapp');

Route::post('/Help/Support/Send/{accountId}', [SupportController::class, 'postSendSupport'])->name('indexSendSupport');
Route::post('/Help/Support/WhatsappSend/{accountId}', [WhatsappController::class, 'postWhatsappSend'])->name('indexSendWhatsapp');







Route::get('DeleteVendorApi/{appId}/{accountId}', [DeleteVendorApiController::class, 'Delete'])->name('Delete');
