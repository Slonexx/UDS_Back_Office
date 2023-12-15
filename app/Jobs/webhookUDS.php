<?php

namespace App\Jobs;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class webhookUDS implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public mixed $params;

    public string $url;
    public function __construct($params, $url)
    {
        $this->params = $params;
        $this->url = $url;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $client = new Client([
            'verify' => false,
            'timeout' => 10
        ]);
        try {
            $delay = mt_rand(10, 1000);
            usleep($delay);
            $response = $client->post($this->url, $this->params);
        }  catch(ClientException $e) {
            $msError = "Превышено ограничение на количество запросов в единицу времени";

            $statusCode = $e->getResponse()->getStatusCode();
            $body_encoded = $e->getResponse()->getBody()->getContents();

            $body = json_decode($body_encoded);

            $data = $body->data ?? false;

            $inputMessage = null;
            if($data){
                if(property_exists($data, "errors"))
                    $inputMessage = $data->errors[0]->error;


            }

            if($statusCode == 429 && $inputMessage == $msError){
                $delay = mt_rand(100, 1000);
                usleep($delay);
                webhookUDS::dispatch($this->params, $this->url)->onConnection('database')->onQueue("low");
            }

        }
    }
}
