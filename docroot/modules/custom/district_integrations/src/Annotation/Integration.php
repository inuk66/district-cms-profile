<?php

namespace Drupal\district_integrations\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Integration annotation object.
 *
 * Plugin Namespace: Plugin\Integration.
 *
 * @see \Drupal\district_integrations\Plugin\IntegrationInterface
 * @see \Drupal\district_integrations\Plugin\IntegrationManager
 *
 * @Annotation
 */
class Integration extends Plugin {

  /**
   * The unique identifier.
   *
   * @var string
   */
  public $id;

  /**
   * The label.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

  /**
   * The group.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $group;

}
