<?php

namespace Drupal\rsvp_confirm\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\Context\ContextProviderInterface;
use Drupal\node\NodeInterface;
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
  protected $userCurrentUser;

  /**
   * The node.node_route_context service.
   *
   * @var \Drupal\Core\Plugin\Context\ContextProviderInterface
   */
  protected $nodeNodeRoute;

  /**
   * The node storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nodeStorage;

  /**
   * The entity storage for users.
   *
   * @var \Drupal\user\UserStorageInterface
   */
  protected $userStorage;

  /**
   * @var \Drupal\Core\Session\AccountInterface
   */
  private $user;

  /**
   * @var \Drupal\node\NodeInterface
   */
  private  $nodeContent;

  /**
   * The controller constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
    $this->nodeStorage = $entity_type_manager->getStorage('node');
    $this->userStorage = $entity_type_manager->getStorage('user');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  private function rsvpExists() {
    $confirmation = $this->entityTypeManager->getStorage('rsvp_confirm');
    $nodes = $confirmation->loadByProperties([
      'nid' => $this->nodeContent->id(),
      'uid' => $this->user->id()
    ]);

    return count($nodes);
  }

  /**
   * Builds the response.
   *
   * @param \Drupal\node\NodeInterface|null $node
   *
   * @return mixed
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function confirm(NodeInterface $node = NULL) {
    $this->user = \Drupal::currentUser();
    $this->nodeContent = $node;
    $build = [];

    $markup = $this->t('<h2>You have already RSVPed</h2><p>We look forward to seeing you soon for this important event!</p>');
    if (!$this->rsvpExists()) {
      // Create RSVP confirmation relation.
      $confirmation = $this->entityTypeManager->getStorage('rsvp_confirm')->create([
        'uid' => $this->user->id(),
        'nid' => $this->nodeContent->id(),
      ]);

      // Save RSVP confirmation.
      $confirmation->save();

      $markup = <<<str
<h2>Thank you!</h2>

<p>We have received your RSVP request.
<br>
We will get back to you with confirmation soon.!</p>
str;
      $email_text = strip_tags($markup);
      $this->sendEmail('rsvp_confirm', $email_text);
    }

    $build['content'] = [
      '#type' => 'item',
      '#prefix' => '<div class="container"><div class="row"><div class="">',
      '#suffix' => '</div></div></div>',
      '#markup' => $markup,
    ];

    return $build;
  }

  private function sendEmail($key ,$message) {
    /** @var \Drupal\Core\Mail\MailManager $mailManager */
    $mailManager = \Drupal::service('plugin.manager.mail');
    $to = $this->user->getEmail();
    $params['message'] = $message;
    $params['node_title'] = $this->nodeContent->label();
    $language = $this->user->getPreferredLangcode();
    $result = $mailManager->mail('rsvp_confirm', $key, $to, $language, $params);
    $messenger = \Drupal::messenger();
    if ($result['result'] !== true) {
      $messenger->addError(t('There was a problem sending your message and it was not sent.'));
    }
    else {
      $messenger->addMessage(t('RSVP Email sent to @email.', ['@email' => $to]));
    }
  }

  private function getRsvp() {
    $confirmation = $this->entityTypeManager->getStorage('rsvp_confirm');
    return $confirmation->loadByProperties([
      'nid' => $this->nodeContent->id(),
      'uid' => $this->user->id()
    ]);
  }

  /**
   * Builds the response.
   *
   * @param \Drupal\node\NodeInterface|null $node
   *
   * @return mixed
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function cancel(NodeInterface $node = NULL) {
    $this->user = \Drupal::currentUser();
    $this->nodeContent = $node;
    $build = [];
    if ($confirmations = $this->getRsvp()) {
      foreach ($confirmations as $confirmation) {
        // Delete RSVP confirmation.
        $confirmation->delete();
      }
      $build['content'] = [
        '#type' => 'item',
        '#markup' => $this->t('It works!'),
      ];
      $markup = <<<str
<h2>You have cancelled the RSVP.</h2>

<p>You have successfully cancelled the RSVP.</p>
str;
      $email_text = strip_tags($markup);
      $this->sendEmail('rsvp_cancel', $email_text);
    }
    else {
      $markup = 'You have not RSVPed this event.';
    }

    $build['content'] = [
      '#type' => 'item',
      '#prefix' => '<div class="container"><div class="row"><div class="">',
      '#suffix' => '</div></div></div>',
      '#markup' => $markup,
    ];

    return $build;
  }

}
