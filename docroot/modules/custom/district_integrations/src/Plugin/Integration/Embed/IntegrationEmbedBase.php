<?php

namespace Drupal\district_integrations\Plugin\Integration\Embed;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;
use Drupal\district_integrations\Plugin\Integration\IntegrationBase;
use Drupal\district_integrations\Plugin\Integration\IntegrationLinksTrait;

/**
 * Provides a IntegrationEmbed base.
 *
 * @package Drupal\district_integrations\Plugin\Integration\Embed
 */
abstract class IntegrationEmbedBase extends IntegrationBase implements IntegrationEmbedInterface {

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
  public function getEmbedsSettingsTablePrefix() {
    $config = $this->getConfiguration();
    return [
      '#type' => 'container',
      'heading' => ['#markup' => '<h2>Embeds</h2>'],
      'description' => [
        '#markup' => '<p>Embeds can be referenced in a WYSIWYG via a token [district_embed:' . $config['id'] . '_ID]</p>',
      ],
    ];
  }

  /**
   * Render the embed iframe.
   *
   * @param array $link
   *   A single link, expecting keys for id, label, url.
   *
   * @return string
   *   A rendered a tag.
   *
   * @throws \Exception
   */
  public function render(array $link) {
    try {
      $config = $this->getConfiguration();
      $link = [
        '#type' => 'link',
        '#title' => $link['label'],
        '#url' => Url::fromRoute('district_integrations.embed_view', [
          'plugin_id' => $config['id'],
          'link_id' => $link['id'],
        ]),
        '#attributes' => [
          'class' => [
            'use-ajax',
            'integration-link',
            'integration-link--' . $link['id'],
          ],
          'data-dialog-options' => '{"width": 800}',
          'data-dialog-type' => 'modal',
        ],
      ];
      $link_markup = drupal_render($link);
      return Markup::create($link_markup);
    }
    catch (Exception $e) {
      // @todo Log error.
    }
  }

}
