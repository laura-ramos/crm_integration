<?php
namespace Drupal\crm_integration\Controller;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\Core\Link;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Messenger\Messenger;

/**
 * Class CRMIntegrationController.
 *
 * @package Drupal\crm_integration\Controller
 */
class CRMIntegrationController extends ControllerBase {

  /**
   * Symfony\Component\HttpFoundation\Request definition.
   *
   * @var \Symfony\Component\HttpFoundation\Request|null
   */
  protected $currentRequest;

  /**
   * The Drupal messenger service.
   *
   * @var \Drupal\Core\Messenger\Messenger
   */
  protected $messenger;

  public function __construct(RequestStack $request, Messenger $messenger) {
    $this->currentRequest = $request->getCurrentRequest();
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack'),
      $container->get('messenger'),
    );
  }
  
  public function initialize()
  {
    $config = \Drupal::config('crm_integration.settings');

    $form = $this->formBuilder()->getForm('Drupal\crm_integration\Form\CRMIntegrationForm');
    //get redirect url
    $redirect = Url::fromRoute('crm_integration.callback',[], ['absolute' => true])->toString();
    //The authorization request link
    $authUrl = 'https://accounts.zoho.com/oauth/v2/auth?scope=ZohoBigin.users.ALL&client_id='.$config->get('client_id').'&response_type=code&access_type=online&redirect_uri='.$redirect;

    return [
      '#theme' => 'form_settings',
      '#auth_url' => $authUrl,
      '#form' => $form,
    ];
  }

  public function callback() {
    if($this->currentRequest->query->has('code')) {
      //  Get access tokens using authorization code 
      $authService = \Drupal::service('crm_integration.auth_service');
      $access = $authService->generateAccessToken($this->currentRequest->query->get('code'));
      return [
        '#type' => 'markup',
        '#markup' => $access ? 'Success' : 'Failed',
      ];
    }
  }
}