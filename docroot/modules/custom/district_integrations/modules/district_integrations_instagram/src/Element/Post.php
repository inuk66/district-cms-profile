<?php

namespace Drupal\district_integrations_instagram\Element;

use Drupal\Core\Render\Element\RenderElement;

/**
 * Provides a Post render element.
 *
 * @RenderElement("instagram_post")
 */
class Post extends RenderElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {

    // Defaults.
    return [
      '#theme' => 'district_integrations_instagram_post',
      '#tag' => '',
      '#title' => '',
      '#imageuri' => '',
      '#posturi' => '',
    ];
  }

}
