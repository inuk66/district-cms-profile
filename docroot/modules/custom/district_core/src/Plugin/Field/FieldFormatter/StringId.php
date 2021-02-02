<?php

namespace Drupal\district_core\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Return a number between 0 and "max_number" based on a string.
 *
 * This might be used to generate a class dynamically based on a text string.
 * An example usage is setting map marker colours based on the term name.
 *
 * @FieldFormatter(
 *   id = "string_id",
 *   label = @Translation("String ID"),
 *   field_types = {
 *     "string",
 *     "text",
 *   }
 * )
 */
class StringId extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return parent::defaultSettings() + [
      'max_number' => '50',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);

    $elements['description'] = [
      '#type' => 'markup',
      '#markup' => $this->t('Returns an "id" between 0 and "Max number" based on this string'),
    ];

    $elements['max_number'] = [
      '#title' => $this->t('Max number'),
      '#type' => 'number',
      '#default_value' => $this->getSetting('max_number'),
      '#description' => $this->t('The maximum number the id can be'),
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();
    $summary[] = $this->t('Max number: @max',
      ['@max' => $this->getSetting('max_number')]);
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode = NULL) {
    $elements = [];

    foreach ($items as $item) {
      $elements[] = [
        '#type' => 'markup',
        '#markup' => $this->stringToId($item->value),
      ];
    }

    return $elements;
  }

  /**
   * Convert string into an id between 0 and max.
   *
   * @param string $string
   *   String to process.
   *
   * @return int
   *   A number between 0 and max_number.
   */
  public function stringToId($string) {
    $number = abs(crc32($string));
    return $number % $this->getSetting('max_number');
  }

}
