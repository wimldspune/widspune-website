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

    $user_storage = \Drupal::entityTypeManager()->getStorage('user');

    $query = $user_storage->getQuery();
//    $query->condition('status', 1);
    $query->condition('field_full_name', NULL, 'IS NULL');
    $query->condition('uid', [0,1], 'NOT IN');
    $query->sort('uid');
    $query->pager('5');
    $pl_item_ids = $query->execute();
    dsm(count($pl_item_ids));
    dsm(($pl_item_ids));

    $users = $user_storage->loadMultiple($pl_item_ids);
    /** @var \Drupal\user\UserInterface $user */
    foreach ($users as $user) {
      $user->set('field_full_name', $user->getDisplayName());
      $user->save();
    }

    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('It works!'),
    ];

    return $build;
  }

}
