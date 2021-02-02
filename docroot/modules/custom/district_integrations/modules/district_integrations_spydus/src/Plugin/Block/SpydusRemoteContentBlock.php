<?php

namespace Drupal\district_integrations_spydus\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'SpydusRemoteContentBlock' block.
 *
 * @Block(
 *  id = "spydus_remote_content_block",
 *  admin_label = @Translation("Spydus remote content"),
 * )
 */
class SpydusRemoteContentBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Spydus service.
   *
   * @var \Drupal\district_integrations_spydus\SpydusService
   */
  protected $service;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static($configuration, $plugin_id, $plugin_definition);
    $instance->service = $container->get('district_integrations_spydus.service');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'type' => '',
      'limit' => 6,
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['type'] = [
      '#type' => 'select',
      '#title' => $this->t('Content type'),
      '#description' => $this->t('Select what Spydus content this block will show'),
      '#options' => [],
      '#empty_option' => $this->t('- Select -'),
      '#empty_value' => '',
      '#required' => TRUE,
      '#default_value' => $this->configuration['type'],
      '#size' => 1,
      '#weight' => '0',
    ];

    $config = $this->service->getConfig();
    foreach ($config['urls'] as $item) {
      $form['type']['#options'][$item['id']] = $item['label'];
    }

    $form['limit'] = [
      '#type' => 'number',
      '#title' => $this->t('Item limit'),
      '#default_value' => $this->configuration['limit'],
      '#description' => $this->t('Limit list of items displayed'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['type'] = $form_state->getValue('type');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    $base_class = 'spydus-remote-content';
    $build['wrapper'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          $base_class,
          'js-' . $base_class,
          $base_class . '--' . $this->configuration['type'],
        ],
        'data-limit' => $this->configuration['limit'],
        'data-src' => Url::fromRoute('district_integrations_spydus.json_data', [
          'id' => $this->configuration['type'],
        ])->toString(),
      ],
      'loading' => [
        '#markup' => '<div class="loader"><i></i> Loading...</div>',
      ],
    ];

    // Add JS.
    $build['#attached']['library'][] = 'district_integrations_spydus/spydus';

    return $build;
  }

}
