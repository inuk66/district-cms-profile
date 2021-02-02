<?php

namespace Drupal\district_integrations_instagram;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\district_integrations\BaseApiClient;
use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;

/**
 * Provides Instagram service class.
 *
 * @package Drupal\district_integrations_instagram
 */
class InstagramService extends BaseApiClient {

  /**
   * Cache key for storage.
   *
   * @var string
   */
  protected $cacheKey = 'di_instagram';

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
    $this->config = district_integrations_plugin_config('api', 'instagram');
  }

  /**
   * Get the parsed data from an endpoint by id.
   *
   * @param string $id
   *   Id of the endpoint.
   * @param int $limit
   *   The number of images to fetch.
   *
   * @return array
   *   Associative array containing id, label, hashtag and parsed items.
   */
  public function getData($id, $limit) {

    $endpoint = $this->getEndpoint($id);

    if (empty($endpoint['tag'])) {
      return [];
    }

    $url = 'https://instagram.com/explore/tags/' . $endpoint['tag'] . '?__a=1';

    // Scrape the remote url for raw data.
    $json = $this->get($url, TRUE);

    if (!$json) {
      $this->logger->warning('Could not fetch data from Instagram.');
    }

    // We have a graphql object, we can parse the media from it.
    $graphql = $json['graphql'];
    $items = [];

    if (!empty($graphql['hashtag'])) {
      $tagdata = $graphql['hashtag'];
      // We are going to get the thumbnails from the top posts.
      $medialist = $tagdata['edge_hashtag_to_top_posts']['edges'];
      foreach ($medialist as $media) {
        if (count($items) >= $limit) {
          break;
        }
        $node = $media['node'];

        $alt = $node['edge_media_to_caption']['edges'][0]['node']['text'];
        $src = $node['display_url'];
        $id = $node['id'];
        $shortcode = $node['shortcode'];

        // Strip HTML.
        $alt = strip_tags($alt);
        // Strip Hash references.
        $alt = preg_replace('/#[a-z0-9]+/i', ' ', $alt);

        $items[] = [
          'id' => $id,
          'title' => $alt,
          'uri' => $src,
          'tag' => $endpoint['tag'],
          'shortcode' => $shortcode,
        ];
      }

    }

    // Return items and metadata.
    return array_merge($endpoint, [
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
   *   Associative array containing id, label, account name.
   */
  public function getEndpoint($id) {
    foreach ($this->config['tags'] as $item) {
      if ($item['id'] === $id) {
        return $item;
      }
    }
    return [];
  }

}
