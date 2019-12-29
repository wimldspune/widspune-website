<?php

namespace Drupal\rsvp_confirm;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\Context\ContextProviderInterface;

/**
 * RsvpServiceConfirmation service.
 */
class RsvpServiceConfirmation {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The logger channel factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $logger;

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
   * Constructs a RsvpServiceConfirmation object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger
   *   The logger channel factory.
   * @param \Drupal\Core\Plugin\Context\ContextProviderInterface $user_current_user_context
   *   The user.current_user_context service.
   * @param \Drupal\Core\Plugin\Context\ContextProviderInterface $node_node_route_context
   *   The node.node_route_context service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, LoggerChannelFactoryInterface $logger, ContextProviderInterface $user_current_user_context, ContextProviderInterface $node_node_route_context) {
    $this->entityTypeManager = $entity_type_manager;
    $this->logger = $logger;
    $this->userCurrentUserContext = $user_current_user_context;
    $this->nodeNodeRouteContext = $node_node_route_context;
  }

  /**
   * Method description.
   */
  public function doSomething() {
    // @DCG place your code here.
  }

}
