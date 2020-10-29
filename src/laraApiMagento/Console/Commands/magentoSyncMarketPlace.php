<?php

namespace App\Console\Commands;

use App\Asset;
use Illuminate\Console\Command;

class magentoSyncMarketPlace extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'magento:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Magento to marketplace.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {



        // Staging Keys
        //
        $api_token = "";
        $api_url = "";
        switch (config('app.env')) {
            case 'local':
                $api_url = "http://marketplace-stg.aindastudio.com";
                $api_token = "gslzlwwxcglusuj69mk64al79j0hfrwb";
                break;
            case 'staging':
                $api_url = "http://marketplace-stg.aindastudio.com";
                $api_token = "gslzlwwxcglusuj69mk64al79j0hfrwb";
                break;
            case 'production':
                // Consumer Key         uhzsfntuwwdc4906sgyow0rsn061wdh4
                // Consumer Secret      grwsdw2q4y96kxkiis4d3zwqikv927ux
                // Access Token         mvga1x2v0arzazwa33smqe36phxianhn
                // Access Token Secret  fsljyrtoje8rgjq1v98eqwhkvimyr1qg
                $api_url = "http://10.1.181.252";
                $api_token = "mvga1x2v0arzazwa33smqe36phxianhn";
                break;
            default:
                # code...
                break;
        }


        $assets = Asset::withoutGlobalScopes()->where('organization_id', '=', 1)->get();
        foreach ($assets as $asset) {
            try {
                $url = $api_url . "/rest/V1/products/" . $asset->_id;
                $this->info("Checking for Updates on asset: $url");

                $client = new \GuzzleHttp\Client();
                $magento_response = $client->get(
                    $api_url . "/rest/V1/products/" . $asset->_id,
                    [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $api_token,
                            'Content-Type' => 'application/json',
                            'Accept' => 'application/json',
                        ],
                    ]
                );
                $magento_product = json_decode($magento_response->getBody(), true);

                // if (($magento_product['name'] != $asset['name']) || ($magento_product['price'] > 0 )) {

                $name = $magento_product['name'];
                $price = $magento_product['price'];
                $status = $magento_product['status'];

                $asset['marketplace'] = ["status" => $status, "price" => $price];

                $asset->save();
            } catch (\Exception $ex) {

                // https://devdocs.magento.com/guides/m1x/api/soap/catalog/catalogProduct/catalog_product.create.html
                $client2 = new \GuzzleHttp\Client();
                $magento_response2 = $client2->Post(
                    $api_url . "/rest/V1/products/",
                    [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $api_token,
                            'Content-Type' => 'application/json',
                            'Accept' => 'application/json',
                        ],
                        'query' => ['product' => [
                            "storeView" => "default",
                            "name" => $asset->name,
                            "sku" => $asset->_id,
                            "type_id" => "downloadable",
                            "status" => 1,
                            "visibility" => 4,
                            "price" => 0,
                            "attribute_set_id" => 4,
                            "stock_data" => [
                                "qty" => 1000, //Quantity of items
                                "is_in_stock" => 1
                            ],
                            "custom_attributes" => [
                                [
                                    "attribute_code" => "url_key",
                                    "value" => 'product-' . $asset->_id,
                                ]
                            ],

                        ]],

                    ]
                );
            }
        }
    }
}
