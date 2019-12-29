<?php

namespace Drupal\rsvp_confirm\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\Context\ContextProviderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for RSVP confirm routes.
 */
class RsvpConfirm extends ControllerBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The user.current_user_context service.
   *
   * @var \Drupal\Core\Plugin\Context\ContextProviderInterface
   */
  protected $userCurrentUserContext;

  /**
   * The node.node_route_context service.
   *
   * @var \Drupal\Core\Plugin\Context\ContextProviderInterface
   */
  protected $nodeNodeRouteContext;

  /**
   * The controller constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Plugin\Context\ContextProviderInterface $user_current_user_context
   *   The user.current_user_context service.
   * @param \Drupal\Core\Plugin\Context\ContextProviderInterface $node_node_route_context
   *   The node.node_route_context service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager,
                              ContextProviderInterface $user_current_user_context,
                              ContextProviderInterface $node_node_route_context) {
    $this->entityTypeManager = $entity_type_manager;
    $this->userCurrentUserContext = $user_current_user_context;
    $this->nodeNodeRouteContext = $node_node_route_context;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('user.current_user_context'),
      $container->get('node.node_route_context')
    );
  }

  /**
   * Builds the response.
   */
  public function conform() {

    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('It works!'),
    ];

    return $build;
  }

}
