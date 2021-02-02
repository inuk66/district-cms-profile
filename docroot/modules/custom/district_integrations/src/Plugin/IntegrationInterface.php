<?php

namespace Drupal\district_integrations\Plugin;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides an interface defining a Integration plugin.
 */
interface IntegrationInterface extends ConfigurablePluginInterface, PluginInspectionInterface {

  /**
   * Returns if a plugin is configured.
   *
   * @return bool
   *   TRUE if this plugin is configured.
   */
  public function isConfigured();

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
  public function settingsForm(array $form, FormStateInterface $form_state);

}
