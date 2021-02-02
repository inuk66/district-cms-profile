<?php

namespace Drupal\district_integrations\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Controler to provide embeds.
 *
 * @package Drupal\district_integrations\Controller
 */
class EmbedController extends ControllerBase {

  /**
   * Index page for all integrations.
   *
   * @param string $plugin_id
   *   Plugin id.
   * @param string $link_id
   *   Id for the specific link.
   *
   * @return array
   *   Renderable array.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function view($plugin_id, $link_id) {
    $link = $this->getLink($plugin_id, $link_id);

    if ($link) {
      $out = [
        '#type' => 'html_tag',
        '#tag' => 'iframe',
        '#attributes' => [
          'src' => $link['url'],
          'width' => '100%',
          'height' => '600px',
          'frameborder' => 0,
          'marginheight' => 0,
          'marginwidth' => 0,
        ],
        '#value' => '',
      ];

      return $out;
    }
    else {
      throw new NotFoundHttpException();
    }
  }

  /**
   * View page title.
   *
   * @param string $plugin_id
   *   Plugin id.
   * @param string $link_id
   *   Id for the specific link.
   *
   * @return string
   *   Title of the embed.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function viewTitle($plugin_id, $link_id) {
    $link = $this->getLink($plugin_id, $link_id);

    if ($link) {
      return htmlentities($link['label']);
    }
    else {
      return NULL;
    }
  }

  /**
   * Get link from plugin.
   *
   * @param string $plugin_id
   *   Plugin id.
   * @param string $link_id
   *   Id for the specific link.
   *
   * @return array|bool
   *   The link array, false if not found.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  protected function getLink($plugin_id, $link_id) {
    $plugin_manager = district_integrations_plugins('embed');
    $instance = $plugin_manager->createInstance($plugin_id);
    return $instance->getLinkById($link_id);
  }

}
