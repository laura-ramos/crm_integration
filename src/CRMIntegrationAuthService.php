<?php

namespace Drupal\crm_integration;

use Drupal\Core\Database\Connection;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Drupal\Core\Url;
use Drupal\Core\Config\ConfigFactoryInterface;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class CRMIntegrationAuthService.
 */
class CRMIntegrationAuthService {

  /**
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * Config Factory service object.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new Service object.
   */
  public function __construct(ClientInterface $httpClient, ConfigFactoryInterface $configFactory,) {
    $this->httpClient = $httpClient;
    $this->configFactory = $configFactory;
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
    $clientId = $this->setting('client_id');
    $clientSecret = $this->setting('client_secret');
    $redirectUri = Url::fromRoute('crm_integration.callback',[], ['absolute' => true])->toString();

    $params = [
      'client_id' => $clientId,
      'client_secret' => $clientSecret,
      'code' => $code,
      'grant_type' => 'authorization_code',
      'redirect_uri' => $redirectUri
    ];
    try {
      $request = $this->httpClient->request('POST', $this->urlAccount().'/oauth/v2/token', [
        'query' => $params
      ]);
      $response = $request->getBody();
      $data  = json_decode($response->getContents());
      // save token in config for now
      if(!empty($data->access_token)) {
        $this->configFactory->getEditable('crm_integration.settings')
        ->set('access_token',$data->access_token)
        ->set('refresh_token',$data->refresh_token)
        ->save();
      }
      return empty($data->access_token) ? false : true;
      
    } catch (GuzzleException $e) {
      \Drupal::logger('crm')->error("Error trying generate Token. Exception message: {$e->getMessage()}");
      return false;
    }
  }

  /**
   * Get access token.
   *
   * @return string
   *   token.
   */
  public function getAccessToken() {
    return $this->setting('access_token') ?? '';
  }

  /**
   * Refresh token.
   *
   * @return bool
   *   token.
   */
  public function refreshToken() {
    $clientId = $this->setting('client_id');
    $clientSecret = $this->setting('client_secret');

    $params = [
      'client_id' => $clientId,
      'client_secret' => $clientSecret,
      'refresh_token' => $this->setting('refresh_token'),
      'grant_type' => 'refresh_token',
    ];
    try {
      $request = $this->httpClient->request('POST', $this->urlAccount().'/oauth/v2/token', [
        'query' => $params
      ]);
      $response = $request->getBody();
      $data  = json_decode($response->getContents());
      if(!empty($data->access_token)) {
        //save token
        $this->configFactory->getEditable('crm_integration.settings')->set('access_token',$data->access_token)->save();
      }
      return empty($data->access_token) ? false : true;
    } catch (GuzzleException $e) {
      \Drupal::logger('crm')->error("Error trying refresh Token. Exception message: {$e->getMessage()}");
      return false;
    }
  }

  /**
   * Revoke refresh tokens.
   *
   * @return string
   * 
   */
  public function revokeToken() {
    $params = [
      'token' => $this->getAccessToken(),
    ];
    try {
      $request = $this->httpClient->request('POST', $this->urlAccount().'/oauth/v2/token/revoke', [
        'query' => $params
      ]);
      if ($request->getStatusCode() == '200') {
        $response = $request->getBody();
        $data  = json_decode($response->getContents());

        $this->configFactory->getEditable('crm_integration.settings')
          ->set('access_token', '')->set('refresh_token', '')->save();
        return $data->status;
      }
    } catch (GuzzleException $e) {
      return $e->getMessage();
    }
  }

  /**
   * Get domain-specific accounts URL to generate access and refresh token.
   *
   * @return string
   *   The URL..
   */
  public function urlAccount() {
    $accountsArray = [
      'com' => 'https://accounts.zoho.com',
      'eu' => 'https://accounts.zoho.eu',
      'cn' => 'https://accounts.zoho.com.cn',
      'in' => 'https://accounts.zoho.in',
    ];
    return $accountsArray[$this->setting('domain')];
  }

  /**
   * Get domain specific accounts url for rest api.
   *
   * @return string
   *   The URL..
   */
  public function urlApi() {
    $apiArray = [
      'com' => 'https://www.zohoapis.com',
      'eu' => 'https://www.zohoapis.eu',
      'cn' => 'https://www.zohoapis.com.cn',
      'in' => 'https://www.zohoapis.in',
    ];
    return $apiArray[$this->setting('domain')];
  }

  /**
   * Access the settings of this module.
   *
   * @param string $key
   *   The key of the configuration.
   *
   * @return \Drupal\Core\Config\ImmutableConfig
   *   The value of the configuration item requested.
   */
  protected function setting($key) {
    return $this->configFactory->get('crm_integration.settings')->get($key);
  }
}