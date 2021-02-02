<?php

namespace Drupal\district_integrations_simpleview\Plugin\Integration\Api;

use Drupal\Core\Form\FormStateInterface;
use Drupal\district_integrations\Plugin\Integration\Api\IntegrationApiBase;

/**
 * Plugin implementation of the 'simpleview' integration type.
 *
 * @Integration(
 *   id = "simpleview",
 *   label = @Translation("Simpleview"),
 *   group = "Tourism"
 * )
 */
class Simpleview extends IntegrationApiBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'api_url' => '',
      'api_username' => '',
      'api_password' => '',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function isConfigured() {
    $config = $this->getConfiguration();
    return !empty($config['api_url']) && !empty($config['api_username']) && !empty($config['api_password']);
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);
    $config = $this->getConfiguration();

    $form['api_url'] = [
      '#type' => 'textfield',
      '#title' => 'Base URL',
      '#description' => $this->t('Simpleview API base URL. E.g. https://clientname.simpleviewcrm.com'),
      '#default_value' => $config['api_url'],
    ];

    $form['api_username'] = [
      '#type' => 'textfield',
      '#title' => 'API Username',
      '#description' => $this->t('The API username for Simpleview API'),
      '#default_value' => $config['api_username'],
    ];

    $form['api_password'] = [
      '#type' => 'textfield',
      '#title' => 'API Password',
      '#description' => $this->t('The API password for Simpleview API'),
      '#default_value' => $config['api_password'],
    ];

    return $form;
  }

}
