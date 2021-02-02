<?php

namespace Drupal\district_integrations\Traits;

/**
 * Misc helper functions for District Integrations.
 *
 * @package Drupal\district_integrations
 */
trait IntegrationHelperTrait {

  /**
   * Ensure an external url is complete with scheme.
   *
   * @param string $url
   *   The url to check.
   *
   * @return string
   *   Url with scheme.
   */
  protected function ensureExternalUrl($url) {
    if (empty($url)) {
      return $url;
    }
    if (empty(parse_url($url, PHP_URL_SCHEME))) {
      $url = 'http://' . $url;
    }
    return $url;
  }

}
