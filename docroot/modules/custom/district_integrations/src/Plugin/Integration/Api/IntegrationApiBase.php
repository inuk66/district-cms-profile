<?php

namespace Drupal\district_integrations\Plugin\Integration\Api;

use Drupal\Core\Form\FormStateInterface;
use Drupal\district_integrations\Plugin\Integration\IntegrationBase;

/**
 * Provides IntegrationApi base.
 *
 * @package Drupal\district_integrations\Plugin\Integration\Api
 */
abstract class IntegrationApiBase extends IntegrationBase implements IntegrationApiInterface {

  /**
   * Return the settings form for this plugin.
   *
   * @param array $form
   *   Form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   *
   * @return array
   *   Form array.
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    return $form;
  }

}
