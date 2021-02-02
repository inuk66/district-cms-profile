<?php

namespace Drupal\district_integrations\Plugin\Integration;

use Drupal\Console\Command\Shared\TranslationTrait;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\district_integrations\Plugin\IntegrationInterface;
use Drupal\Component\Utility\NestedArray;
use Drupal\district_integrations\Plugin\IntegrationManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Provides a base class for Integration plugins.
 */
abstract class IntegrationBase extends PluginBase implements IntegrationInterface, ContainerFactoryPluginInterface {

  use TranslationTrait;

  /**
   * The columns for the table.
   *
   * @var string[]
   */
  protected $keys = ['id', 'label', 'url'];

  /**
   * The name of the rows.
   *
   * @var string
   */
  protected $collection = 'urls';

  /**
   * Base integration manager.
   *
   * @var \Drupal\district_integrations\Plugin\IntegrationManagerInterface
   */
  protected $integrationPluginManager;

  /**
   * Module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Prefix for configuration key name.
   *
   * @var string
   */
  protected $configPrefix = 'district_integrations';

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new VisualisationBase.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\district_integrations\Plugin\IntegrationManagerInterface $integration_plugin_manager
   *   The source plugin manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Instance of the module handler.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Instance of config factory.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    IntegrationManagerInterface $integration_plugin_manager,
    ModuleHandlerInterface $module_handler,
    ConfigFactoryInterface $config_factory
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->integrationPluginManager = $integration_plugin_manager;
    $this->moduleHandler = $module_handler;

    // Get config for this specific plugin.
    $this->configFactory = $config_factory;

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.integration'),
      $container->get('module_handler'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    $config = $this->configFactory->get($this->getConfigurationKey())->get();
    $config = !empty($config) ? $config : $this->configuration;
    return NestedArray::mergeDeep($this->defaultConfiguration(), $config);
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = $configuration;
    $config_editable = $this->configFactory->getEditable($this->getConfigurationKey());

    // Save all updated config for this plugin.
    $config_editable->setData($configuration)->save();
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return [];
  }

  /**
   * Get config location for this plugin.
   *
   * @return string
   *   Config key for this plugin.
   */
  protected function getConfigurationKey() {
    return implode('.', [
      $this->configPrefix,
      $this->configuration['type'],
      $this->configuration['id'],
    ]);
  }

}
