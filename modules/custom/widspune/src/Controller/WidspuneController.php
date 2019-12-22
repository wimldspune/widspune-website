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

//    $entity_type_manager = \Drupal::service('entity_type.manager');
//    $user_storage = $entity_type_manager->getStorage('user');
//
//    $query = $user_storage->getQuery();
//    $query->condition('status', 1);
//    $query->condition('field_photo_with_frame', NULL, 'IS NULL');
//    $query->condition('roles', 'volunteer');
//    $query->sort('uid');
//    $pl_item_ids = $query->execute();
//
//    $users = $user_storage->loadMultiple($pl_item_ids);
//
//    foreach ($users as $user) {
//      $user->set('status', 0);
//      $user->save();
//    }

    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('It works!'),
    ];

    return $build;
  }

}
