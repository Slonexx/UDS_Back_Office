<?php

namespace App\Http\Controllers\Web;

use App\Components\MsClient;
use App\Http\Controllers\BD\newProductSettingBD;
use App\Http\Controllers\Config\Lib\VendorApiController;
use App\Http\Controllers\Controller;
use App\Services\newProductService\createProductForUDS;
use App\Services\newProductService\updateProductForUDS;
use App\Services\Settings\SettingsService;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class indexController extends Controller
{

    public function index(Request $request): View|Factory|Application|RedirectResponse
    {
        $contextKey = $request->contextKey;
        if ($contextKey == null) {
            return view("main.dump");
        }

        $vendorAPI = new VendorApiController();
        $employee = $vendorAPI->context($contextKey);
        $accountId = $employee->accountId;
        $isAdmin = $employee->permissions->admin->view;

        return redirect()->route('indexMain', [
            'accountId' => $accountId,
            'isAdmin' => $isAdmin,
        ]);

    }

    public function show($accountId, $isAdmin): Factory|View|Application
    {
        return view("web.index", [
            'accountId' => $accountId,
            'isAdmin' => $isAdmin,
        ]);
    }

    public function counterparty(Request $request): Factory|View|Application
    {
        $contextKey = $request->contextKey;
        $vendorAPI = new VendorApiController();
        $employee = $vendorAPI->context($contextKey);
        $accountId = $employee->accountId;

        $isAdmin = $employee->permissions->admin->view;
        //$isAdmin = "ALL";

        if ($isAdmin == "NO") {
            return view('widgets.counterparty', [
                'accountId' => $accountId,

                // 'accountId' => "1dd5bd55-d141-11ec-0a80-055600047495",
                'admin' => "NO",
            ]);
        }

        return view('widgets.counterparty', [
            'accountId' => $accountId,

            //'accountId' => "1dd5bd55-d141-11ec-0a80-055600047495",
            'admin' => "ALL",
        ]);
    }

    public function product(Request $request): Factory|View|Application
    {
        $contextKey = $request->contextKey;
        $vendorAPI = new VendorApiController();
        $employee = $vendorAPI->context($contextKey);
        $accountId = $employee->accountId;
        $isAdmin = $employee->permissions->admin->view;
        //$accountId = "1dd5bd55-d141-11ec-0a80-055600047495";
        //$isAdmin = "ALL";

        if ($isAdmin == "NO") {
            return view('widgets.counterparty', [
                'accountId' => $accountId,
                'admin' => "NO",
            ]);
        }

        return view('widgets.product', [
            'accountId' => $accountId,
        ]);
    }


    public function ObjectEdit(Request $request): Factory|View|Application
    {
        try {
            $contextKey = $request->contextKey;
            try {
                $vendorAPI = new VendorApiController();
                $employee = $vendorAPI->context($contextKey);
                $accountId = $employee->accountId;
            } catch (BadResponseException) {
                return view('widget.Error', [
                    'status' => false,
                    'code' => 400,
                    'message' => "Ошибка получения контекста приложения! Обновите страницу (F5)",
                ]);
            }

            return view('widget.object', [
                'accountId' => $accountId,
                'cashier_id' => $employee->id,
                'cashier_name' => $employee->name,

                //'accountId' => "1dd5bd55-d141-11ec-0a80-055600047495",
                //'cashier_id' => "5f3023e9-05b3-11ee-0a80-06f20001197a",
                //'cashier_name' => "Сергей",
            ]);

        } catch (BadResponseException $e) {

            $error = json_decode($e->getResponse()->getBody()->getContents());
            if (property_exists($error, 'errors')) {
                foreach ($error->errors as $item) {
                    $message[] = $item->error;
                }
            } else {
                $message[] = $error;
            }

            return view('widget.Error', [
                'status' => false,
                'code' => 400,
                'message' => $message ?? $e->getMessage(),
            ]);
        }
    }

    public function CustomerOrderEdit(Request $request): View|Factory|Application
    {
        return $this->ObjectEdit($request);
    }

    public function DemandEdit(Request $request): View|Factory|Application
    {
        return $this->ObjectEdit($request);
    }

    public function SalesreturnEdit(Request $request): View|Factory|Application
    {
        try {
            $contextKey = $request->contextKey;
            try {
                $vendorAPI = new VendorApiController();
                $employee = $vendorAPI->context($contextKey);
                $accountId = $employee->accountId;
            } catch (BadResponseException) {
                return view('widget.Error', [
                    'status' => false,
                    'code' => 400,
                    'message' => "Ошибка получения контекста приложения! Обновите страницу (F5)",
                ]);
            }
            return view('widgets.Salesreturn', [
                'accountId' => $accountId,
                'cashier_id' => $employee->id,
                'cashier_name' => $employee->name,

               /*  'accountId' => "1dd5bd55-d141-11ec-0a80-055600047495",
                 'cashier_id' => "5f3023e9-05b3-11ee-0a80-06f20001197a",
                 'cashier_name' => "Сергей",*/
            ]);
        } catch (BadResponseException $e) {

            $error = json_decode($e->getResponse()->getBody()->getContents());
            if (property_exists($error, 'errors')) {
                foreach ($error->errors as $item) {
                    $message[] = $item->error;
                }
            } else {
                $message[] = $error;
            }

            return view('widget.Error', [
                'status' => false,
                'code' => 400,
                'message' => $message ?? $e->getMessage(),
            ]);
        }

        return $this->ObjectEdit($request);
    }

    public function searchEmployeeByID($login)
    {
        $allSettings = app(SettingsService::class)->getSettings();

        foreach ($allSettings as $setting) {

            try {
                $ClientCheckMC = new MsClient($setting->TokenMoySklad);
                $body = $ClientCheckMC->get('https://api.moysklad.ru/api/remap/1.2/entity/employee?filter=uid~' . $login)->rows;

                if ($body != []) {
                    dd($body);
                }

            } catch (BadResponseException) {
                continue;
            }

        }
    }


    function time(Request $request, $accountId)
    {

        $data = $request->all();

        set_time_limit(30000);
        /*$setting = new getSettingVendorController($accountId);
        $ms = new MsClient($setting->TokenMoySklad);
        $counterparty = $ms->get('https://api.moysklad.ru/api/remap/1.2/entity/counterparty')->rows;

        foreach ($counterparty as $item) {
            try {
                $ms->delete('https://api.moysklad.ru/api/remap/1.2/entity/counterparty/' . $item->id, null);
            } catch (BadResponseException) {
                continue;
            }
        }*/

        $item = new newProductSettingBD($accountId);
        /*$mainSetting = new getMainSettingBD($item->accountId);

        $ClientCheckMC = new MsClient($mainSetting->tokenMs);
        $ClientCheckUDS = new UdsClient($mainSetting->companyId, $mainSetting->TokenUDS);*/

        $data = [
            'accountId' => $item->accountId,
            'salesPrices' => $item->salesPrices,
            'promotionalPrice' => $item->promotionalPrice,
            'Store' => $item->Store,
            'StoreRecord' => $item->StoreRecord,
            'productHidden' => $item->productHidden,
            'countRound' => $item->countRound,
        ];
        if ($data['countRound'] < 10) {
            /*$record = newProductModel::where('accountId', $item->accountId)->first();
            $record->countRound = $item->countRound + 1;
            $record->save();*/

            $create = new updateProductForUDS($data);
            $create->initialization();
        }


    }


}
