<?php

namespace Drupal\district_integrations\Plugin\Integration\Link;

/**
 * Provides an interface defining a Integration Link plugin.
 */
interface IntegrationLinkInterface {

  /**
   * Render the link.
   *
   * @param array $link
   *   A single link, expecting keys for id, label, url.
   *
   * @return string
   *   A rendered anchor tag.
   */
  public function render(array $link);

}
