<?php

namespace Drupal\district_integrations_atdw;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\district_integrations\BaseApiClient;
use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;
use Drupal\district_integrations\Traits\IntegrationHelperTrait;

/**
 * Class SpydusService.
 *
 * @package Drupal\district_integrations_spydus
 */
class AtdwApiService extends BaseApiClient {

  use IntegrationHelperTrait;

  /**
   * Cache key for storage.
   *
   * @var string
   */
  protected $cacheKey = 'di_atdw';

  /**
   * Should the following requests be cached.
   *
   * Cache is handled by this class after parsing rather than BaseApiClient.
   *
   * @var bool
   */
  protected $cacheData = FALSE;

  /**
   * Default timezone for the site, used with dates.
   *
   * @var string
   */
  protected $timeZone;

  /**
   * Default fields to get on lookup.
   *
   * @var array
   */
  protected $defaultFieldList = [
    'productId', 'status', 'product_name', 'product_description', 'product_image',
    'address', 'boundary', 'product_update_date', 'product_classifications',
    'product_category', 'start_date', 'end_date', 'rate_from', 'rate_to',
    'comms_ph', 'comms_em', 'comms_url', 'comms_burl',
  ];

  /**
   * Filter to apply to body text.
   *
   * @var string
   */
  protected $bodyFilter = 'restricted_html';

  /**
   * Product categories.
   *
   * Populated by categories endpoint.
   *
   * @var array
   */
  protected $categories = [];

