<?php

namespace Drupal\rsvp_confirm\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\rsvp_confirm\RsvpServiceConfirmation;

/**
 * Returns responses for RSVP confirm routes.
 */
class CalendarViewController extends ControllerBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity type manager.
   *
   * @var \Drupal\rsvp_confirm\RsvpServiceConfirmation
   */
  protected $rsvpConfirmation;

  /**
   * The node storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nodeStorage;

  /**
   * The controller constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\rsvp_confirm\RsvpServiceConfirmation $rsvp_confirmation
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager,
                              RsvpServiceConfirmation $rsvp_confirmation) {
    $this->entityTypeManager = $entity_type_manager;
    $this->rsvpConfirmation = $rsvp_confirmation;
    $this->nodeStorage = $entity_type_manager->getStorage('node');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('rsvp_confirm.confirm')
    );
  }

  /**
   * Builds the response.
   */
  public function build() {

    // Fetch the content.
    $query = $this->nodeStorage->getQuery();
    $query->condition('type', 'article');
    $query->condition('status', 1);

    $result = $query->execute();
    $nids = $query->execute();

    $items = [];
    if (!empty($nids)) {
      $nodes = $this->nodeStorage->loadMultiple($nids);
      foreach ($nodes as $node) {
        $markup = '
        <div class="col-lg-4 col-md-4 col-sm-4">
				 <div class="volunteers-info ">
					<div class="caption  ">
						<h4>Title : ' .  $node->getTitle() . '</h4>
						<p>Venue :  </p>
						<p>Date : 25th Jan 2020</p>
						<p>Time  : </p>
						<p>&nbsp;</p>
					</div>
				</div>
			 </div>
        ';
        $item['#markup'] = $markup;
        $items[] = $item;
      }
    }

    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('It works!'),
    ];

    $build['item_list'] = [
      '#theme' => 'item_list',
      '#list_type' => 'ul',
      '#wrapper_attributes' => [
        'class' => [
          'wrapper',
        ],
      ],
      '#attributes' => [
        'class' => [
          'wrapper__links',
        ],
      ],
      '#items' => $items,
    ];

    return $build;
  }

}
