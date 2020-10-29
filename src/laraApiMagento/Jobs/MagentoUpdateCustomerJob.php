<?php

namespace App\Jobs;

use App\Countries;
use App\Organization;
use App\OrganizationMagento;
use App\Traits\MagentoClientTrait;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MagentoUpdateCustomerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, MagentoClientTrait;

    private $organization_id;

    /**
     * @var Organization
     */
    private $organization;

    private $customerOnMagento;

    /**
     * @var GuzzleHttp\Client
     */
    private $httpClient;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($organization_id)
    {
        $this->organization_id = $organization_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->loadProperties();

        if ($this->customerOnMagento != null) {
            $this->update();
        } else {
            $this->insert();
        }
    }

    private function loadProperties()
    {
        $this->organization = Organization::find($this->organization_id);

        $this->httpClient = $this->createMagentoClient('/rest/V1/customers/');

        $this->customerOnMagento = $this->getCustomerOnMagento();
    }

    private function isCustomerRegisterOnMagento(): bool
    {

        $customerMagento = $this->organization->customerMagento;

        if ($customerMagento != null) {
            try {
                $this->httpClient->get(strval($customerMagento->customer_id));

                return true;
            } catch (ClientException $e) {

                $status_code = $e->getResponse()->getStatusCode();

                if ($status_code == 404) {
                    $customerMagento->delete();
                    return false;
                } else {
                    throw $e;
                }
            }
        }

        return false;
    }

    private function getCustomerOnMagento()
    {

        $organizationOnMagento = $this->organization->customerMagento;

        if (isset($organizationOnMagento)) {
            try {
                $response = $this->httpClient->get(strval($organizationOnMagento->customer_id));

                return json_decode($response->getBody(), true);
            } catch (ClientException $e) {
                $status_code = $e->getResponse()->getStatusCode();

                if ($status_code == 404) {
                    $organizationOnMagento->delete();
                } else {
                    throw $e;
                }
            }
        }
    }

    private function update()
    {
        $body = ['customer' => [
            'id' => $this->customerOnMagento['id'],
            'website_id' => $this->customerOnMagento['website_id'],
            'email' => $this->organization->email1,
            'firstname' => $this->organization->name,
            'lastname' => ' ' //Last name is required,            
        ]];

        $country = $this->getCounty();
        if (isset($country)) {
            $body['customer']['addresses'] = [$this->getAddresses()];
        }

        $this->httpClient->put(strval($this->customerOnMagento['id']), ['body' => json_encode($body)]);
    }

    private function getAddresses()
    {
        $addresses = [
            'country_id' => $this->getCounty(),
            'firstname' => $this->organization->name,
            'lastname' => '-', //Last name is required                        
            'default_billing' => true
        ];

        if (!empty($this->customerOnMagento["addresses"][0])) {
            $addresses += ['id' => $this->customerOnMagento["addresses"][0]["id"]];
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
        $country = Countries::where('name', $this->organization->country)->first();

        if (isset($country)) {
            return substr($country->country_code, 0, 2);
        }
    }

    private function insert()
    {
        $body = [
            'customer' => [
                'email' => $this->organization->email1,
                'firstname' => $this->organization->name,
                'lastname' => ' ' //Last name is required                
            ]
        ];

        $country = $this->getCounty();
        if (isset($country)) {
            $body['customer']['addresses'] = [$this->getAddresses()];
        }

        $response = $this->httpClient->post("", ['body' => json_encode($body)]);
        $data = json_decode($response->getBody(), true);

        OrganizationMagento::create([
            "customer_id" => $data['id'],
            "organization_id" => $this->organization->id
        ]);
    }
}
