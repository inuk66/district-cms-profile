<?php

namespace Drupal\district_integrations_instagram\Plugin\Integration\Api;

use Drupal\Core\Form\FormStateInterface;
use Drupal\district_integrations\Plugin\Integration\Api\IntegrationApiBase;
use Drupal\district_integrations\Plugin\Integration\IntegrationLinksTrait;

/**
 * Plugin implementation of the 'instagram' integration type.
 *
 * @Integration(
 *   id = "instagram",
 *   label = @Translation("Instagram"),
 *   group = "Social"
 * )
 */
class Instagram extends IntegrationApiBase {

  use IntegrationLinksTrait;

  /**
   * The columns for the table.
   *
   * @var string[]
   */
  protected $keys = ['id', 'label', 'tag'];

  /**
   * The name of the rows.
   *
   * @var string
   */
  protected $collection = 'tags';

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'tags' => [],
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function isConfigured() {
    $config = $this->getConfiguration();
    return !empty($config['tags']);
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);
    $config = $this->getConfiguration();

    $form['tags_desc'] = [
      0 => ['#markup' => '<h4>Hash tags to get the images from</h4>'],
      1 => ['#markup' => '<p>Provide an id, title and an Instagram hashtag</p>'],
    ];
    $form['tags'] = $this->generateLinkTable($config['tags']);

    return $form;
  }

}
