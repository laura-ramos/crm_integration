<?php

namespace Drupal\crm_integration;

use Drupal\Core\Database\Connection;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;

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
   */
  public function createUser($user) {
    // get token
    $oauth = \Drupal::service('crm_integration.auth_service');
    $accessToken = $oauth->getAccessToken();
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
      $request = $this->httpClient->post('https://www.zohoapis.com/bigin/v1/Contacts', [
        'headers' => $headers,
        'body' => $body
      ]);
      $res_body = $request->getBody();
      $response  = json_decode($res_body->getContents());
      return $response->data;

    } catch (RequestException $e) {
      $exception = $e->getResponse()->getBody();
      $exception = json_decode($exception);
      return $exception->message;
    }
   }
  
}