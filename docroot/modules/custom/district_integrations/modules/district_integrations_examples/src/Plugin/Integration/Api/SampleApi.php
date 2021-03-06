<?php

namespace Drupal\district_integrations_examples\Plugin\Integration\Api;

use Drupal\Core\Form\FormStateInterface;
use Drupal\district_integrations\Plugin\Integration\Api\IntegrationApiBase;

/**
 * Plugin implementation of the 'sample_api' integration type.
 *
 * @Integration(
 *   id = "sample_api",
 *   label = @Translation("Sample API provider"),
 *   group = "Tests"
 * )
 */
class SampleApi extends IntegrationApiBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'base_url' => '',
      'api_key' => '',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function isConfigured() {
    $config = $this->getConfiguration();
    return !empty($config['base_url'] && $config['api_key']);
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);
    $config = $this->getConfiguration();

    $form['base_url'] = [
      '#type' => 'textfield',
      '#title' => 'Base URL',
      '#description' => $this->t('The base url for this API.'),
      '#default_value' => $config['base_url'],
    ];

    $form['api_key'] = [
      '#type' => 'textfield',
      '#title' => 'API Key',
      '#description' => $this->t('The API key for this integration'),
      '#default_value' => $config['api_key'],
    ];

    return $form;
  }

}
