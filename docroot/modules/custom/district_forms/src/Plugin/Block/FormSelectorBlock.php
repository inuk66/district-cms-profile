<?php

namespace Drupal\district_forms\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'FormSelectorBlock' block.
 *
 * @Block(
 *  id = "district_form_selector_block",
 *  admin_label = @Translation("Form selector block"),
 * )
 */
class FormSelectorBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * The FormBuilder object.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * Request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $stack;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static($configuration, $plugin_id, $plugin_definition);
    $instance->entityTypeManager = $container->get('entity_type.manager');
    $instance->formBuilder = $container->get('form_builder');
    $instance->stack = $container->get('request_stack');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'forms' => [],
      'empty_text' => 'Select a service',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['forms'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('What forms should be available'),
      '#description' => $this->t('Select which forms should be available'),
      '#options' => [],
      '#required' => TRUE,
      '#default_value' => $this->configuration['forms'],
    ];

    $form['empty_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Empty text'),
      '#description' => $this->t('What should display when no form is selected'),
      '#required' => TRUE,
      '#default_value' => $this->configuration['empty_text'],
    ];

    $webform_entity_manager = $this->entityTypeManager->getStorage('webform');
    foreach ($webform_entity_manager->loadByProperties([]) as $item) {
      $form['forms']['#options'][$item->id()] = $item->label();
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['forms'] = array_keys(array_filter($form_state->getValue('forms')));
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $webform_entity_manager = $this->entityTypeManager->getStorage('webform');
    $forms = $this->configuration['forms'];
    $query = $this->stack->getCurrentRequest()->query;

    // Is a form selected.
    $selected_form = $query->get('selected_form');
    if (!in_array($selected_form, $forms)) {
      $selected_form = '';
    }

    $form_entities = $webform_entity_manager->loadMultiple($forms);

    // Populate available options from config.
    $form_options = ['' => $this->configuration['empty_text']];
    foreach ($form_entities as $key => $form) {
      $form_options[$key] = $form->label();
    }

    // Provide a select box that allows user to choose the form.
    $build = [
      'selector' => [
        '#type' => 'select',
        '#options' => $form_options,
        '#value' => $selected_form,
        '#attributes' => [
          'class' => ['form-selector', 'js-form-selector'],
          'id' => 'form-selector',
        ],
      ],
    ];

    // If form provided, populate.
    if ($selected_form) {
      $form = $form_entities[$selected_form];
      $build['form'] = [
        '#type' => 'container',
        '#attrubutes' => [
          'class' => ['form-selector__form'],
        ],
        'title' => [
          '#markup' => '<h3>' . $form->label() . '</h3>',
        ],
        'content' => [
          '#type' => 'webform',
          '#webform' => $form,
        ],
      ];
    }

    $build['#cache'] = [
      'tags' => ['form_selector:' . $selected_form],
      'contexts' => [
        'url.query_args:selected_form',
      ],
    ];

    // Add JS.
    $build['#attached']['library'][] = 'district_forms/form_selector';

    return $build;
  }

}
