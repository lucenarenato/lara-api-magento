<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\MarketPlace;
use App\Traits\MagentoClientTrait;
use GuzzleHttp\Exception\ClientException;

class MagentoUpdateProductJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, MagentoClientTrait;

    private $asset_id;
    private $asset;
    private $httpClient;

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
        $this->loadProperties();

        if ($this->isProductRegisterOnMagento()) {
            $this->update();
        } else {
            $this->insert();
        }
    }

    private function loadProperties(): void
    {
        $this->asset = MarketPlace::withoutGlobalScopes()->find($this->asset_id);
        $this->httpClient = $this->createMagentoClient('/rest/all/V1/products/');
    }

    private function isProductRegisterOnMagento(): bool
    {
        try {
            $this->httpClient->get($this->asset->_id);
            return true;
        } catch (ClientException $e) {

            $status_code = $e->getResponse()->getStatusCode();

            if ($status_code == 404) {
                return false;
            } else {
                throw $e;
            }
        }
    }

    private function update(): void
    {
        $body =  json_encode([
            'product' => [
                'name' => $this->asset->name,
                'custom_attributes' => $this->getCustomAttributes()
            ]
        ]);

        $this->httpClient->put($this->asset->_id, ['body' => $body]);
    }

    private function insert(): void
    {
        $body =  json_encode([
            'product' => [
                'sku' => $this->asset->_id,
                'name' => $this->asset->name,
                'price' => 0,
                'type_id' => 'downloadable',
                'attribute_set_id' => 4,
                'custom_attributes' => $this->getCustomAttributes(),
                'options' => $this->getCustomizedProductOptions()
            ],
        ]);

        $this->httpClient->post("", ['body' => $body]);
    }

    private function getCustomAttributes(): array
    {
        return [
            [
                "attribute_code" => "url_key",
                "value" => $this->asset->_id
            ],
            [
                'attribute_code' => 'description',
                'value' => '<p>' . $this->asset->small_description ?? '' . '</p>'
            ]
        ];
    }

    private function getCustomizedProductOptions(): array
    {
        return [
            [
                'product_sku' => $this->asset->_id,
                'title' => 'Prices',
                'type' => 'radio',
                'sort_order' => 1,
                'is_require' => true,
                'max_characters' => 0,
                'values' => $this->getPricingOptions(),
                'extensionAttributes' => null
            ]
        ];
    }

    private function getPricingOptions(): array
    {
        $pricingOptions = [];
        $order = 1;
        foreach ($this->asset->marketplace['prices'] as $price) {
            array_push($pricingOptions, [
                'title' => $price['size'],
                'sort_order' => $order++,
                'price' => $price['price'],
                'price_type' => 'fixed',
                'sku' => $price['sku']
            ]);
        }

        return $pricingOptions;
    }
}
