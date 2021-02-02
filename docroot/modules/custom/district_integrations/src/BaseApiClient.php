<?php

namespace Drupal\district_integrations;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;

/**
 * Class BaseApiClient.
 *
 * This class is a starting point for API integrations that need to perform
 * external requests. Most things can just extend this class, more complex
 * classes might want to copy this as a boilerplate.
 *
 * @package Drupal\district_integrations
 */
abstract class BaseApiClient {

  /**
   * Drupal\Core\Cache\CacheBackendInterface definition.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cacheDefault;

  /**
   * GuzzleHttp\ClientInterface definition.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * Logger instance.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Config factory instance.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Plugin configuration.
   *
   * @var array
   */
  protected $config = [];

  /**
   * Cache key for storage.
   *
   * @var string
   */
  protected $cacheKey = 'di';

  /**
   * How long to cache results for (in seconds).
   *
   * @var int
   */
  protected $cacheExpiry = (86400 * 7);

  /**
   * Should the following requests be cached.
   *
   * @var bool
   */
  protected $cacheData = TRUE;

  /**
   * The user agent to use for http requests.
   *
   * @var string
   */
  protected $userAgent = 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116 Safari/537.36';

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
    $this->cacheDefault = $cache_default;
    $this->httpClient = $client;
    $this->logger = $logger;
    $this->configFactory = $config_factory;
  }

  /**
   * Request remote content via a get request.
   *
   * @param string $url
   *   Full url to the.
   * @param bool $json
   *   If set TRUE, response will get json decoded. If FALSE raw content.
   *
   * @return mixed
   *   Remote content body as raw or json.
   */
  public function get($url, $json = TRUE) {
    $cid = $this->getCacheKey($url);

    // Attempt to get from cache first.
    if (($cache_bin = $this->cacheDefault->get($cid)) && $this->cacheData) {
      return $cache_bin->data;
    }

    // Remote lookup.
    try {
      $response = $this->httpClient->get($url, ['headers' => ['User-Agent' => $this->userAgent]]);
      $body = (string) $response->getBody();
      $data = $json ? Json::decode($body) : $body;
      $this->cacheDefault->set($cid, $data, $this->getCacheExpiry());
      return $data;
    }
    catch (\Exception $e) {
      // @todo Log error.
    }

    return [];
  }

  /**
   * Set if cache should be used for the following requests.
   *
   * @param bool $cache
   *   TRUE if cache should be used, FALSE if not.
   *
   * @return $this
   *   Instance of this class.
   */
  public function setUseCache($cache) {
    $this->cacheData = $cache;
    return $this;
  }

  /**
   * Get the configuration.
   *
   * @return array
   *   Configuration for this plugin.
   */
  public function getConfig() {
    return $this->config;
  }

  /**
   * Get cache key for endpoint.
   *
   * @param string $endpoint
   *   Url that data is retrieved from.
   *
   * @return string
   *   Cache key for this url.
   */
  protected function getCacheKey($endpoint) {
    return $this->cacheKey . ':' . base64_encode($endpoint);
  }

  /**
   * Get cache expiry.
   *
   * @return float|int
   *   When should this cache expire.
   */
  protected function getCacheExpiry() {
    return time() + $this->cacheExpiry;
  }

}
