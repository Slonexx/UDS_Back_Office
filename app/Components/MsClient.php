<?php

namespace App\Components;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class MsClient{

    private Client $client;

    public function __construct($apiKey) {
        //$this->apiKey = $apiKey;
        $this->client = new Client([
            'headers' => [
                'Authorization' => $apiKey,
                'Content-Type' => 'application/json',
            ]
        ]);
    }

    public function get($url){
        $res = $this->client->get($url,[
            'Accept' => 'application/json',
        ]);
        return json_decode($res->getBody());
    }

    public function post($url, $body){
        $res = $this->client->post($url,[
            'body' => json_encode($body),
        ]);

        return json_decode($res->getBody());
    }

    public function put($url, $body){
        $res = $this->client->put($url,[
            'Accept' => 'application/json',
            'body' => json_encode($body),
         ]);
         return json_decode($res->getBody());
    }

    public function delete($url, $body){
        $res = $this->client->delete($url,[
            'Accept' => 'application/json',
            'body' => json_encode($body),
        ]);
        return json_decode($res->getBody());
    }

/*    public function multiPost($url,$bodyArr)
    {
        try {

            //$responses =
                Http::pool(function (Pool $pool) use ($url, $bodyArr){
                foreach ($bodyArr as $body){
                    $pool->contentType('application/json')
                        ->withToken($this->apiKey)
                        ->post($url,$body);
                }
            });

            //dd($responses);

        } catch (RequestException $e){
            dd($e);
        }

    }*/

}
