<?php

namespace Drupal\widspune\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\taxonomy\Entity\Term;

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
  public function buildForm(array $form, FormStateInterface $form_state) {
    $content = '<h1 class="text-center page-header">Become a Sponsor</h1>
          <div = class= "form-description">
            <p>The Women in Data Science (WiDS) initiative aims to inspire and educate data scientists worldwide, regardless of gender, and support women in the field.</p>
            <span class="form-required">Required Fields </span>
          </div>
          <hr />';
    $form['message'] = [
      '#type' => 'markup',
      '#markup' => $content,
    ];

    // Sponsoring Organisation
    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Sponsoring Organisation'),
      '#required' => TRUE,
      '#placeholder' => $this->t('e.g. WiDS Pune'),
    ];

    // Organisation Mailing Address
    $form['org_email'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Organisation Mailing Address'),
      '#required' => TRUE,
      '#placeholder' => $this->t('e.g. contact@domain.tld'),
    ];

    // Name of Representative
    $form['rep_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name of Representative'),
      '#required' => TRUE,
      '#placeholder' => $this->t('eg. John Doe'),
    ];

    // Representative E-mail
    $form['rep_email'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Representative E-mail'),
      '#required' => TRUE,
      '#placeholder' => $this->t('e.g. contact@domain.tld'),
    ];

    // Representative Phone No.
    $form['rep_phone'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Representative Phone No.'),
      '#required' => TRUE,
      '#placeholder' => $this->t('e.g. 9890989898'),
    ];

    //
    $query = \Drupal::entityQuery('taxonomy_term');
    $query->condition('vid',  'sponsorship_details');
    $tids = $query->execute();
    $terms = Term::loadMultiple($tids);
    foreach ($terms as $term) {
      $term_data[$term->id()] = $term->getName();
    }

    // In what form would you like to Sponsor
    $form['type'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('In what form would you like to Sponsor'),
      '#required' => TRUE,
      '#options' => $term_data,
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
    if ($rep_phone !== '' && !is_integer($rep_phone)) {
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
    $sponsor['field_sponsorship_details'] = $values->getValue('type');
    $sponsor['status'] = 0;

    $entity = \Drupal::entityTypeManager()->getStorage('node')->create($sponsor);
    $status = $entity->save();
    if ($status == SAVED_NEW) {
      $this->messenger()->addStatus($this->t('Thank you for your interest. Someone will be contacting you shortly.'));

    }
  }

}
