<?php

namespace Drupal\district_integrations_examples\Plugin\Integration\Link;

use Drupal\Core\Form\FormStateInterface;
use Drupal\district_integrations\Plugin\Integration\Link\IntegrationLinkBase;

/**
 * Plugin implementation of the 'sample_embed' integration type.
 *
 * @Integration(
 *   id = "page_up",
 *   label = @Translation("PageUp"),
 *   group = "Jobs"
 * )
 */
class PageUp extends IntegrationLinkBase {

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

    $form['heading'] = $this->getLinksSettingsTablePrefix();
    $form['urls'] = $this->generateLinkTable($config['urls']);

    return $form;
  }

}
