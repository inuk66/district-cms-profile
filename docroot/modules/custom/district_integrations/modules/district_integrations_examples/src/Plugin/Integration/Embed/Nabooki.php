<?php

namespace Drupal\district_integrations_examples\Plugin\Integration\Embed;

use Drupal\Core\Form\FormStateInterface;
use Drupal\district_integrations\Plugin\Integration\Embed\IntegrationEmbedBase;

/**
 * Plugin implementation of the 'nabooki' integration type.
 *
 * @Integration(
 *   id = "nabooki",
 *   label = @Translation("Nabooki"),
 *   group = "Bookings"
 * )
 */
class Nabooki extends IntegrationEmbedBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
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

    return $form;
  }

}