  /**
   * Product classifications.
   *
   * Populated by classifications endpoint.
   *
   * @var array
   */
  protected $classifications = [];

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
    $this->config = district_integrations_plugin_config('api', 'atdw');
    $this->timeZone = $this->configFactory->get('system.date')->get('timezone.default');
  }

  /**
   * Get a list of products.
   *
   * @param array $params
   *   Params overrides in the url query.
   * @param bool $fully_load
   *   Should an additional lookup be done to get all product data.
   *
   * @return array
   *   Response from API, products are contained in the "products" property.
   */
  public function getProducts(array $params = [], $fully_load = FALSE) {
    $url = $this->getProductsEndpointUrl($params);
    $cid = $this->getCacheKey($url);

    // Attempt to get from cache first.
    if ($cache_bin = $this->cacheDefault->get($cid)) {
      return $cache_bin->data;
    }

    // Remote lookup.
    $this->getCategories();
    try {
      $data = $this->get($url);

      if (!empty($data['products'])) {
        foreach ($data['products'] as &$product) {
          $product = $this->parseProduct($product, $fully_load);
        }
      }

      $this->cacheDefault->set($cid, $data, $this->getCacheExpiry());
      return $data;
    }
    catch (Exception $e) {
      // @todo Log error.
    }

    return [];
  }

  /**
   * Parse a single product.
   *
   * @param array $product
   *   A product row.
   * @param bool $fully_load
   *   Should this product be fully loaded, involves another API request.
   *
   * @return mixed
   *   Parsed product.
   */
  public function parseProduct(array $product, $fully_load = FALSE) {
    // Fetch all product data.
    if ($fully_load) {
      $product['product'] = $this->get(
        $this->getProductEndpointUrl($product['productId'])
      );
    }

    // Default size for image.
    $product['productImage'] = strtok($product['productImage'], '?');
    // Apply formatting to body.
    $product['productDescription'] =
      check_markup($product['productDescription'], $this->bodyFilter);
    // Make first paragraph lead.
    $product['productDescription'] = preg_replace('/^<p>/', '<p class="lead">', $product['productDescription']);
    // Product category description.
    $product['productCategoryDescription'] =
      $this->categories[$product['productCategoryId']] ?? '';
    // Ensure urls.
    $product['comms_url'] = $this->ensureExternalUrl($product['comms_url']);
    $product['comms_burl'] = $this->ensureExternalUrl($product['comms_burl']);
    // Format dates.
    $product['eventDates'] = $this->formatEventDates($product['eventDates']);
    // Classifications.
    $classifications = $this->getClassifications($product['productCategoryId']);
    foreach ($product['productClassifications'] as $classification_code) {
      $product['classifications'] = $classifications[$classification_code];
    }

    return $product;
  }

  /**
   * Get all images from product list.
   *
   * @param array $params
   *   Params overrides in the url query.
   *
   * @return array
   *   Array of images.
   */
  public function getProductImages(array $params = []) {
    $products = $this->getProducts($params);
    $out = [];
    $all_media = [];

    // First parse, reduce to array keyed by media id with values
    // being different sizes of that media.
    foreach ($products['products'] as $product) {
      if (!empty($product['product']['multimedia'])) {
        foreach ($product['product']['multimedia'] as $media) {
          if ($media['attributeIdMultimediaContent'] === 'IMAGE') {
            $all_media[$media['multimediaId']][] = $media;
          }
        }
      }
    }

    // Get the largest media for each media item.
    foreach ($all_media as $media_sizes) {
      $out[] = $this->getMaxArrayValue($media_sizes, 'width');
    }

    return [
      'products' => $out,
    ];
  }

  /**
   * Get product categories.
   *
   * @return array
   *   Array of categories, keyed by id, value is human name.
   */
  public function getCategories() {
    // Is in memory.
    if (!empty($this->categories)) {
      return $this->categories;
    }

    $url = $this->getEndpointUrl('categories');
    $cid = $this->getCacheKey($url);

    // Attempt to get from cache first.
    if ($cache_bin = $this->cacheDefault->get($cid)) {
      $this->categories = $cache_bin->data;
    }
    else {
      // Remote lookup.
      $cats = $this->get($url);
      foreach ($cats as $cat) {
        $this->categories[$cat['CategoryId']] = $cat['Description'];
      }
      $this->cacheDefault->set($cid, $this->categories, $this->getCacheExpiry());
    }

    return $this->categories;
  }

  /**
   * Get product classifications.
   *
   * @param string $type
   *   The type of product. eg EVENT, ACCOM, etc.
   *
   * @return array
   *   Array of categories, keyed by id, value is human name.
   */
  public function getClassifications($type) {
    // Is in memory.
    if (!empty($this->classifications[$type])) {
      return $this->classifications[$type];
    }

    $url = $this->getEndpointUrl('producttypes', ['cats' => $type]);
    $cid = $this->getCacheKey($url);

    // Attempt to get from cache first.
    if ($cache_bin = $this->cacheDefault->get($cid)) {
      $this->classifications[$type] = $cache_bin->data;
    }
    else {
      // Remote lookup.
      $cats = $this->get($url);
      foreach ($cats as $cat) {
        $this->classifications[$type][$cat['ProductTypeId']] = $cat['Description'];
      }
      $this->cacheDefault->set($cid, $this->classifications[$type], $this->getCacheExpiry());
    }

    return $this->classifications[$type];
  }

  /**
   * Get endpoint for a list of products.
   *
   * @param array $params
   *   Params overrides in the url query.
   *
   * @return string
   *   The full url to call.
   */
  public function getProductsEndpointUrl(array $params = []) {
    $cats = str_replace("\r\n", PHP_EOL, $this->config['categories']);

    $params = array_merge([
      'latlong' => implode(',', array_values($this->config['coordinates'])),
      'dist' => $this->config['distance'],
      'cats' => implode(',', explode(PHP_EOL, $cats)),
      'pge' => 1,
      'size' => $this->config['result_count'],
      'fl' => implode(',', $this->defaultFieldList),
      'order' => 'product_update_date',
    ], $params);

    return $this->getEndpointUrl('products', $params);
  }

  /**
   * Get a single product url.
   *
   * @param string $product_id
   *   The product id.
   *
   * @return string
   *   Url to that product.
   */
  public function getProductEndpointUrl($product_id) {
    return $this->getEndpointUrl('product', [
      'productId' => $product_id,
    ]);
  }

  /**
   * Get an endpoint Url.
   *
   * @param string $endpoint
   *   The endpoint.
   * @param array $params
   *   Params overrides in the url query.
   *
   * @return string
   *   Full url to endpoint.
   */
  public function getEndpointUrl($endpoint, array $params = []) {
    $url = trim($this->config['api_url'], '/') . '/' . $endpoint;

    $params = array_merge([
      'key' => $this->config['api_key'],
      'out' => 'json',
    ], $params);

    return urldecode($url . '?' . http_build_query($params));
  }

  /**
   * Override the parent get method with parsing output.
   *
   * @param string $url
   *   Url to get.
   * @param bool $json
   *   Should output be parsed as JSON.
   *
   * @return array|mixed|null
   *   Parsed response.
   */
  public function get($url, $json = FALSE) {
    $data = parent::get($url, FALSE);
    return $this->decodeResponse($data);
  }

  /**
   * Decode a response from ATDW API.
   *
   * @param string $body
   *   Raw response.
   *
   * @return array|null
   *   Decoded response.
   */
  protected function decodeResponse($body) {
    return json_decode(mb_convert_encoding($body, 'UTF-8', 'UTF-16LE'), TRUE);
  }

  /**
   * Get the array item with the highest value assigned to a key.
   *
   * @param array $array
   *   Array to test.
   * @param string $key
   *   Property of the item that gets evaluated.
   *
   * @return array
   *   The item from $array with the highest $key value.
   */
  protected function getMaxArrayValue(array $array, $key) {
    uasort($array, function ($a, $b) use ($key) {
      return $a[$key] > $b[$key] ? -1 : 1;
    });

    return reset($array);
  }

  /**
   * Format event dates to display as all day events.
   *
   * @todo This may need updating and an additional API request to support
   * other than all day events.
   *
   * @param array|null $dates
   *   Array of dates from the API.
   *
   * @return array
   *   Array of dates, each with `value` and `end_value`. End value is the start
   *   value @ 23:59 which indicates an all day event in smart_date.
   */
  protected function formatEventDates($dates) {
    if (empty($dates)) {
      return [];
    }
    $out = [];

    // All day duration in minutes.
    $all_day_duration = 1439;

    // Build parsed dates ready for saving to smart date field.
    foreach ($dates as $date) {
      // Convert date format YYYY-mm-dd 00:00 into epoch unix timestamp.
      $date = $date . ' 00:00';
      $time = DrupalDateTime::createFromFormat('Y-m-d H:i', $date, $this->timeZone)->getTimestamp();
      // Output.
      $out[] = [
        'value' => $time,
        'end_value' => $time + ($all_day_duration * 60),
        'timezone' => '',
        'duration' => $all_day_duration,
      ];
    }

    return $out;
  }

}
