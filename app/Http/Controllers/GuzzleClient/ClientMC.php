<?php

namespace App\Http\Controllers\GuzzleClient;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class ClientMC extends Controller
{
    private $apiKey;
    private $uri;

    public function __construct($uri,$apiKey)
    {
        $this->apiKey = $apiKey;
        $this->uri = $uri;
    }

    public function setRequestUrl($uri){
        $this->uri = $uri;
    }

    public function requestGet()
    {

        $headers = [
            'Authorization' => $this->apiKey,
            'Content-Type' => 'application/json',
            'Accept-Encoding' => 'gzip',
        ];
        $client = new Client();

        $res = $client->request('GET', $this->uri ,[
            'headers' => $headers,
        ]);

        return json_decode($res->getBody());
    }

    public function requestPost($body){
        $headers = [
            'Authorization' => $this->apiKey,
            'Content-Type' => 'application/json',
        ];

        $client = new Client([
            'headers' => $headers,
            'http_errors' => false,
        ]);

        $res = $client->post($this->uri,[
            'body' => json_encode($body),
        ]);

        return json_decode($res->getBody());
    }

    public function requestPut($body){
        $headers = [
            'Authorization' => $this->apiKey,
            'Content-Type' => 'application/json',
            'Accept-Encoding' => 'gzip',
        ];

        $client = new Client([
            'headers' => $headers,
        ]);
        $res = $client->put($this->uri,[
            'body' => json_encode($body),
        ]);

        return json_decode($res->getBody());
    }

    public function requestDelete()
    {
        $headers = [
            'Authorization' => $this->apiKey,
        ];

        $client = new Client([
            'headers' => $headers,
        ]);

        $res = $client->delete($this->uri);
        //return json_decode($res->getBody());
    }

}
