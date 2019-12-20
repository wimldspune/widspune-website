<?php

namespace Drupal\widspune\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns responses for WiDS Pune Core routes.
 */
class WidspuneController extends ControllerBase {

  /**
   * Builds the response.
   */
  public function build() {

    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('It works!'),
    ];

    return $build;
  }

}
