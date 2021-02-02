<?php

namespace Drupal\district_integrations_simpleview;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\district_integrations\BaseApiClient;
use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;
use SimpleView\SimpleViewLibrary;
use SimpleView\SimpleViewFilter;
use Drupal\district_integrations\Traits\IntegrationHelperTrait;

/**
 * Provides a service to interact with Simpleview CRM.
 *
 * @package Drupal\district_integrations_simpleview
 */
class SimpleviewService extends BaseApiClient {

  use IntegrationHelperTrait;

  /**
   * Cache key for storage.
   *
   * @var string
   */
  protected $cacheKey = 'di_simpleview';

  /**
   * Simpleview Library.
   *
   * @var \SimpleView\SimpleViewLibrary
   */
  protected $client;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_default
   *   Cache service.
   * @param \GuzzleHttp\ClientInterface $client
   *   Http client.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger instance.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory instance.
   */
  public function __construct(
    CacheBackendInterface $cache_default,
    ClientInterface $client,
    LoggerInterface $logger,
    ConfigFactoryInterface $config_factory
  ) {
    parent::__construct($cache_default, $client, $logger, $config_factory);
    $this->config = district_integrations_plugin_config('api', 'simpleview');
    $this->client = new SimpleViewLibrary($this->getClientConfig());
  }

  /**
   * Get Simpleview business listings.
   *
   * @param int $pageSize
   *   Items per page, maximum is 250.
   * @param int $pageNum
   *   Requested page number.
   * @param object|null $filter
   *   Filter data object or NULL to use default filter.
   * @param bool $displayAmenities
   *   Flag to include amenities in the listing data.
   *
   * @return array
   *   Array of DATA and STATUS or empty array if API request failed.
   */
  public function getListings($pageSize = 250, $pageNum = 1, $filter = NULL, $displayAmenities = FALSE) {
    if (empty($filter)) {
      // Default filter which should return all listings.
      $filter = SimpleViewFilter::filterAllListings();
    }

    $response = $this->getData('getListings', [
      $pageSize,
      $pageNum,
      $filter,
      $displayAmenities,
    ]);
    if (!empty($response['DATA'])) {
      foreach ($response['DATA'] as &$listing) {
        $listing = $this->parseListing($listing);
      }
    }

    return $response;
  }

  /**
   * Parse a single listing.
   *
   * @param array $listing
   *   Single listing array.
   *
   * @return mixed
   *   Parsed listing.
   */
  protected function parseListing(array $listing) {
    // Append ADDR3 to ADDR2 if not empty.
    if (!empty($listing['ADDR3'])) {
      $listing['ADDR2'] .= ', ' . $listing['ADDR3'];
    }
    // Ensure urls.
    $listing['WEBURL'] = $this->ensureExternalUrl($listing['WEBURL']);
    // Group some fields for component paragraph contact.
    if (!empty($listing['EMAIL']) || !empty($listing['PRIMARYCONTACTFULLNAME']) || !empty($listing['SOCIALMEDIA'])) {
      $urls = [];
      foreach ($listing['SOCIALMEDIA'] as $social) {
        $urls[] = [
          'uri' => $this->ensureExternalUrl($social['VALUE']),
          'title' => $social['SERVICE'],
        ];
      }
      $listing['FIELD_CONTACT'] = [
        [
          'type' => 'contact',
          'field_name' => $listing['PRIMARYCONTACTFULLNAME'],
          'field_email' => $listing['EMAIL'],
          'field_urls' => $urls,
        ],
      ];
    }
    return $listing;
  }

  /**
   * Get data from Simpleview Library SOAP client.
   *
   * Data will be cached using method and params for the cache key.
   *
   * @param string $method
   *   Simpleview Library class method which correspond to SOAP API method.
   * @param array $params
   *   Parameters for the method being called.
   *
   * @return array
   *   Array of DATA and STATUS or empty array if API request failed.
   *
   * @see \SimpleView\SimpleViewLibrary
   */
  public function getData(string $method, array $params = []) {
    // Use combination of method and params for cache key.
    $cacheKey = $method . ':' . json_encode($params);
    $cid = $this->getCacheKey($cacheKey);

    // Attempt to get from cache first.
    if ($cache_bin = $this->cacheDefault->get($cid)) {
      return $cache_bin->data;
    }

    $response = $this->client->$method(...$params);

    // Store in cache if no errors.
    if (isset($response['STATUS']['HASERRORS']) && empty($response['STATUS']['HASERRORS'])) {
      $this->cacheDefault->set($cid, $response, $this->getCacheExpiry());
    }

    return is_array($response) ? $response : [];
  }

  /**
   * Return config object for SimpleViewLibrary client.
   *
   * @return object
   *   Config object
   *
   * @see \SimpleView\SimpleViewLibrary
   */
  protected function getClientConfig() {
    $clientConfig = [
      'clientUserName' => $this->config['api_username'],
      'clientPassword' => $this->config['api_password'],
      'serviceUrl' => trim($this->config['api_url'], '/') . '/webapi/listings/soap/listings.cfc?wsdl',
    ];

    return (object) $clientConfig;
  }

}
