<?php

namespace Drupal\district_integrations\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormBuilder;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Defines integration settings.
 *
 * @package Drupal\district_integrations\Controller
 */
class IntegrationSettings extends ControllerBase {

  /**
   * Form builder.
   *
   * @var \Drupal\Core\Form\FormBuilder
   */
  protected $formBuilder;

  /**
   * {@inheritdoc}
   */
  public function __construct(FormBuilder $form_builder) {
    $this->formBuilder = $form_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('form_builder')
    );
  }

  /**
   * Index page for all integrations.
   *
   * @return array
   *   Renderable array.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function index() {
    $out = [];

    $out = array_merge($out, $this->buildIntegrationSection(
      'api', $this->t('Integrations responsible for connecting to external APIs')));
    $out = array_merge($out, $this->buildIntegrationSection(
      'embed', $this->t('Integrations responsible for embedding external content such as forms')));
    $out = array_merge($out, $this->buildIntegrationSection(
      'link', $this->t('Integrations responsible for managing external links')));

    return $out;
  }

  /**
   * Edit page.
   *
   * @param string $type
   *   Plugin type.
   * @param string $plugin_id
   *   Plugin id.
   *
   * @return array
   *   Renderable array.
   */
  public function edit($type, $plugin_id) {
    $form = $this->formBuilder
      ->getForm('Drupal\district_integrations\Form\IntegrationSettingsForm', $type, $plugin_id);

    return [
      'form' => $form,
    ];
  }

  /**
   * Edit page title.
   *
   * @param string $type
   *   Plugin type.
   * @param string $plugin_id
   *   Plugin id.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   Title of the edit page.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function editTitle($type, $plugin_id) {
    $plugin = district_integrations_plugins($type)->getDefinition($plugin_id);
    return $this->t('@label settings (@type integration)',
      ['@label' => $plugin['label'], '@type' => $type]);
  }

  /**
   * Build an integration table section.
   *
   * @param string $type
   *   Plugin type.
   * @param string $desc
   *   Description for the section.
   *
   * @return array
   *   Table renderable array.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  protected function buildIntegrationSection($type, $desc = '') {
    $integrations = district_integrations_plugins($type);

    $header['label'] = [
      'width' => '45%',
      'data' => $this->t('Name'),
    ];
    $header['machine_name'] = [
      'width' => '15%',
      'data' => $this->t('Machine name'),
    ];
    $header['group'] = [
      'width' => '15%',
      'data' => $this->t('Group'),
    ];
    $header['status'] = [
      'width' => '15%',
      'data' => $this->t('Configured'),
    ];
    $header['operations'] = [
      'width' => '10%',
      'data' => $this->t('Operations'),
    ];

    $table = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => [],
    ];

    foreach ($integrations->getDefinitions() as $plugin_id => $plugin) {
      $plugin_instance = $integrations->createInstance($plugin_id, ['of' => 'configuration values']);
      $edit_url = Url::fromRoute('district_integrations.settings_edit', [
        'type' => $type,
        'plugin_id' => $plugin['id'],
      ]);

      $row['label'] = Link::fromTextAndUrl($plugin['label'], $edit_url)->toString();
      $row['machine_name'] = $plugin['id'];
      $row['group'] = $plugin['group'];
      $row['status'] = $plugin_instance->isConfigured() ? 'yes' : 'no';

      $row['operations']['data'] = [
        '#type' => 'dropbutton',
        '#links' => [
          'simple_form' => [
            'title' => $this->t('Edit'),
            'url' => $edit_url,
          ],
        ],
      ];

      $table['#rows'][$plugin['id']] = $row;
    }

    return [
      $type => [
        'heading' => ['#markup' => '<h2>' . ucwords($type) . ' integrations</h2>'],
        'desc' => ['#markup' => '<p>' . $desc . '</p>'],
        'table' => $table,
        'spacer' => ['#markup' => '<br />'],
      ],
    ];
  }

}
