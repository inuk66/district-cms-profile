<?php

namespace Drupal\district_locations\Plugin\Field\FieldFormatter;

use Drupal\address\AddressInterface;
use Drupal\address\Plugin\Field\FieldFormatter\AddressPlainFormatter;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'inline_address_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "inline_address_formatter",
 *   label = @Translation("Inline"),
 *   field_types = {
 *     "address"
 *   }
 * )
 */
class InlineAddressFormatter extends AddressPlainFormatter {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $elements[$delta] = ['#markup' => $this->viewElement($item, $langcode)];
    }

    return $elements;
  }

  /**
   * Builds a renderable array for a single address item.
   *
   * @param \Drupal\address\AddressInterface $address
   *   The address.
   * @param string $langcode
   *   The language that should be used to render the field.
   *
   * @return string
   *   The address as a string.
   */
  protected function viewElement(AddressInterface $address, $langcode) {
    $country_code = $address->getCountryCode();
    $address_format = $this->addressFormatRepository->get($country_code);
    $values = $this->getValues($address, $address_format);

    $state = !empty($values['administrativeArea']['code']) ?
      $values['administrativeArea']['code'] . ' ' : '';

    $parts = [
      $values['addressLine1'],
      $values['addressLine2'],
      $values['locality'],
      $state . ' ' . $values['postalCode'],
    ];

    $out = array_filter($parts, function ($var) {
      return !empty(trim($var));
    });

    return implode(', ', $out);
  }

}
