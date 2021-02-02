<?php

namespace Drupal\district_integrations_spydus\Plugin\Integration\Api;

use Drupal\Core\Form\FormStateInterface;
use Drupal\district_integrations\Plugin\Integration\Api\IntegrationApiBase;
use Drupal\district_integrations\Plugin\Integration\IntegrationLinksTrait;

/**
 * Plugin implementation of the 'spydus' integration type.
 *
 * @Integration(
 *   id = "spydus",
 *   label = @Translation("Spydus"),
 *   group = "Library management"
 * )
 */
class Spydus extends IntegrationApiBase {

  use IntegrationLinksTrait;

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'base_url' => '',
      'urls' => [],
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function isConfigured() {
    $config = $this->getConfiguration();
    return !empty($config['base_url']);
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);
    $config = $this->getConfiguration();

    $form['urls_desc'] = [
      0 => ['#markup' => '<h4>Urls to get the data from</h4>'],
      1 => ['#markup' => '<p>Provide an id, title and spydus listing page url</p>'],
    ];
    $form['urls'] = $this->generateLinkTable($config['urls']);

    $form['base_url'] = [
      '#type' => 'textfield',
      '#title' => 'Base URL',
      '#description' => $this->t('The base url for spydus site.'),
      '#default_value' => $config['base_url'],
    ];

    return $form;
  }

}
