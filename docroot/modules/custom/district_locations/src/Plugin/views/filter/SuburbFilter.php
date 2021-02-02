<?php

namespace Drupal\district_locations\Plugin\views\filter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\filter\StringFilter;

/**
 * Override output of an address:locality exposed filter.
 *
 * This shows a dropdown listing suburb terms instead of a text box, improving
 * user experience.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("suburb_filter")
 */
class SuburbFilter extends StringFilter {

  /**
   * {@inheritdoc}
   */
  protected function valueForm(&$form, FormStateInterface $form_state) {
    parent::valueForm($form, $form_state);

    $element = [
      '#type' => 'select',
      '#size' => 1,
      '#empty_value' => 'All',
      '#empty_option' => '- Any -',
      '#options' => district_locations_get_suburb_options(),
    ];

    $form['value'] = array_merge($form['value'], $element);
  }

}
