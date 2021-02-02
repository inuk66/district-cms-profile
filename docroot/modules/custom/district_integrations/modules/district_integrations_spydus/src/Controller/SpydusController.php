<?php

namespace Drupal\district_integrations_spydus\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Provide route endpoints for Spydus data.
 */
class SpydusController extends ControllerBase {

  /**
   * Drupal\district_integrations_spydus\SpydusService definition.
   *
   * @var \Drupal\district_integrations_spydus\SpydusService
   */
  protected $service;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->service = $container->get('district_integrations_spydus.service');
    return $instance;
  }

  /**
   * Get the data for an endpoint.
   *
   * @param string $id
   *   Enspoint/url id.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   JSON response containing data.
   */
  public function data($id) {
    $data = $this->service->getData($id);
    return new JsonResponse($data);
  }

}
