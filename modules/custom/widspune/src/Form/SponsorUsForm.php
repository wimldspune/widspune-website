<?php

namespace Drupal\widspune\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\taxonomy\Entity\Term;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Provides a WiDS Pune Core form.
 */
class SponsorUsForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'widspune_sponsor_us';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $type = NULL) {

    if (empty($type) || !in_array($type, ['sponsor', 'community-partner'])) {
      throw new NotFoundHttpException();
    }

    $form_title = '<h1 class="text-center page-header">';
    if($type == 'sponsor') {
      $form_title .= 'Become a Sponsor';
    }
    else {
      $form_title .= 'Become a Community-Partner';
    }
    $form_title .= '</h1>';

    $content = $form_title;
    $content .= '<div = class= "form-description">
            <p>The Women in Data Science (WiDS) initiative aims to inspire and educate data scientists worldwide, regardless of gender, and support women in the field.</p>
            <span class="form-required">Required Fields </span>
          </div>
          <hr />';

    $form['message'] = [
      '#type' => 'markup',
      '#markup' => $content,
    ];

    $required = TRUE;

    // Sponsoring Organisation
    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Sponsoring Organisation'),
      '#required' => $required,
      '#placeholder' => $this->t('e.g. WiDS Pune'),
    ];

    // Organisation Mailing Address
    $form['org_email'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Organisation Mailing Address'),
      '#required' => $required,
      '#placeholder' => $this->t('e.g. contact@domain.tld'),
    ];

    // Name of Representative
    $form['rep_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name of Representative'),
      '#required' => $required,
      '#placeholder' => $this->t('eg. John Doe'),
    ];

    // Representative E-mail
    $form['rep_email'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Representative E-mail'),
      '#required' => $required,
      '#placeholder' => $this->t('e.g. contact@domain.tld'),
    ];

    // Representative Phone No.
    $form['rep_phone'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Representative Phone No.'),
      '#required' => $required,
      '#placeholder' => $this->t('e.g. 9890989898'),
    ];

    $vid = 'promotion_types';
    $field_title = 'Would you promote our event amongst your community members through';
    if ($type == 'sponsor') {
      $vid = 'sponsorship_details';
      $field_title = 'In what form would you like to Sponsor';
    }
    $query = \Drupal::entityQuery('taxonomy_term');
    $query->condition('vid',  $vid);
    $tids = $query->execute();
    $terms = Term::loadMultiple($tids);
    foreach ($terms as $term) {
      $term_data[$term->id()] = $term->getName();
    }

    // In what form would you like to Sponsor
    $form['categories'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('@title', ['@title' => $field_title]),
      '#required' => $required,
      '#options' => $term_data,
    ];

    $form['type'] = [
      '#type' => 'hidden',
      '#value' => $type,
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $email_validator = \Drupal::service('email.validator');

    $org_email = $form_state->getValue('org_email');
    if ($org_email !== '' && !$email_validator->isValid($org_email)) {
      $form_state->setErrorByName('org_email', t('The email address %mail is not valid.', ['%mail' => $org_email]));
    }

    $rep_email = $form_state->getValue('rep_email');
    if ($rep_email !== '' && !$email_validator->isValid($org_email)) {
      $form_state->setErrorByName('rep_email', t('The email address %mail is not valid.', ['%mail' => $rep_email]));
    }
    $rep_phone = $form_state->getValue('rep_phone');
    if ($rep_phone !== '' && !is_numeric($rep_phone)) {
      $form_state->setErrorByName('rep_phone', t('The phone number %phone is not valid.', ['%phone' => $rep_phone]));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->cleanValues();
    $sponsor['type'] = 'sponsor';
    $sponsor['title'] = $values->getValue('title');
    $sponsor['field_organisation_email_address'] = $values->getValue('org_email');
    $sponsor['field_name_of_representative'] = $values->getValue('rep_name');
    $sponsor['field_representative_email'] = $values->getValue('rep_email');
    $sponsor['field_representative_phone_numbe'] = $values->getValue('rep_phone');
    $sponsor['status'] = 0;

    $categories = $values->getValue('categories');
    $categories = array_filter($categories);

    $type =  $values->getValue('type');
    if ($type == 'sponsor') {
      $sponsor['field_sponsorship_details'] = $categories;
    }
    else {
      $sponsor['field_promotion_types'] = $categories;
    }

    $entity = \Drupal::entityTypeManager()->getStorage('node')->create($sponsor);
    $status = $entity->save();
    if ($status == SAVED_NEW) {
      $this->messenger()->addStatus($this->t('Thank you for your interest. Someone will be contacting you shortly.'));
    }
  }

}
