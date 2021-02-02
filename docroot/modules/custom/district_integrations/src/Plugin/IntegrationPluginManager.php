<?php

namespace Drupal\district_integrations\Plugin;

use Drupal\Component\Plugin\Factory\DefaultFactory;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Provides a Integration plugin manager.
 */
class IntegrationPluginManager extends DefaultPluginManager implements IntegrationPluginManagerInterface {

  /**
   * Type of plugin. Eg api, embed, link.
   *
   * @var string
   */
  protected $type;

  /**
   * Constructs a new IntegrationPluginManager.
   *
   * @param string $type
   *   The type of the plugin: Style.
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   * @param string $annotation
   *   (optional) The annotation class name. Defaults to
   *   'Drupal\Component\Annotation\PluginID'.
   */
  public function __construct($type, \Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler, $annotation = 'Drupal\Component\Annotation\PluginID') {
    parent::__construct('Plugin/Integration/' . $type, $namespaces, $module_handler, NULL, $annotation);
    $this->type = strtolower($type);
    $this->setCacheBackend($cache_backend, 'district_integrations_plugins_' . $this->type);
  }

  /**
   * {@inheritdoc}
   */
  public function createInstance($plugin_id, array $configuration = [], IntegrationInterface $integration = NULL) {
    $plugin_definition = $this->getDefinition($plugin_id);
    $configuration['id'] = $plugin_id;
    $configuration['type'] = $this->type;

    $plugin_class = DefaultFactory::getPluginClass($plugin_id, $plugin_definition);

    if (is_subclass_of($plugin_class, 'Drupal\Core\Plugin\ContainerFactoryPluginInterface')) {
      $plugin = $plugin_class::create(\Drupal::getContainer(), $configuration, $plugin_id, $plugin_definition, $integration);
    }
    else {
      $plugin = new $plugin_class($configuration, $plugin_id, $plugin_definition, $integration);
    }

    return $plugin;
  }

}
