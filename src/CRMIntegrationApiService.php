<?php

namespace Drupal\crm_integration;

use Drupal\Core\Database\Connection;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class CRMIntegrationApiService.
 */
class CRMIntegrationApiService {

  /**
   * Database connection.
   *
   * Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * Constructs a new Service object.
   */
  public function __construct(Connection $connection, ClientInterface $http_client) {
    $this->database = $connection;
    $this->httpClient = $http_client;
  }

  /**
   * Add new user to Bigin crm.
   *
   * @param array
   *   User data.
   *
   * @return string
   *   api rest response
   */
  public function createUser($user) {
    $oauth = \Drupal::service('crm_integration.auth_service');
    $accessToken = $oauth->getAccessToken(); // get token
    // define header
    $headers = [
      'Authorization' => 'Zoho-oauthtoken '.$accessToken,
      'Content-Type' => 'application/json'
    ];
    // user data
    $body = '{
      "data": [
        {
          "Last_Name": '.$user['name'].',
          "First_Name": "",
          "Email": '.$user['email'].',
          "Lead_Source": "",
        }
      ]
    }';
   
    try {
      $request = $this->httpClient->post($oauth->urlApi().'/bigin/v1/Contacts', [
        'headers' => $headers,
        'body' => $body
      ]);
      $resBody = $request->getBody();
      $response  = json_decode($resBody->getContents());
      return $response->data;

    } catch (GuzzleException $e) {
      \Drupal::logger('crm')->error("Error trying create contact Bigin api. Exception message: {$e->getMessage()}");
      return false;
    }
  }
}