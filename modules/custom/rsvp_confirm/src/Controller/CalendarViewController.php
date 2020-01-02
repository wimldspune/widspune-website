<?php

namespace Drupal\rsvp_confirm\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\time_field\Time;
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
   * @param \Drupal\node\NodeInterface $node
   * @param \Drupal\user\UserInterface $user
   *
   * @return string
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function getRsvpLinks($node, $user) {
    $options = [];
    $options['attributes']['class'][] = 'btn btn-rsvp';
    $options['attributes']['target'] = '_blank';
    $options['attributes']['title'] = 'Click to RSVP this event.';
    $links['rsvp'] = Link::fromTextAndUrl('RSVP', Url::fromRoute('rsvp_confirm.rsvp_conform', ['node' => $node->id()], $options))->toString();

    $cancel_class = 'disabled';
    if ($user->isAuthenticated()) {
      $confirmation = $this->entityTypeManager->getStorage('rsvp_confirm');
      $nodes = $confirmation->loadByProperties([
        'nid' => $node->id(),
        'uid' => $user->id()
      ]);

      if (count($nodes)) {
        $links['rsvp'] = '<span class="btn rsvp-confirmed">You have RSVPed!</span>';
        $cancel_class = '';
      }
    }
    if ($user->isAnonymous()) {
      $links['rsvp'] = Link::fromTextAndUrl('RSVP', Url::fromUserInput('/user/login?destination=/rsvp/confirm/' . $node->id(), $options))->toString();
      $cancel_class = 'disabled';
    }

    $cancel_options = [];
    $cancel_options['attributes']['class'][] = 'btn btn-rsvp';
    $cancel_options['attributes']['class'][] = $cancel_class;
    $cancel_options['attributes']['target'] = '_blank';
    $cancel_options['attributes']['title'] = 'Click to cancel this RSVPed event.';
    $links['cancel'] = Link::fromTextAndUrl('Cancel', Url::fromRoute('rsvp_confirm.rsvp_cancel', ['node' => $node->id()], $cancel_options))->toString();

    return implode('', $links);
  }

  /**
   * Builds the response.
   */
  public function build() {

    // Fetch the content.
    $query = $this->nodeStorage->getQuery();
    $query->condition('type', 'event');
    $query->condition('status', 1);
    $query->sort('field_event_date');

    $nids = $query->execute();
    $user = \Drupal::currentUser();

    $items = [];
    $markup_data = '';
    if (!empty($nids)) {
      $nodes = $this->nodeStorage->loadMultiple($nids);
      /** @var \Drupal\node\NodeInterface $node */
      foreach ($nodes as $node) {
        $id = $node->id();
        $title = $node->label();
        $node_link = $node->toLink($title, 'canonical', ['attributes'=>['target' => '_blank']])->toString();

        $date_formatted = 'TBD';
        if (!$node->get('field_event_date')->isEmpty()) {
          $date_formatted = $node->field_event_date->date->format('jS M Y');
        }

        $timings = 'TBD';
        if (!$node->get('field_event_time')->isEmpty()) {
          $time_data = $node->get('field_event_time')->first()->getValue();
          $from = Time::createFromTimestamp($time_data['from']);
          $to = Time::createFromTimestamp($time_data['to']);
          $timings = $from->format('h:i a') . ' to ' . $to->format('h:i a');
        }

        $link = 'TBD';
        if (!$node->get('field_venue_address_link')->isEmpty()) {
          $link_data = $node->get('field_venue_address_link')->first()
            ->getValue();
          $uri = $link_data['uri'];
          $title = $link_data['title'];
          $option['attributes'] = ['target' => '_blank'];
          $link = Link::fromTextAndUrl($title, Url::fromUri($uri, $option))->toString();
        }

        $rsvp_links = $this->getRsvpLinks($node, $user);

        $markup = <<<str
<div class="calendar-info">
  <div class="calendar-data">
    <h4><span>Title of the event :</span> {$node_link} </h4>
    <p><strong>Date :</strong> {$date_formatted} </p>
    <p><strong>Time :</strong> {$timings} </p>
    <p><strong>Venue :</strong> {$link}  </p>
    <div class='rsvp-links'>
      {$rsvp_links}
    </div>
  </div>
</div>
str;
        $markup_data .= $markup;
      }
    }

    $markup = '<h1 class="page-header text-center">WiDS Pune 2020 Calendar</h1>';
    $markup .= $markup_data;

    $build['item_list'] = [
      '#type' => 'container',
      '#markup' => $markup,
      '#wrapper_attributes' => [
        'class' => [
          'wrapper',
          'col-lg-12',
        ],
      ],
      '#attributes' => [
        'class' => [
          'wrapper__links',
          'wids-calendar',
        ],
      ],
    ];

    return $build;
  }

}
