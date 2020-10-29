<?php

namespace App\Traits;

trait MagentoClientTrait
{

  /**
   * @param string $endPoint 
   * @return \GuzzleHttp\Client
   */
    public static function createMagentoClient($endPoint)
    {
        return new \GuzzleHttp\Client([
          'base_uri' => env('MAGENTO_URL') . $endPoint,
          'headers' => [
              'Authorization' => 'Bearer ' . env('MAGENTO_TOKEN'),
              'Content-Type' => 'application/json',
              'Accept' => 'application/json',
          ]
      ]);
    }

    public static function getMagentoClient($endPoint)
    {
        return new \GuzzleHttp\Client([
          'base_uri' => env('MAGENTO_URL') . $endPoint,
          'headers' => [
              'Authorization' => 'Bearer ' . env('MAGENTO_TOKEN'),
              'Content-Type' => 'application/json',
              'Accept' => 'application/json',
          ]
      ]);
    }
}
