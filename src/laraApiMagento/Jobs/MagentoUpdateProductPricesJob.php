<?php

namespace App\Jobs;

use App\MarketPlace;
use App\Traits\MagentoClientTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MagentoUpdateProductPricesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, MagentoClientTrait;

    private $productSKU;
    private $prices;
    private $asset;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($productSKU)
    {
        $this->productSKU = $productSKU;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->loadProperties();

        if ($this->asset !== null) {

            $newPrices = [];

            foreach ($this->prices as $price) {
                array_push($newPrices, [
                    "sku" => $price["sku"],
                    "title" => $price["sku"],
                    "size" => $price["title"],
                    "price" => $price["price"]
                ]);
            }

            $this->asset->update(['marketplace.prices' => $newPrices]);
        }
    }

    private function loadProperties(): void
    {
        $this->asset = MarketPlace::withoutGlobalScopes()->find($this->productSKU);
        $this->prices = $this->getPrices();
    }

    private function getPrices()
    {
        $httpClient = $this->createMagentoClient('/rest/all/V1/products/');
        $response = $httpClient->get($this->productSKU . '/options');

        $productOptions = json_decode($response->getBody(), true);

        foreach ($productOptions as $option) {
            if (array_key_exists("title", $option) && $option["title"] === "Prices") {
                return $option["values"];
            }
        }
    }
}
