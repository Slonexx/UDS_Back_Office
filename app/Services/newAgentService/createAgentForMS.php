<?php

namespace App\Services\newAgentService;

use App\Components\MsClient;
use App\Components\UdsClient;
use App\Models\newAgentModel;
use GuzzleHttp\Exception\BadResponseException;

class createAgentForMS
{

    private mixed $setting;
    private MsClient $msClient;
    private UdsClient $udsClient;


    public function __construct($data, $ms, $uds)
    {
        $this->setting = json_decode(json_encode($data));
        $this->msClient = $ms;
        $this->udsClient = $uds;


    }

    public function initialization(): void
    {
        $offset = $this->setting->offset;


        while ($this->haveRowsInResponse($url,$offset)){
            $customersFromUds = $this->udsClient->get($url);
            foreach ($customersFromUds->rows as $customerFromUds){
                $currId = $customerFromUds->participant->id;
                if ($customerFromUds->phone != null) {
                    $phone = $this->filterPhone($customerFromUds->phone);
                } else {
                    $phone = "";
                }
                $displayName = $customerFromUds->displayName;

                if (!$this->isAgentExistsMs($currId,$phone, $displayName)){
                    try {
                        $this->createAgent($customerFromUds, $phone);
                    }catch (BadResponseException){

                    }

                }
            }
            $offset += 50;
            newAgentModel::where('accountId', $this->setting->accountId)->update(['offset' => $offset]);
        }

    }


    public function createAgent($customer, $phone): void
    {

        if ($this->setting->examination == '0'){
            if ($phone == "") { } else $urlToFind = "https://online.moysklad.ru/api/remap/1.2/entity/counterparty?filter=phone~".$phone;
        } elseif ($this->setting->examination == '1') { $urlToFind = "https://online.moysklad.ru/api/remap/1.2/entity/counterparty?filter=name=".$customer->displayName; }

        elseif ($this->setting->examination == '2') {
            if ($phone == "") {
                $urlToFind = "https://online.moysklad.ru/api/remap/1.2/entity/counterparty?filter=name=".$customer->displayName;
            } else
                $urlToFind = "https://online.moysklad.ru/api/remap/1.2/entity/counterparty?filter=phone~".$phone.";name=".$customer->displayName;
        }

        else {
            if ($phone == "") { } else $urlToFind = "https://online.moysklad.ru/api/remap/1.2/entity/counterparty?filter=phone~".$phone;
        }

        $json = $this->msClient->get($urlToFind);


        $body = [
            "name" => $customer->displayName,
            "companyType"=> "individual",
            "externalCode" => (string) $customer->participant->id,
        ];

        if ($customer->email != null and $this->setting->email == '1'){
            $body["email"] = $customer->email;
        }

        if ($customer->gender != null and $this->setting->gender == '1'){
            $body["sex"] = $customer->gender;
        }

        if ($customer->birthDate != null and $this->setting->birthDate == '1'){

            $dateString = $customer->birthDate;
            $dateTime = strtotime($dateString);
            $newDateTime = date("Y-m-d", strtotime("-1 day", $dateTime)) . " 21:00:00.000";

            $body["birthDate"] = $newDateTime;

        }

        if ($customer->phone != null){
            $body["phone"] = $phone;
        }

        if ($json->meta->size == 0){
            $this->msClient->post("https://online.moysklad.ru/api/remap/1.2/entity/counterparty",$body);
        } else {
            unset($body['name']);
            unset($body['companyType']);
            $this->msClient->put("https://online.moysklad.ru/api/remap/1.2/entity/counterparty/".$json->rows[0]->id,$body);
        }


    }


    private function haveRowsInResponse(&$url,$offset,$nodeId=0): bool
    {
        $url = "https://api.uds.app/partner/v2/customers?max=50&offset=".$offset;
        if ($nodeId > 0){
            $url = $url."&nodeId=".$nodeId;
        }
        $json =  $this->udsClient->get($url);

        return count($json->rows) > 0;
    }

    private function isAgentExistsMs($nodeId, $phone, $displayName): bool
    {
        try {
            $json = $this->msClient->get("https://online.moysklad.ru/api/remap/1.2/entity/counterparty?filter=externalCode=".$nodeId);
        } catch (BadResponseException){ return false; }

        if ($json->meta->size == 0) { return false; }


        if ($this->setting->examination == '0'){
            if ($phone == "") {
                return false;
            } else
            $urlToFind = "https://online.moysklad.ru/api/remap/1.2/entity/counterparty?filter=phone~".$phone;

        } elseif ($this->setting->examination == '1') {
            $urlToFind = "https://online.moysklad.ru/api/remap/1.2/entity/counterparty?filter=name=".$displayName;
        }

        elseif ($this->setting->examination == '2') {
            if ($phone == "") {
                $urlToFind = "https://online.moysklad.ru/api/remap/1.2/entity/counterparty?filter=name=".$displayName;
            } else
            $urlToFind = "https://online.moysklad.ru/api/remap/1.2/entity/counterparty?filter=phone~".$phone.";name=".$displayName;
        }

        else {
            if ($phone == "") {
                return false;
            } else
            $urlToFind = "https://online.moysklad.ru/api/remap/1.2/entity/counterparty?filter=phone~".$phone;
        }

        $json = $this->msClient->get($urlToFind);

        if ($json->meta->size > 0){
          return true;
        } else return false;

    }

    private function filterPhone($phone): array|string|null
    {
        return preg_replace('/^\+7/', '', $phone);
    }

}
