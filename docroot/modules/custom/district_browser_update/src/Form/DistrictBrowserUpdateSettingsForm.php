<?php

namespace Drupal\district_browser_update\Form;

use Drupal\Core\Config\ConfigBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\CachedDiscoveryClearerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure District Browser Update settings for this site.
 */
class DistrictBrowserUpdateSettingsForm extends ConfigFormBase {

  /**
   * A plugin cache clear instance.
   *
   * @var \Drupal\Core\Plugin\CachedDiscoveryClearerInterface
   */
  protected $pluginCacheClearer;

  /**
   * DistrictBrowserUpdateSettingsForm constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Plugin\CachedDiscoveryClearerInterface $plugin_cache_clearer
   *   A plugin cache clear instance.
   */
  public function __construct(ConfigFactoryInterface $config_factory, CachedDiscoveryClearerInterface $plugin_cache_clearer) {
    parent::__construct($config_factory);

    $this->pluginCacheClearer = $plugin_cache_clearer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('plugin.cache_clearer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'district_browser_update_district_browser_update_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['district_browser_update.settings'];
  }

  /**
   * Get a list of supported non-IE browsers.
   *
   * @return array
   *   The list of supported non-IE browsers.
   */
  private function getSupportedNonIeBrowsers() {
    return [
      'opera' => $this->t('Opera'),
      'safari' => $this->t('Safari'),
      'firefox' => $this->t('Firefox'),
      'chrome' => $this->t('Chrome'),
    ];
  }

  /**
   * Build 'Browser Update' browser options.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param \Drupal\Core\Config\ConfigBase $settings
   *   The saved settings.
   *
   * @return array
   *   The form structure.
   *
   * @url https://browser-update.org/
   */
  private function buildFormBrowserOptions(
    array $form,
    FormStateInterface $form_state,
    ConfigBase $settings
  ) {
    $browser_fieldset = 'browser_versions';

    $form[$browser_fieldset] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Required browser versions'),
    ];

    $form[$browser_fieldset]['description'] = [
      '#type' => 'markup',
      '#markup' => $this->t("Select the oldest browser versions that will not trigger 'Browser Update' notification."),
    ];

    $browser_versions = [
      0 => $this->t('Latest version'),
      -1 => $this->t('1 version behind'),
      -2 => $this->t('2 versions behind'),
      -3 => $this->t('3 versions behind'),
      -4 => $this->t('4 versions behind'),
    ];

    $supported_browsers = $this->getSupportedNonIeBrowsers();

    foreach ($supported_browsers as $browser => $label) {
      $key = $browser . '_req_version';

      $form[$browser_fieldset][$key] = [
        '#type' => 'select',
        '#title' => $label,
        '#options' => $browser_versions,
        '#default_value' => $settings->get($key) ?? -3,
      ];
    }

    $edge_ie_browser_versions = $browser_versions + [
      11 => $this->t('11'),
      10 => $this->t('10'),
    ];

    $form[$browser_fieldset]['ie_edge_req_version'] = [
      '#type' => 'select',
      '#title' => $this->t('Edge/IE'),
      '#options' => $edge_ie_browser_versions,
      '#default_value' => $settings->get('ie_edge_req_version') ?? 11,
    ];

    $form[$browser_fieldset]['insecure'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Notify all browser versions with severe security issues.'),
      '#default_value' => $settings->get('insecure') ?? FALSE,
    ];

    return $form;
  }

  /**
   * Build 'Browser Update' customisation options.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param \Drupal\Core\Config\ConfigBase $settings
   *   The saved settings.
   *
   * @return array
   *   The form structure.
   *
   * @url https://browser-update.org/customize.html
   */
  private function buildFormCustomisationOptions(
    array $form,
    FormStateInterface $form_state,
    ConfigBase $settings
  ) {
    $options_fieldset = 'customisation_options';

    $form[$options_fieldset] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Other options'),
    ];

    $form[$options_fieldset]['reminder'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Reminder Frequency'),
      '#description' => $this->t('After how many hours should the message reappear. 0 = show all the time.'),
      '#default_value' => $settings->get('reminder') ?? 24,
    ];

    $form[$options_fieldset]['reminder_closed'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Reminder Frequency - explicitly closed'),
      '#description' => $this->t('If the user explicitly closes the message, it reappears after x hours.'),
      '#default_value' => $settings->get('reminder_closed') ?? 150,
    ];

    $form[$options_fieldset]['no_close'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Hide ignore button.'),
      '#description' => $this->t('Check to hide the "ignore" button to close the notification.'),
      '#default_value' => $settings->get('no_close') ?? FALSE,
    ];

    $form[$options_fieldset]['no_permanent_hide'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Disable permanent hide.'),
      '#description' => $this->t('Check to not allow users to permanently hide the notification.'),
      '#default_value' => $settings->get('no_permanent_hide') ?? FALSE,
    ];

    $form[$options_fieldset]['no_statistics'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Disable sending anonymous statistics.'),
      '#description' => $this->t('For every 1000th visitor anonymous statistics on the used browser are collected. Check to disable sending of anonymous statistics.'),
      '#default_value' => $settings->get('no_statistics') ?? FALSE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $settings = $this->config('district_browser_update.settings');

    $form = $this->buildFormBrowserOptions($form, $form_state, $settings);
    $form = $this->buildFormCustomisationOptions($form, $form_state, $settings);

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $int_fields = [
      'reminder' => $this->t('Reminder Frequency'),
      'reminder_closed' => $this->t('Reminder Frequency - explicitly closed'),
    ];

    // Validate a list of numeric fields.
    foreach ($int_fields as $field => $field_label) {
      $field_value = $form_state->getValue($field) ?? '';

      if (!is_numeric($field_value) || $field_value < 0) {
        $form_state->setErrorByName(
          $field,
          $this->t(
            "Invalid '<em>@field_label</em>' value.",
            ['@field_label' => $field_label]
          )
        );
      }
    }

    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $settings = $this->config('district_browser_update.settings');
    $supported_browsers = array_keys($this->getSupportedNonIeBrowsers());
    $supported_browsers[] = 'ie_edge';

    $values = $form_state->getValues();

    foreach ($supported_browsers as $browser) {
      $key = $browser . '_req_version';

      $settings->set($key, $values[$key]);
    }

    $browser_update_options = [
      'insecure',
      'reminder',
      'reminder_closed',
      'no_close',
      'no_permanent_hide',
      'no_statistics',
    ];

    foreach ($browser_update_options as $option) {
      $settings->set($option, $values[$option]);
    }

    $settings->save();

    parent::submitForm($form, $form_state);

    // Clear the 'plugin' cache so that changes are immediately
    // applied on the front-end.
    $this->pluginCacheClearer->clearCachedDefinitions();
  }

}
