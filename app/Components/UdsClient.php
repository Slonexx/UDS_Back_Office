<?php

namespace App\Components;

use DateTime;
use GuzzleHttp\Client;
use Illuminate\Support\Str;

class UdsClient {
    
    private Client $client;

    public function __construct($companyId, $apiKey) {
        $credentials = base64_encode($companyId.':'.$apiKey);
        $this->client = new Client([
            'headers' => [
                'Authorization' => ['Basic '.$credentials],
                "Accept-Charset" => "utf-8",
                'Content-Type' => 'application/json',
            ]
        ]);
    }

    public function get($url){
        $date = new DateTime();
        $uuid_v4 = Str::uuid();
        $res = $this->client->get($url,[
                "Accept" => "application/json",
                "X-Origin-Request-Id" => $uuid_v4,
                "X-Timestamp" => $date->format(DateTime::ATOM),
        ]
    );
        return json_decode($res->getBody());
    }

    public function post($url, $body){
        $date = new DateTime();
        $uuid_v4 = Str::uuid();
        $res = $this->client->post($url,[
                "Accept" => "application/json",
                "X-Origin-Request-Id" => $uuid_v4,
                "X-Timestamp" => $date->format(DateTime::ATOM),
            'body' => json_encode($body),
        ]);
        
        return json_decode($res->getBody());
    }

    public function put($url, $body){
        $date = new DateTime();
        $uuid_v4 = Str::uuid();
        $res = $this->client->put($url,[
            "Accept" => "application/json",
            "X-Origin-Request-Id" => $uuid_v4,
            "X-Timestamp" => $date->format(DateTime::ATOM),
            'body' => json_encode($body),
         ]);
         return json_decode($res->getBody());
    }

}