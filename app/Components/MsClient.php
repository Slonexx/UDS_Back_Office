<?php

namespace App\Components;
use GuzzleHttp\Client;

class MsClient{

    private Client $client;

    public function __construct($apiKey) {
        $this->apiKey = $apiKey;
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
        ]
    );
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

}