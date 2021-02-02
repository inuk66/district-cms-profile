<?php

namespace Drupal\district_integrations\Plugin;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Provides a Integration plugin manager.
 */
class IntegrationManager extends DefaultPluginManager implements IntegrationManagerInterface {

  /**
   * Constructs a new IntegrationManager.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct(
      'Plugin/Integration',
      $namespaces,
      $module_handler,
      'Drupal\district_integrations\Plugin\IntegrationInterface',
      'Drupal\district_integrations\Annotation\Integration'
    );
    $this->setCacheBackend($cache_backend, 'district_integrations_plugins_integration');
  }

}
