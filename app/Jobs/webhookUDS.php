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
        } catch(ClientException ) {
        }
    }
}
