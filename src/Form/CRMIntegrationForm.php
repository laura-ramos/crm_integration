<?php

/**
 * @file
 * Content the settings for administering the CRM integration form.
 */

namespace Drupal\crm_integration\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Path\PathValidatorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;


class CRMIntegrationForm extends ConfigFormBase
{
  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    // Unique ID of the form.
    return 'crm_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames()
  {
    return [
      'crm_integration.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $config = $this->config('crm_integration.settings');

    $form['client_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Client ID'),
      '#default_value' => $config->get('client_id'),
      '#required' => TRUE,
    ];

    $form['client_secret'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Client Secret'),
      '#default_value' => $config->get('client_secret'),
      '#required' => TRUE,
    ];

    $form['domain'] = [
      '#type' => 'select',
      '#title' => $this->t('Choose your Domain'),
      '#default_value' => $config->get('domain'),
      '#options' => [
        'com' => $this->t('.COM (Default)'),
        'eu' => $this->t('.EU'),
        'cn' => $this->t('.CN'),
        'in' => $this->t('.IN'),
      ],
      '#required' => TRUE,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $config_keys = [
      'client_id', 'client_secret', 'domain',
    ];
    $crm_config = $this->config('crm_integration.settings');
    foreach ($config_keys as $config_key) {
      if ($form_state->hasValue($config_key)) {
        $crm_config->set($config_key, $form_state->getValue($config_key));
      }
    }
    $crm_config->save();
    $this->messenger()->addMessage($this->t('The configuration options have been saved.'));
    parent::submitForm($form, $form_state);
  }
}
