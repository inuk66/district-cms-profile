<?php

namespace Drupal\district_integrations_atdw\Plugin\Integration\Api;

use Drupal\Core\Form\FormStateInterface;
use Drupal\district_integrations\Plugin\Integration\Api\IntegrationApiBase;

/**
 * Plugin implementation of the 'atdw' integration type.
 *
 * @Integration(
 *   id = "atdw",
 *   label = @Translation("Australian Tourism Data Warehouse"),
 *   group = "Tourism"
 * )
 */
class Atdw extends IntegrationApiBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'api_url' => '',
      'api_key' => '',
      'coordinates' => ['lat' => '', 'lng' => ''],
      'distance' => 30,
      'categories' => '',
      'result_count' => 20,
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function isConfigured() {
    $config = $this->getConfiguration();
    return !empty($config['api_url']) && !empty($config['api_key']);
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
      '#description' => $this->t('The ATDW API URL. see http://developer.atdw.com.au/ATDWO-api.html for more information.'),
      '#default_value' => $config['api_url'],
    ];

    $form['api_key'] = [
      '#type' => 'textfield',
      '#title' => 'API Key',
      '#description' => $this->t('Authentication key for API usage.'),
      '#default_value' => $config['api_key'],
    ];

    $form['coordinates'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Coordinates'),
      '#tree' => TRUE,
      '#description' => $this->t("The 'Coordinates' parameters allows you to search for products within a specified location"),
      'lat' => [
        '#type' => 'number',
        '#title' => $this->t('Latitude'),
        '#default_value' => $config['coordinates']['lat'],
        '#required' => TRUE,
        '#step' => "0.001",
      ],
      'lng' => [
        '#type' => 'number',
        '#title' => $this->t('Longitude'),
        '#default_value' => $config['coordinates']['lng'],
        '#required' => TRUE,
        '#step' => "0.001",
      ],
    ];

    $form['distance'] = [
      '#type' => 'number',
      '#title' => $this->t('Distance'),
      '#default_value' => $config['distance'],
      '#required' => TRUE,
      '#description' => $this->t("The distance from the coordinates to search for products."),
    ];

    $form['categories'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Default Categories'),
      '#default_value' => $config['categories'],
      '#required' => FALSE,
      '#description' => $this->t("Refine results to these categories. One category per line"),
    ];

    $form['result_count'] = [
      '#type' => 'number',
      '#title' => $this->t('Results count'),
      '#default_value' => $config['result_count'],
      '#required' => TRUE,
      '#description' => $this->t("The number of results to fetch per import"),
    ];

    return $form;
  }

}
