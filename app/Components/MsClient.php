<?php

namespace App\Components;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

class MsClient{

    private Client $client;

    public function __construct($apiKey) {
        $this->client = new Client([
            'headers' => [
                'Authorization' => $apiKey,
                'Content-Type' => 'application/json',
                'Accept-Encoding' => 'gzip',
            ]
        ]);
    }

    public function get($url){
        $res = $this->client->get($url,[
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
            'body' => json_encode($body),
         ]);
         return json_decode($res->getBody());
    }

    public function delete($url, $body){
        $res = $this->client->delete($url,[
            'body' => json_encode($body),
        ]);
        return json_decode($res->getBody());
    }


    public function newGet($url): object
    {
        try {
            return  $this->ResponseHandler($this->client->get($url));
        } catch (BadResponseException $e) {
            return $this->ResponseHandlerField($e);
        }
    }

    public function newPUT($url, $body){
        try {
            return  $this->ResponseHandler($this->client->put($url, ['json'=> $body]));
        } catch (BadResponseException $e) {
            return $this->ResponseHandlerField($e);
        }
    }

    private function ResponseHandler(ResponseInterface $post): object
    {
        return (object) [
            'status' => true,
            'body' => $post->getBody(),
            'data' => json_decode($post->getBody()->getContents()),
        ];
    }
    private function ResponseHandlerField(BadResponseException|\Exception $e): object
    {
        return (object) [
            'status' => false,
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'body' => $e->getResponse()->getBody(),
            'data' => json_decode($e->getResponse()->getBody()->getContents()),
        ];
    }
}
