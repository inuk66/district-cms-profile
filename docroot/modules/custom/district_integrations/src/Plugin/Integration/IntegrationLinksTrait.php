<?php

namespace Drupal\district_integrations\Plugin\Integration;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a IntegrationLinks trait.
 *
 * @package Drupal\district_integrations\Plugin\Integration
 */
trait IntegrationLinksTrait {

  /**
   * How many blank rows to add to a link table.
   *
   * @var int
   */
  public $blankRows = 3;

  /**
   * Generate a table with an id, label and url for each row.
   *
   * @param array $data
   *   Array of urls to render in the link table.
   *
   * @return array
   *   Renderable array.
   */
  public function generateLinkTable(array $data) {
    $row_count = count($data) + $this->blankRows;
    $header = [];

    foreach ($this->keys as $key) {
      $header[$key] = ucwords($key);
    }

    $table = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => [],
    ];

    for ($i = 0; $i < $row_count; $i++) {
      foreach ($this->keys as $key) {
        $row[$key] = [
          '#type' => 'textfield',
          '#default_value' => $data[$i][$key] ?? '',
          '#maxlength' => 1024,
        ];
      }
      $table[$i] = $row;
    }

    return $table;
  }

  /**
   * Parse submitted settings form values.
   *
   * @param array $form
   *   Form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state as called in submitForm().
   */
  public function parseFormValues(array $form, FormStateInterface &$form_state) {
    // Ensure no empty "urls" rows are saved.
    $urls = $form_state->getValue($this->collection);
    $save_urls = [];

    if (!empty($urls)) {
      foreach ($urls as $url) {
        $is_empty = empty(implode('', array_values($url)));
        if (!$is_empty) {
          $save_urls[] = $url;
        }
      }
      $form_state->setValue($this->collection, $save_urls);
    }
  }

  /**
   * Get a url by its Id.
   *
   * @param string $id
   *   Id for the URL.
   *
   * @return array|bool
   *   Return link array if exists false if not.
   */
  public function getLinkById($id) {
    $config = $this->getConfiguration();
    foreach ($config[$this->collection] as $link) {
      if ($link['id'] === $id) {
        return $link;
      }
    }
    return FALSE;
  }

}
