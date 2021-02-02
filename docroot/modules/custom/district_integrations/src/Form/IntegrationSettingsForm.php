<?php

namespace Drupal\district_integrations\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Url;

/**
 * Implements an example form.
 */
class IntegrationSettingsForm extends FormBase {

  use MessengerTrait;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'integration_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $plugin_type = '', $plugin_id = '') {
    $plugin = $this->getPluginInstance($plugin_type, $plugin_id);
    $form_state->setStorage([
      'plugin_type' => $plugin_type,
      'plugin_id' => $plugin_id,
    ]);

    $form['actions']['#type']['#weight'] = 100;
    $form['actions']['#type'] = 'actions';

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
    ];

    $form['actions']['cancel'] = [
      '#title' => $this->t('Cancel'),
      '#type' => 'link',
      '#url' => Url::fromRoute('district_integrations.settings'),
      '#attributes' => ['class' => ['button']],
    ];

    // The plugin defines its own settings form.
    $form = $plugin->settingsForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Allow plugins to parse form values.
    $plugin_info = $form_state->getStorage();
    $plugin = $this->getPluginInstance($plugin_info['plugin_type'], $plugin_info['plugin_id']);

    if (method_exists($plugin, 'validateFormValues')) {
      $plugin->validateFormValues($form, $form_state);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $plugin_info = $form_state->getStorage();
    $plugin = $this->getPluginInstance($plugin_info['plugin_type'], $plugin_info['plugin_id']);
    $config = $plugin->getConfiguration();

    // Allow plugins to parse form values.
    if (method_exists($plugin, 'parseFormValues')) {
      $plugin->parseFormValues($form, $form_state);
    }

    // Save any value found in default config if value exists.
    foreach (array_keys($plugin->defaultConfiguration()) as $key) {
      $config[$key] = $form_state->getValue($key);
    }

    $plugin->setConfiguration($config);
    $this->messenger()->addMessage('Updated configuration');
  }

  /**
   * Get instance of the plugin class.
   *
   * @param string $plugin_type
   *   The plugin type.
   * @param string $plugin_id
   *   The plugin id.
   *
   * @return \Drupal\district_integrations\Plugin\IntegrationInterface
   *   Instance of the plugin.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  protected function getPluginInstance($plugin_type, $plugin_id) {
    $integrations = district_integrations_plugins($plugin_type);
    return $integrations->createInstance($plugin_id, ['of' => 'configuration values']);
  }

}
