<?php

namespace App\Components;

use DateTime;
use DateTimeInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Psr\Http\Message\ResponseInterface;

class UdsClient {

    private Client $client;

    public function __construct($companyId, $apiKey) {
        $this->client = new Client([
            'headers' => [
                'Authorization' => ['Basic '. base64_encode($companyId.':'.$apiKey)],
                "Accept-Charset" => "utf-8",
                'Content-Type' => 'application/json',
            ]
        ]);
    }

    /**
     * @throws GuzzleException
     */
    public function get($url){

        $date = new DateTime();
        $uuid_v4 = Str::uuid();
        $res = $this->client->get($url,[
                "Accept" => "application/json",
                "X-Origin-Request-Id" => $uuid_v4,
                "X-Timestamp" => $date->format(DateTimeInterface::ATOM),
        ]
    );
        return json_decode($res->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function getisErrors($url){
        $date = new DateTime();
        $uuid_v4 = Str::uuid();
        $res = $this->client->get($url,[
                "Accept" => "application/json",
                "X-Origin-Request-Id" => $uuid_v4,
                "X-Timestamp" => $date->format(DateTimeInterface::ATOM),
                'http_errors' => false,
                ]
        );
        return json_decode($res->getStatusCode());
    }

    public function post($url, $body){
        $date = new DateTime();
        $uuid_v4 = Str::uuid();
        $res = $this->client->post($url,[
                "Accept" => "application/json",
                "X-Origin-Request-Id" => $uuid_v4,
                "X-Timestamp" => $date->format(DateTime::ATOM),
            'http_errors' => false,
            'body' => json_encode($body),
        ]);

        return json_decode($res->getBody());
    }

    public function postHttp_errorsNo($url, $body){
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

    public function delete($url){
        $date = new DateTime();
        $uuid_v4 = Str::uuid();
        $res = $this->client->delete($url,[
                "Accept" => "application/json",
                "X-Origin-Request-Id" => $uuid_v4,
                "X-Timestamp" => $date->format(DateTimeInterface::ATOM),
            ]
        );
        return json_decode($res->getBody());
    }


    public function newGET($url): object
    {
        try {
            $res = $this->client->get($url,[
                    "Accept" => "application/json",
                    "X-Origin-Request-Id" => Str::uuid(),
                    "X-Timestamp" => ( new DateTime() )->format(DateTimeInterface::ATOM),
                ]
            );
            return  $this->ResponseHandler($res);
        } catch (BadResponseException $e) {
            return $this->ResponseHandlerField($e);
        }
    }

    public function newPOST($url, $body): object
    {
        try {
            $res = $this->client->post($url,[
                    "Accept" => "application/json",
                    "X-Origin-Request-Id" => Str::uuid(),
                    "X-Timestamp" => ( new DateTime() )->format(DateTimeInterface::ATOM),
                    'json' => $body,
                ]
            );
            return  $this->ResponseHandler($res);
        } catch (BadResponseException $e) {
            return $this->ResponseHandlerField($e);
        }
    }

    public function newPUT($url, mixed $body): object
    {
        try {
            $res = $this->client->put($url,[
                    "Accept" => "application/json",
                    "X-Origin-Request-Id" => Str::uuid(),
                    "X-Timestamp" => ( new DateTime() )->format(DateTimeInterface::ATOM),
                    'json' => $body,
                ]
            );
            return  $this->ResponseHandler($res);
        } catch (BadResponseException $e) {
            return $this->ResponseHandlerField($e);
        }
    }

    public function checkingSetting(): object
    {
        try {
            $res = $this->client->get(Config::get("Global.int_setting"),[
                    "Accept" => "application/json",
                    "X-Origin-Request-Id" => Str::uuid(),
                    "X-Timestamp" => ( new DateTime() )->format(DateTimeInterface::ATOM),
                ]
            );
            return  $this->ResponseHandler($res);
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
