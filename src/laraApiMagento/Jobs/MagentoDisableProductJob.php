<?php

namespace App\Jobs;

use App\Traits\MagentoClientTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MagentoDisableProductJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, MagentoClientTrait;

    private $asset_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($asset_id)
    {
        $this->asset_id = $asset_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->httpClient = $this->createMagentoClient('/rest/all/V1/products/');

        $body =  json_encode([
            'product' => [
                'status' => 2,
            ]
        ]);

        try {
            $this->httpClient->put($this->asset_id, ['body' => $body]);
            return true;
        } catch (ClientException $e) {

            $status_code = $e->getResponse()->getStatusCode();

            if ($status_code == 404) {
                return;
            } else {
                throw $e;
            }
        }
    }
}
