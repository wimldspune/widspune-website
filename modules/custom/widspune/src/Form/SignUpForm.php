<?php

namespace Drupal\widspune\Form;

use Drupal\Component\Utility\Environment;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\taxonomy\Entity\Term;
use Drupal\user\Entity\User;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Provides a WiDS Pune Core form.
 */
class SignUpForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'widspune_sign_up_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $type = NULL) {
//    $user = \Drupal::currentUser();
//    $check_against = ['administrator'];
//    $roles = $user->getRoles();
//    $result = array_intersect($check_against, $roles);
//    if ($user->id() !== 1 || empty($result)) {
//      $response = new RedirectResponse("user/" . $user->id() . '/edit');
//      $response->send();
//    }

    $required = TRUE;
    // Full Name
    $form['field_full_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Full name'),
      '#required' => $required,
      '#placeholder' => $this->t('e.g. FirstName LastName'),
    ];

    // Email address
    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email'),
      '#required' => $required,
      '#placeholder' => $this->t('e.g. user@domain.tld'),
    ];

    $form['pass'] = [
      '#type' => 'password',
      '#title' => t('Password'),
      '#required' => $required,
      '#attributes' => ['class' => ['password-field', 'js-password-field']],
      '#error_no_message' => TRUE,
    ];

    // Mobile Number
    $form['field_mobile_number'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Mobile Number'),
      '#required' => $required,
      '#placeholder' => $this->t('e.g. 1234567890'),
    ];
    // WhatsApp Number
    $form['field_whatsapp_number'] = [
      '#type' => 'textfield',
      '#title' => $this->t('WhatsApp Number'),
      '#required' => $required,
      '#placeholder' => $this->t('e.g. 1234567890'),
    ];

    // Gender
    $form['field_gender'] = [
      '#type' => 'radios',
      '#title' => $this->t('Gender'),
      '#required' => $required,
      '#options' => [
        'prefernotosay' => 'Prefer not to say',
        'male' => 'Male',
        'female' => 'Female',
      ],
    ];

    // Pin code
    $form['field_pin_code'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Pin Code'),
      '#required' => $required,
      '#placeholder' => $this->t('e.g. 123 456'),
    ];

    // T-shirt size

    // Educational Qualifications with Specialisation e.g. BE - Computer Science
    $form['field_education'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Educational Qualifications with Specialisation e.g. BE - Computer Science'),
      '#required' => $required,
      '#placeholder' => $this->t('e.g. BE - Computer Science'),
    ];

    // Age
    $query = \Drupal::entityQuery('taxonomy_term');
    $query->condition('vid',  'age_groups');
    $tids = $query->execute();
    $terms = Term::loadMultiple($tids);
    $term_data = [];
    foreach ($terms as $term) {
      $term_data[$term->id()] = $term->getName();
    }
    $form['field_age'] = [
      '#type' => 'radios',
      '#title' => $this->t('Age'),
      '#required' => $required,
      '#options' => $term_data,
    ];

    // Company / Organisation / University
    $form['field_com_org_uni'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Company / Organisation / University'),
      '#required' => $required,
      '#placeholder' => $this->t('e.g. Company / Organisation / University'),
    ];

    // Designation
    $form['field_designation'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Designation'),
      '#required' => $required,
      '#placeholder' => $this->t('e.g. Student/Researcher'),
    ];

    // Total Experience
    $query = \Drupal::entityQuery('taxonomy_term');
    $query->condition('vid',  'total_experience');
    $tids = $query->execute();
    $terms = Term::loadMultiple($tids);
    $term_data = [];
    foreach ($terms as $term) {
      $term_data[$term->id()] = $term->getName();
    }
    $form['field_total_experience'] = [
      '#type' => 'radios',
      '#title' => $this->t('Total Experience'),
      '#required' => $required,
      '#options' => $term_data,
    ];

    // Experience in DS (Data Science)
    $query = \Drupal::entityQuery('taxonomy_term');
    $query->condition('vid',  'exp_in_ds_data_science');
    $tids = $query->execute();
    $terms = Term::loadMultiple($tids);
    $term_data = [];
    foreach ($terms as $term) {
      $term_data[$term->id()] = $term->getName();
    }
    $form['field_exp_in_data_science'] = [
      '#type' => 'radios',
      '#title' => $this->t('Experience in DS (Data Science)'),
      '#required' => $required,
      '#options' => $term_data,
    ];

    // Add photo
    $form['profile_image'] = [
      '#type' => 'managed_file',
      '#title' => t('Profile Picture'),
      '#upload_validators' => array(
        'file_validate_extensions' => array('gif png jpg jpeg'),
        'file_validate_size' => array(Environment::getUploadMaxSize()),
      ),
     '#upload_location' => 'public://profile-pictures',
     '#required' => $required,
     '#description' => $this->t('Your virtual face or picture.'),
    ];

    // Linkedin profile
    $form['field_linkedin_profile'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Linkedin profile'),
      '#required' => $required,
      '#placeholder' => $this->t('e.g. https://www.linkedin.com/'),
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

  private function uniqueEmailValidate($email) {
    $db = \Drupal::service('database');
    $query = $db->select('users_field_data', 'users');
    $query->fields('users', ['uid']);
    $query->fields('users', ['mail']);
    $query->condition('mail', '%' . $email . '%', 'LIKE');
    $uids = $query->execute()->fetchCol();
    return count($uids);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $email_validator = \Drupal::service('email.validator');

    $email = $form_state->getValue('email');
    if ($email !== '' && !$email_validator->isValid($email)) {
      $form_state->setErrorByName('email', t('The email address %mail is not valid.', ['%mail' => $email]));
    }
    elseif ($email !== '' && $this->uniqueEmailValidate($email)) {
      $form_state->setErrorByName('email', t('The email address %mail is already taken.', ['%mail' => $email]));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

//    field_full_name
//    field_mobile_number
//    field_whatsapp_number
//    field_gender
//    field_pin_code
//    field_education
//    field_age [taxonomy]
//    field_com_org_uni [taxonomy]
//    field_designation
//    field_total_experience [terms]
//    field_exp_in_data_science [terms]
//    field_linkedin_profile

    $values = $form_state->cleanValues();
    $user['field_full_name'] = $values->getValue('field_full_name');
    $user['field_mobile_number'] = $values->getValue('field_mobile_number');
    $user['field_whatsapp_number'] = $values->getValue('field_whatsapp_number');
    $user['field_pin_code'] = $values->getValue('field_pin_code');
    $user['field_education'] = $values->getValue('field_education');
    $user['field_designation'] = $values->getValue('field_designation');

    $user['field_gender'] = $values->getValue('field_gender');
    $user['field_age'] = $values->getValue('field_age');
    $user['field_total_experience'] = $values->getValue('field_total_experience');
    $user['field_exp_in_data_science'] = $values->getValue('field_exp_in_data_science');
    $user['field_linkedin_profile'] = $values->getValue('field_linkedin_profile');

    $user_picture = $values->getValue('profile_image');
    $file_id = reset($user_picture) ?? NULL;
    $user['user_picture'] = $file_id;

    $field_com_org_uni = $values->getValue('field_com_org_uni');
    if ($this->checkCompanyUniversity($field_com_org_uni)) {
      $term = $this->checkCompanyUniversity($field_com_org_uni);
    }
    else {
      $term = $this->createCompanyUniversity($field_com_org_uni);
    }
    $user['field_com_org_uni'] = $term;

    $email = $values->getValue('email');
    $pass = $values->getValue('pass');

    $new_user = User::create();
    $new_user->setEmail($email);
    $new_user->setUsername($email);
    $new_user->set('init', $email);
    $new_user->setPassword($pass);
    $new_user->activate();

    foreach ($user as $field => $value) {
      $new_user->set($field, $value);
    }
    $new_user->save();
    _user_mail_notify('register_no_approval_required', $new_user);
    \Drupal::messenger()->addStatus($this->t('Registration successful.'));
    $form_state->setRedirect('<front>');

  }

  private function checkCompanyUniversity($name) {
    $db = \Drupal::service('database');
    $query = $db->select('taxonomy_term_field_data', 'terms');
    $query->condition('vid', 'company_organisation_university');
    $query->fields('terms', ['tid']);
    $query->condition('name', $name);
    $tids = $query->execute()->fetchCol();
    if (!empty($tids)) {
      return reset($tids);
    }
  }

  private function createCompanyUniversity($name) {
    $term = Term::create([
      'vid' => 'company_organisation_university',
      'name' => $name
    ]);
    $term->save();
    return $term->id();
  }

}
