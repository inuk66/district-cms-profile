<?php

namespace Drupal\district_integrations\Plugin;

use Drupal\Component\Plugin\PluginManagerInterface;

/**
 * Provides an interface defining a IntegrationPluginManager.
 */
interface IntegrationPluginManagerInterface extends PluginManagerInterface {

  /**
   * Creates a pre-configured instance of a plugin.
   *
   * @param string $plugin_id
   *   The ID of the plugin being instantiated.
   * @param array $configuration
   *   An array of configuration relevant to the plugin instance.
   * @param \Drupal\district_integrations\Plugin\IntegrationInterface $integration
   *   The integration context in which the plugin will run.
   *
   * @return object
   *   A fully configured plugin instance.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   *   If the instance cannot be created, such as if the ID is invalid.
   */
  public function createInstance($plugin_id, array $configuration = [], IntegrationInterface $integration = NULL);

}
