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
    $service = \Drupal::service('crm_integration.auth_service');

    $form = $this->formBuilder()->getForm('Drupal\crm_integration\Form\CRMIntegrationForm');
    //get redirect url
    $redirect = Url::fromRoute('crm_integration.callback',[], ['absolute' => true])->toString();
    //scopes for API requests to restrict clients from accessing unauthorized resources
    $scope = 'ZohoBigin.modules.contacts.CREATE';
    //The authorization request link
    $authUrl = $service->urlAccount().'/oauth/v2/auth?scope='.$scope.'&client_id='.$config->get('client_id').
    '&response_type=code&access_type=offline&redirect_uri='.$redirect.'&prompt=consent';
    $revokeUrl = Url::fromRoute('crm_integration.revoke',[])->toString();

    return [
      '#theme' => 'form_settings',
      '#auth_url' => $authUrl,
      '#form' => $form,
      '#revoke_url' => $config->get('access_token') ? $revokeUrl : null,
    ];
  }

  public function callback() {
    if($this->currentRequest->query->has('code')) {
      //  Get access tokens using authorization code 
      $authService = \Drupal::service('crm_integration.auth_service');
      $access = $authService->generateAccessToken($this->currentRequest->query->get('code'));
      $this->messenger()->addMessage($access ? 'Success' : 'Failed');
      return $this->redirect('crm_integration.admin_settings_form');
    }
  }

  /**
   * Revoke the refresh token.
   */
  public function revoke() {
    $authService = \Drupal::service('crm_integration.auth_service');
    try {
      $data = $authService->revokeToken();
      $this->messenger->addMessage($data, 'status');
    } catch (GuzzleException $e) {
      $this->messenger->addMessage($this->t('We have problems trying revoke your token.'), 'error');
    }
    return $this->redirect('crm_integration.admin_settings_form');
  }
}