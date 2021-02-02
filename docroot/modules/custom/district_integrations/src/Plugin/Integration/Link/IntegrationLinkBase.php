<?php

namespace Drupal\district_integrations\Plugin\Integration\Link;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;
use Drupal\district_integrations\Plugin\Integration\IntegrationBase;
use Drupal\district_integrations\Plugin\Integration\IntegrationLinksTrait;

/**
 * Provides IntegrationLinkBase Plugin.
 *
 * @package Drupal\district_integrations\Plugin\Integration\Link
 */
abstract class IntegrationLinkBase extends IntegrationBase implements IntegrationLinkInterface {

  use IntegrationLinksTrait;

  /**
   * Return the settings form for this plugin.
   *
   * @param array $form
   *   Form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   *
   * @return array
   *   Form array.
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    return $form;
  }

  /**
   * Get the prefix for settings table list of links/urls.
   *
   * @return array
   *   Renderable array.
   */
  public function getLinksSettingsTablePrefix() {
    $config = $this->getConfiguration();
    return [
      '#type' => 'container',
      'heading' => ['#markup' => '<h2>Links</h2>'],
      'description' => ['#markup' => '<p>Links can be referenced in a WYSIWYG via a token [district_link:' . $config['id'] . '_ID]</p>'],
    ];
  }

  /**
   * Render the link.
   *
   * @param array $link
   *   A single link, expecting keys for id, label, url.
   *
   * @return string
   *   A rendered anchor tag.
   */
  public function render(array $link) {
    try {
      $link = [
        '#type' => 'link',
        '#title' => $link['label'],
        '#url' => Url::fromUri($link['url']),
        '#attributes' => [
          'class' => [
            'integration-link',
            'integration-link--' . $link['id'],
          ],
          'target' => '_blank',
        ],
      ];
      $link_markup = drupal_render($link);
      return Markup::create($link_markup);
    }
    catch (Exception $e) {
      // @!todo Log error.
    }
  }

}
