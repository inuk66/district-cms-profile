<?php

namespace Drupal\district_integrations_instagram\Element;

use Drupal\Core\Render\Element\RenderElement;

/**
 * Provides a PostList element.
 *
 * @RenderElement("instagram_post_list")
 */
class PostList extends RenderElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {

    // Defaults.
    return [
      '#theme' => 'district_integrations_instagram_post_list',
      '#posts' => [],
    ];
  }

}
