<?php

namespace Drupal\district_integrations_simpleview\Plugin\migrate_plus\data_fetcher;

use Drupal\Component\Serialization\Json;
use Drupal\migrate_plus\DataFetcherPluginBase;

/**
 * Retrieve listings from Simpleview CRM.
 *
 * Example:
 *
 * @code
 * source:
 *   plugin: url
 *   data_fetcher_plugin: simpleview_listings
 *
 * @DataFetcher(
 *   id = "simpleview_listings",
 *   title = @Translation("Simpleview Listings")
 * )
 */
class SimpleviewListings extends DataFetcherPluginBase {

  /**
   * Simpleview Service.
   *
   * @var \Drupal\district_integrations_simpleview\SimpleviewService
   */
  protected $service;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->service = \Drupal::service('district_integrations_simpleview.service');
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
    // Limit page size to 10 for initial test.
    return $this->service->getListings(10);
  }

  /**
   * {@inheritdoc}
   */
  public function getResponseContent($url) {
    $response = $this->getResponse($url);
    return Json::encode($response);
  }

}
