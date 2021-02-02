<?php

namespace Drupal\district_integrations_spydus;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\district_integrations\BaseApiClient;
use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Provides a service to interact with Spydus.
 *
 * @package Drupal\district_integrations_spydus
 */
class SpydusService extends BaseApiClient {

  /**
   * Cache key for storage.
   *
   * @var string
   */
  protected $cacheKey = 'di_spydus';

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
    $this->config = district_integrations_plugin_config('api', 'spydus');
  }

  /**
   * Get the parsed data from an endpoint by id.
   *
   * @param string $id
   *   Id of the endpoint.
   *
   * @return array
   *   Associative array containing id, label, url, base_url and parsed items.
   */
  public function getData($id) {
    $endpoint = $this->getEndpoint($id);
    if (empty($endpoint['url'])) {
      return [];
    }

    // Scrape the remote url for raw data.
    $html = $this->get($endpoint['url'], FALSE);
    $base_url = $this->getHost($endpoint['url']);

    // The following parses the DOM to get the structured data.
    $items = $this->getCrawler($html)
      ->filter('.card-list-image-body > a')
      ->reduce(function (Crawler $node, $i) {
        return $node->filter('img')->first();
      })
      ->each(function (Crawler $node, $i) use ($base_url) {
        $img = $node->filter('img')->first();
        return [
          'id' => $i,
          'media' => $img->eq(0)->attr('longdesc'),
          'title' => $img->eq(0)->attr('alt'),
          'url' => $base_url . $node->attr('href'),
        ];
      });

    // Return items and metadata.
    return array_merge($endpoint, [
      'base_url' => $this->config['base_url'],
      'items' => $items,
    ]);
  }

  /**
   * Get an endpoint settings by id.
   *
   * @param string $id
   *   Id of the endpoint.
   *
   * @return array
   *   Associative array containing id, label, url.
   */
  public function getEndpoint($id) {
    foreach ($this->config['urls'] as $item) {
      if ($item['id'] === $id) {
        return $item;
      }
    }
    return [];
  }

  /**
   * Get an instance of Symfony Dom Crawler.
   *
   * @param string $html
   *   Dom to parse.
   *
   * @return \Symfony\Component\DomCrawler\Crawler
   *   Crawler instance.
   */
  protected function getCrawler($html) {
    return new Crawler($html);
  }

  /**
   * Get schema://hostname from a full url.
   *
   * @param string $url
   *   Full url.
   *
   * @return string
   *   The schema://hostname.
   */
  protected function getHost($url) {
    return parse_url($url, PHP_URL_SCHEME) . '://' . parse_url($url, PHP_URL_HOST);
  }

}
