<?php

namespace Drupal\district_integrations_examples\Plugin\Integration\Embed;

use Drupal\Core\Form\FormStateInterface;
use Drupal\district_integrations\Plugin\Integration\Embed\IntegrationEmbedBase;

/**
 * Plugin implementation of the 'sample_embed' integration type.
 *
 * @Integration(
 *   id = "seat_advisor",
 *   label = @Translation("Seat advisor"),
 *   group = "Patron"
 * )
 */
class SeatAdvisor extends IntegrationEmbedBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'api_key' => '',
      'urls' => [],
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function isConfigured() {
    $config = $this->getConfiguration();
    return !empty($config['urls']);
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);
    $config = $this->getConfiguration();

    $form['heading'] = $this->getEmbedsSettingsTablePrefix();
    $form['urls'] = $this->generateLinkTable($config['urls']);

    $form['api_key'] = [
      '#type' => 'textfield',
      '#title' => 'API Key',
      '#description' => $this->t('The API key for this integration'),
      '#default_value' => $config['api_key'],
    ];

    return $form;
  }

}
