<?php

namespace Drupal\district_integrations_atdw\Plugin\migrate_plus\data_fetcher;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate_plus\DataFetcherPluginBase;

/**
 * Retrieve products from ATDW.
 *
 * Example:
 *
 * @code
 * source:
 *   plugin: url
 *   data_fetcher_plugin: atdw_products
 *   categories: 'ACCOMM,ATTRACTION,RESTAURANT'
 *
 * @DataFetcher(
 *   id = "atdw_products",
 *   title = @Translation("ATDW Products")
 * )
 */
class AtdwProducts extends DataFetcherPluginBase implements ContainerFactoryPluginInterface {

  /**
   * ATDW API Service.
   *
   * @var \Drupal\district_integrations_atdw\AtdwApiService
   */
  protected $service;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->service = \Drupal::service('district_integrations_atdw.api_service');
  }

  /**
   * {@inheritdoc}
   */
  public function setRequestHeaders(array $headers) {
    // Does nothing.
  }

  /**
   * {@inheritdoc}
   */
  public function getRequestHeaders() {
    // Does nothing.
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getResponse($url) {
    $params = [];

    // @todo See below.
    // Get Highwater then pass [product_update_date => highwater date]
    // So only getting updated results.
    // $hw = \Drupal::keyValue('migrate:high_water')->get($migration->id());
    // Specify fetching of only certain categories.
    if (!empty($this->configuration['categories'])) {
      $params['cats'] = $this->configuration['categories'];
    }

    $response = $this->service->getProducts($params);
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function getResponseContent($url) {
    $response = $this->getResponse($url);
    return Json::encode($response);
  }

}
