<?php

namespace Drupal\district_events\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provide DistrictEvents settings form.
 *
 * @package Drupal\district_events\Form
 */
class DistrictEventsSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'district_events_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'district_events.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('district_events.settings');

    $form['unpublish_expired_events'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Unpublish expired events'),
      '#default_value' => $config->get('unpublish_expired_events'),
      '#description' => $this->t(
        'Unpublish events that only have dates in the past. The cron will run once a day to check and unpublish expired events.'
      ),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $this->config('district_events.settings')
      ->set('unpublish_expired_events', $values['unpublish_expired_events'])
      ->save();

    parent::submitForm($form, $form_state);
  }

}
