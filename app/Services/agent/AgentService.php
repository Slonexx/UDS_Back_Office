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
         //= "https://api.uds.app/partner/v2/customers";
        $client = new UdsClient($companyId,$apiKeyUds);
        return $client->get($url);
    }

    private function notAddedInMs($apiKeyMs,$apiKeyUds, $companyId, $accountId)
    {

       // $customersFromMs = $this->getMs($apiKeyMs);

        $count = 0;
        $offset = 0;

        $check = DB::table('agent_503s')
            ->where('accountId',$accountId)->first();

        if (!is_null($check)){
            $offset = $check->offset;
        }

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
                            $bd = new BDController();
                            $bd->throwToRetryAgent($accountId,$url, $offset);
                            $bd->errorLog($accountId,$e->getMessage());
                        }

                    }
                }
                $offset += 50;
            }
        } catch (\Throwable $exception){
            $bd = new BDController();
            $bd->errorLog($accountId,$exception->getMessage());
            $bd->throwToRetryAgent($accountId,$url, $offset);
        }




        return [
            "message" => "Inserted customers: ".$count,
        ];
    }

    private function createAgent($apiKeyMs,$customer)
    {

        $agent = [
            "name" => $customer->displayName,
            "companyType" => "individual",
            "externalCode" => "".$customer->participant->id,
        ];

        if ($customer->email != null){
            $agent["email"] = $customer->email;
        }

        if ($customer->phone != null){
            /*$cellPhone = "  ".$customer->phone;
            $toPhone = sprintf("%s %s %s",
                substr($cellPhone, 2, 3),
                substr($cellPhone, 5, 3),
                substr($cellPhone, 8));*/
            $agent["phone"] = $customer->phone;
        }

        $url = "https://online.moysklad.ru/api/remap/1.2/entity/counterparty";
        $client = new MsClient($apiKeyMs);
        $client->post($url,$agent);
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
