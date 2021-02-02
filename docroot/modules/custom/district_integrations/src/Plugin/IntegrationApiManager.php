<?php

namespace Drupal\district_integrations\Plugin;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Provides a IntegrationApiManager plugin manager.
 */
class IntegrationApiManager extends IntegrationPluginManager implements IntegrationManagerInterface {

  /**
   * Constructs a new IntegrationApiManager.
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
      'Api',
      $namespaces,
      $cache_backend,
      $module_handler,
      'Drupal\district_integrations\Annotation\Integration'
    );
  }

}
