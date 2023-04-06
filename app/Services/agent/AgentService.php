<?php

namespace App\Services\agent;

use App\Components\MsClient;
use App\Components\UdsClient;
use App\Http\Controllers\BackEnd\BDController;
use App\Models\Agent_503;
use App\Models\errorLog;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\DB;

class AgentService
{
    public function insertToMs($data)
    {
        return $this->notAddedInMs(
            $data['tokenMs'],
            $data['apiKeyUds'],
            $data['companyId'],
            $data['accountId']
        );
    }

    private function getMs($apiKeyMs): array
    {
        $url = "https://online.moysklad.ru/api/remap/1.2/entity/counterparty";
        $client = new MsClient($apiKeyMs);
        $json = $client->get($url);
        $customerIds = [];
        foreach ($json->rows as $row){
            array_push($customerIds,$row->externalCode);
        }
        return $customerIds;
    }

    private function getUds($url,$companyId, $apiKeyUds)
    {
        $client = new UdsClient($companyId,$apiKeyUds);
        return $client->get($url);
    }

    private function notAddedInMs($apiKeyMs,$apiKeyUds, $companyId, $accountId)
    {

        $count = 0;
        $offset = 0;

        $bd = new BDController();

        $check = DB::table('agent_503s')->where('accountId',$accountId)->first();

        if (!is_null($check)){ $offset = $check->offset; }

        set_time_limit(3600);

        try {
            while ($this->haveRowsInResponse($url,$offset,$companyId,$apiKeyUds)){
                $customersFromUds = $this->getUds($url,$companyId,$apiKeyUds);
                foreach ($customersFromUds->rows as $customerFromUds){
                    //dd($customerFromUds);
                    $currId = $customerFromUds->participant->id;
                    if (!$this->isAgentExistsMs($currId,$customerFromUds->phone,$apiKeyMs)){
                        try {
                            $this->createAgent($apiKeyMs,$customerFromUds);
                            $count++;
                        }catch (ClientException $e){
                            $bd->throwToRetryAgent($accountId,$url, $offset);
                            $bd->errorLog($accountId,$e->getMessage());
                        }

                    }
                }
                $offset += 50;
                $bd->throwToRetryAgent($accountId,$url, $offset);
            }
        } catch (\Throwable $exception){
            //$bd = new BDController();
           // $bd->errorLog($accountId,$exception->getMessage());
        }




        return [
            "message" => "Inserted customers: ".$count,
        ];
    }

    public function createAgent($apiKeyMs,$customer)
    {
        $client = new MsClient($apiKeyMs);

        if ($customer->phone != null){
            $BodyAgentMS = $client->get("https://online.moysklad.ru/api/remap/1.2/entity/counterparty?search=".$customer->phone)->rows;
        } else $BodyAgentMS = [];
        if ($BodyAgentMS != []){
            $agent = [
                "name" => $customer->displayName,
                "externalCode" => (string) $customer->participant->id,
            ];

            if ($customer->email != null){
                $agent["email"] = $customer->email;
            }

            if ($customer->phone != null){
                $agent["phone"] = $customer->phone;
            }
            $client->post("https://online.moysklad.ru/api/remap/1.2/entity/counterparty",$agent);
        } else {
            $agent = [
                "name" => $customer->displayName,
                "externalCode" => (string) $customer->participant->id,
            ];

            if ($customer->email != null){
                $agent["email"] = $customer->email;
            }

            if ($customer->phone != null){
                $agent["phone"] = $customer->phone;
            }
            $client->put("https://online.moysklad.ru/api/remap/1.2/entity/counterparty/".$BodyAgentMS[0]->id, $agent);
        }



    }

    private function haveRowsInResponse(&$url,$offset,$companyId,$apiKeyUds,$nodeId=0): bool
    {
        $url = "https://api.uds.app/partner/v2/customers?max=50&offset=".$offset;
        if ($nodeId > 0){
            $url = $url."&nodeId=".$nodeId;
        }
        $client = new UdsClient($companyId,$apiKeyUds);
        $json = $client->get($url);
        return count($json->rows) > 0;
    }

    private function isAgentExistsMs($nodeId,$phone,$apiKeyMs): bool
    {
        $urlToFind = "https://online.moysklad.ru/api/remap/1.2/entity/counterparty?filter=externalCode=".$nodeId;
        //dd($urlToFind);
        $client = new MsClient($apiKeyMs);
        $json = $client->get($urlToFind);

        if ($json->meta->size == 0){
            if ($phone != null){
                $urlCheckPhone = "https://online.moysklad.ru/api/remap/1.2/entity/counterparty?filter=phone=".$phone;
                $json = $client->get($urlCheckPhone);
                if ($json->meta->size > 0){
                    $this->updateAgent($json->rows[0],$nodeId,$apiKeyMs);
                    return true;
                } else {
                    return false;
                }
            }
            return false;
        } else return true;
    }

    private function updateAgent($agent,$newNodeId, $apiKeyMs)
    {
        $url = "https://online.moysklad.ru/api/remap/1.2/entity/counterparty/".$agent->id;
        $client = new MsClient($apiKeyMs);
        $body = [
            "externalCode" => $newNodeId
        ];
        $client->put($url,$body);
    }

}
