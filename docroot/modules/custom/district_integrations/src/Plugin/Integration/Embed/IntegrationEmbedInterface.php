<?php

namespace Drupal\district_integrations\Plugin\Integration\Embed;

/**
 * Provides an interface defining a Integration Embed plugin.
 */
interface IntegrationEmbedInterface {

  /**
   * Render the Embed.
   *
   * @param array $link
   *   A single link, expecting keys for id, label, url.
   *
   * @return string
   *   A rendered iframe tag.
   */
  public function render(array $link);

}
