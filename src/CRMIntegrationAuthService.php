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
   * Get access token.
   */
  function getAccessToken()
  {
    //https://www.bigin.com/developer/docs/apis/access-refresh.html
    $config = \Drupal::config('crm_integration.settings');
    $clientId = $config->get('client_id');
    $clientSecret = $config->get('client_secret');

    $params = [
      'client_id' => $clientId,
      'client_secret' => $clientSecret,
      'code' => $this->getAuthorizationCode(),
      'grant_type' => 'authorization_code',
    ];
    try {
      $request = $this->httpClient->request('POST', 'https://accounts.zoho.com/oauth/v2/token', [
        'query' => $params
      ]);
      $response = $request->getBody();
      $data  = json_decode($response->getContents());
      if($data->error) {
        return $data->error;
      } else {
        return $data->access_token;
      }
    } catch (RequestException $e) {
      return 'Error';
    }
  }

  /**
   * Generate authorization code
   */
  function getAuthorizationCode()
  {
    //https://www.bigin.com/developer/docs/apis/auth-request.html
    //example code: 1000.f5d1590cdcfac313b344452b24a8f25a.f8ff8362ccfc30331b45fd78530b6687
    return 'xxx';
  }
}
