<?php
$nameIntegration = "UDS - полноценная интеграция";
$nameFolder = "Интеграция UDS";
$MsURL = "https://api.moysklad.ru/api/remap/1.2/";
$int = "https://api.uds.app/partner/v2/";
//$restartCommand = "c:\OSPanel\Open Server Panel.exe" /restart;
return [
    /**
     * @_moysklad_url
     */
    "agent" => "{$MsURL}entity/counterparty/",

    "productAtt" => "{$MsURL}product/metadata/attributes/",


    /**
     * @int_url
     */
    "goods" => "{$int}goods/",
    "int_setting" => "{$int}settings/",



    /**
     * @legacy
     */
    'url' => env('APP_URL'),
    'url_' => env('APP_URL_'),
    'appId' => env('APP_ID'),
    'appUid' => env('APP_UID'),
    'secretKey' => env('SECRET_KEY'),




    'entity' =>  'https://api.moysklad.ru/api/remap/1.2/entity/',
    'VendorEndpoint' =>  'https://apps-api.moysklad.ru/api/vendor/1.0',
    'JsonEndpoint' =>  'https://api.moysklad.ru/api/remap/1.2',
];
