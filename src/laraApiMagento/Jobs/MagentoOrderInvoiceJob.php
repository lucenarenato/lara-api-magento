<?php

namespace App\Jobs;

use App\User;
use App\Asset;
use Exception;
use App\Countries;
use App\MarketPlace;
use App\Organization;
use App\OrderMagento;
use App\OrganizationMagento;
use Illuminate\Bus\Queueable;
use App\Traits\MagentoClientTrait;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class MagentoOrderInvoiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, MagentoClientTrait;

    private $organization_id;

    /**
     * @var Asset
     */

    private $orderOnMagento;

    /**
     * @var GuzzleHttp\Client
     */
    private $httpClient;
    protected $_id;
    protected $asset;
    private $asset_id;
    public $params, $row;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($params, $row)
    {
        $this->params = $params;
        $this->row = $row;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->loadProperties();

        $this->insert();
    }

    private function loadProperties()
    {
        //Log::debug('load');
        
        foreach ($this->row as $product) {
            //Log::debug($product['id']);
            $this->asset = Asset::withoutGlobalScopes()->where('_id', '=', $product['id'])->get()->first();
        }

        $this->httpClient = $this->createMagentoClient('/rest/all/V1/orders');

        $this->orderOnMagento = $this->getOrderOnMagento();
    }

    private function isCustomerRegisterOnMagento(): bool
    {
        $orderOnMagento = $this->asset->orderOnMagento;

        if ($orderOnMagento != null) {
            try {
                $this->httpClient->get(strval($orderOnMagento->asset_id));

                return true;
            } catch (ClientException $e) {

                $status_code = $e->getResponse()->getStatusCode();

                if ($status_code == 404) {
                    $orderOnMagento->delete();
                    return false;
                } else {
                    throw $e;
                }
            }
        }

        return false;
    }

    private function getOrderOnMagento()
    {
        $orderOnMagento = $this->asset->orderOnMagento;

        if (isset($orderOnMagento)) {
            try {
                $response = $this->httpClient->get(strval($orderOnMagento->asset_id));

                return json_decode($response->getBody(), true);
            } catch (ClientException $e) {
                $status_code = $e->getResponse()->getStatusCode();

                if ($status_code == 404) {
                    $orderOnMagento->delete();
                } else {
                    throw $e;
                }
            }
        }
    }

    private function getAddresses()
    {
        $addresses = [];
        $addresses = [
            'country_id' => $this->getCounty(),
            'firstname' => $this->organization->name,
            'lastname' => '-', //Last name is required                        
            'email' => $this->organization->email1,
            'postcode' => $this->organization->zip_code,
            'region' => $this->organization->state,
            'street' => [$this->organization->street_adress]
        ];

        if (!empty($this->orderOnMagento["addresses"][0])) {
            $addresses += ['id' => $this->orderOnMagento["addresses"][0]["id"]];
        }

        if (isset($this->organization->street_adress)) {
            $addresses += ['street' => [$this->organization->street_adress]];
        }

        if (isset($this->organization->fone1)) {
            $addresses += ['telephone' => $this->organization->fone1];
        }

        if (isset($this->organization->zip_code)) {
            $addresses += ['postcode' => $this->organization->zip_code];
        }

        if (isset($this->organization->city)) {
            $addresses += ['city' => $this->organization->city];
        }

        return $addresses;
    }

    private function getCounty()
    {
        $this->organization = Organization::where('id', '=', $this->params['id_organization'])->get()->first();
        $country = Countries::where('name', $this->organization->country)->first();

        if (isset($country)) {
            return substr($country->country_code, 0, 2);
        }
    }

    private function getProductsOnMagento()
    {
        try {
            $httpClient = $this->getMagentoClient('/rest/all/V1/products/');
            $response = $httpClient->get(strval($this->asset->id));
            $magProd = json_decode($response->getBody(), true);
            return $magProd;
        } catch (ClientException $e) {
            $e->getResponse()->getStatusCode();
            Log::error($e->getResponse()->getStatusCode());
        }
    }

    private function getItems($product, $assetCount)
    {
        $magProd = $this->getProductsOnMagento();

        $items = $product;
        //Log::info(json_encode($items, JSON_PRETTY_PRINT));
        $getItems = [];
       
        $collect = array();
        $collect["base_original_price"] = $items['price'];
        $collect["base_price"] = $items['price'];
        $collect["base_price_incl_tax"] = $items['price'];
        $collect["base_row_invoiced"] = 0;
        $collect["base_row_total"] = $items['price'];
        $collect["base_tax_amount"] = 0;
        $collect["base_tax_invoiced"] = 0;
        $collect["discount_amount"] = 0;
        $collect["discount_percent"] = 0;
        $collect["free_shipping"] = 0;
        $collect["is_virtual"] = 1;
        $collect["name"] = $items['name'];
        $collect["description"] = $items['size'];
        $collect["original_price"] = $items['price'];
        $collect["price"] = $items['price'];
        $collect["price_incl_tax"] = $items['price'];
        $collect["product_id"] = $magProd['id'];
        $collect["product_type"] = "downloadable";
        $collect["qty_ordered"] = $assetCount;
        $collect["row_total"] = $items['price'];
        $collect["row_total_incl_tax"] = $items['price'];
        $collect["sku"] = $items['id'] . '-' . $items['sku'];
        $collect["store_id"] = 1;
        //$getItems[] = $collect;
        
        Log::debug($assetCount);

        return $collect;
    }
    private function insert()
    {
        $getItems = [];
        logger($this->row);
        
        foreach ($this->row as $product) {
            $asset = Asset::withoutGlobalScopes()->where('_id', '=', $product['id'])->get()->first();
            
            $assetCount = Asset::withoutGlobalScopes()->where('_id', '=', $product['id'])->count();
            
            $item = $this->getItems($product, $assetCount);
            array_push($getItems, $item);
            $collectPrice = collect($getItems)->sum('price');
            
        }
        
        $itensCount = count($getItems);
        Log::info($itensCount);
        logger(json_encode($asset, JSON_PRETTY_PRINT));
        
        logger($assetCount);
        
        //Log::info($itensCount);
        //Log::debug($getItems);
        $this->organization = Organization::where('id', '=', $this->params['id_organization'])->get()->first();
        $customer_id = OrganizationMagento::select('customer_id')->where('customer_id', '=', $this->organization->id)->first()->customer_id;
        
        $body = [
            'entity' => [
                "base_currency_code" => "EUR",
                "base_discount_amount" => 0,
                "base_discount_tax_compensation_amount" => 0,
                "base_grand_total" => $collectPrice,
                "base_shipping_amount" => 0,
                "base_shipping_discount_amount" => null,
                "base_shipping_incl_tax" => null,
                "base_shipping_tax_amount" => null,
                "base_subtotal" => $collectPrice,
                "base_subtotal_incl_tax" => $collectPrice,
                "base_tax_amount" => 0,
                "base_to_global_rate" => 1,
                "base_to_order_rate" => 1,
                "base_total_due" => $collectPrice,
                "base_total_paid" => $collectPrice,
                "total_item_count" => $itensCount,
                "total_qty_ordered" => $itensCount,
                "grand_total" => $collectPrice,
                "subtotal" => $collectPrice,
                "subtotal_incl_tax" => $collectPrice,
                "total_item_count" => $itensCount,
                "total_qty_ordered" => $itensCount,
                'billing_address' => $this->getAddresses(),
                'email_sent' => null,
                "customer_note_notify" => 1,
                'store_name' => 'Ainda Studio',
                'payment' => [
                    "amount_ordered" => $itensCount, //quantidade pedida
                    "amount_paid" => $product['price'],      //valor pago
                    "base_amount_ordered" => $product['price'], // quantidade base pedida
                    "base_amount_paid" => $product['price'], //valor base pago
                    "base_shipping_amount" => 0, // valor base do envio
                    "method" => "banktransfer", //checkmo banktransfer paypal_express// meio de pagamento
                    "shipping_amount" => 0 //valor do frete
                ],
                "customer_id" => $customer_id,
                "customer_firstname" => $this->organization->name,
                "customer_email" => $this->organization->email1,
                "items" => $getItems,
                "is_virtual" => 1,
                "store_currency_code" => "EUR",
                "order_currency_code" => "EUR",
                "shipping_description" => "Ainda - downloadable",
                "state" => "new",
                "status" => "pending",

            ]
        ];
        // Log::info('$body');
        // Log::info($body);

        $response = $this->httpClient->post("", ['body' => json_encode($body)]);

        $data = json_decode($response->getBody(), true);
        //logger($data);

        OrderMagento::create([
            "customer_id" => $data['customer_id'],
            "entity_id" => $data['entity_id'],
            "customer_firstname" => $this->organization->name,
            "increment_id" => $data['increment_id'],
            "order_date" => $data['created_at']
        ]);

        $orderInvoice = $this->orderInvoiceOnMagento($data);
    }

    private function orderInvoiceOnMagento($data)
    {
        try {
            logger($data); 
            //logger($getItems);die;
            $httpClient = $this->createMagentoClient('/rest/all/V1/order/' . $data['entity_id'] . '/invoice');

            $body = [
                'entity' => [
                    'order_id' => $data['entity_id'],
                    //'total_qty' => 1,
                    'capture' => true,
                    "items" => [
                        //"sku" => 0,
                        "order_item_id"=> $data['entity_id'],
                        "qty" => 1
                    ],
                    "notify" => true
                ]
            ];

            
            $response = $httpClient->post("", ['body' => json_encode($body)]);


            $invoice = json_decode($response->getBody(), true);
            logger($invoice);

            $httpInvoice = $this->createMagentoClient('/rest/all/V1/invoices/' . $invoice . '/emails');
            $emails = $httpInvoice->post("", ['body' => '']);           

        } catch (ClientException $e) {
            $e->getResponse()->getStatusCode();
            Log::error($e->getResponse()->getStatusCode());
        }
    }
}
