<?php

namespace Drupal\district_integrations_instagram\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'InstagramRemoteContentBlock' block.
 *
 * @Block(
 *  id = "instagram_remote_content_block",
 *  admin_label = @Translation("Instagram remote content"),
 * )
 */
class InstagramRemoteContentBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Instagram service.
   *
   * @var \Drupal\district_integrations_instagram\InstagramService
   */
  protected $service;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static($configuration, $plugin_id, $plugin_definition);
    $instance->service = $container->get('district_integrations_instagram.service');
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
      '#title' => $this->t('Tag'),
      '#description' => $this->t('Select what Instagram tag this block will show'),
      '#options' => [],
      '#empty_option' => $this->t('- Select -'),
      '#empty_value' => '',
      '#required' => TRUE,
      '#default_value' => $this->configuration['type'],
      '#size' => 1,
      '#weight' => '0',
    ];

    $config = $this->service->getConfig();
    foreach ($config['tags'] as $item) {
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
    $this->configuration['limit'] = $form_state->getValue('limit');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    $data = $this->service->getData(
      $this->configuration['type'],
      $this->configuration['limit']
    );

    return $this->renderData($data);
  }

  /**
   * Return a render array from the data that was fetched.
   */
  public function renderData($data) {
    $build = [
      'post-list' => [
        '#type' => 'instagram_post_list',
        '#posts' => [],
      ],
    ];
    foreach ($data['items'] as $item) {
      $build['post-list']['#posts'][] = [
        '#type' => 'instagram_post',
        '#tag' => $item['tag'],
        '#title' => $item['title'],
        '#imageuri' => $item['uri'],
        '#posturi' => 'http://www.instagram.com/p/' . $item['shortcode'] . '/',
      ];
    }

    return $build;
  }

}
