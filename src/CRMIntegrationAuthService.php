<?php

namespace Drupal\crm_integration;

use Drupal\Core\Database\Connection;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;

/**
 * Class CRMIntegrationAuthService.
 */
class CRMIntegrationAuthService {

  /**
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * Constructs a new Service object.
   */
  public function __construct(ClientInterface $http_client) {
    $this->httpClient = $http_client;
  }

  /**
   * Generate access token.
   *
   * @param string $code
   *   Authorization code.
   *
   * @return bool
   *   Returns true if the access token could be generated.
   */
  public function generateAccessToken($code)
  {
    //https://www.bigin.com/developer/docs/apis/access-refresh.html
    $config = \Drupal::config('crm_integration.settings');
    $clientId = $config->get('client_id');
    $clientSecret = $config->get('client_secret');

    $params = [
      'client_id' => $clientId,
      'client_secret' => $clientSecret,
      'code' => $code,
      'grant_type' => 'authorization_code',
    ];
    try {
      $request = $this->httpClient->request('POST', $this->urlAccount().'/oauth/v2/token', [
        'query' => $params
      ]);
      $response = $request->getBody();
      $data  = json_decode($response->getContents());
      //save token in progress
      return empty($data->access_token) ? false : true;
      
    } catch (RequestException $e) {
      return false;
    }
  }

  /**
   * Get domain-specific accounts URL to generate access and refresh token.
   *
   * @return string
   *   The URL..
   */
  public function urlAccount() {
    $config = \Drupal::config('crm_integration.settings');
    $accountsArray = [
      'com' => 'https://accounts.zoho.com',
      'eu' => 'https://accounts.zoho.eu',
      'cn' => 'https://accounts.zoho.com.cn',
      'in' => 'https://accounts.zoho.in',
    ];
    return $accountsArray[$config->get('domain')];
  }

  /**
   * Get domain specific accounts url for rest api.
   *
   * @return string
   *   The URL..
   */
  public function urlApi() {
    $config = \Drupal::config('crm_integration.settings');
    $apiArray = [
      'com' => 'www.zohoapis.com',
      'eu' => 'www.zohoapis.eu',
      'cn' => 'www.zohoapis.com.cn',
      'in' => 'www.zohoapis.in',
    ];
    return $apiArray[$config->get('domain')];
  }
}