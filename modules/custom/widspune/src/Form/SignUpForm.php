<?php

namespace Drupal\widspune\Form;

use Drupal\Component\Utility\Environment;
use Drupal\Component\Utility\UrlHelper;
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
      '#placeholder' => $this->t('Your secret to sign in.'),
    ];

    // Mobile Number
    $form['field_mobile_number'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Mobile Number'),
      '#required' => $required,
      '#placeholder' => $this->t('e.g. 1234567890'),
      '#description' => $this->t('Please enter Indian number without country code.'),
    ];

    // WhatsApp Number
    $form['field_whatsapp_number'] = [
      '#type' => 'textfield',
      '#title' => $this->t('WhatsApp Number'),
      '#required' => $required,
      '#placeholder' => $this->t('e.g. 1234567890'),
      '#description' => $this->t('Please enter Indian number without country code.'),
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
      '#placeholder' => $this->t('e.g. 123456'),
    ];

    // T-shirt size
    $query = \Drupal::entityQuery('taxonomy_term');
    $query->condition('vid',  't_shirt_sizes');
    $query->sort('tid');
    $tids = $query->execute();
    $terms = Term::loadMultiple($tids);
    $term_data = [];
    foreach ($terms as $term) {
      $term_data[$term->id()] = $term->getName();
    }
    $form['field_t_shirt_size'] = [
      '#type' => 'select',
      '#title' => $this->t('T-shirt size'),
      '#required' => $required,
      '#options' => $term_data,
    ];

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
    $query->sort('tid');
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
    $query->sort('tid');
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
    $query->sort('tid');
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
     '#description' => $this->t('Your virtual face or picture. <br> 100 MB limit. <br>Allowed types: png gif jpg jpeg.<br>Headshot if card size.'),
    ];

    // Linkedin profile
    $form['field_linkedin_profile'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Linkedin profile'),
      '#required' => FALSE,
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

    $value = $form_state->getValue('field_linkedin_profile');
    $val1 = 'https://www.linkedin.com/';
    $val2 = 'http://www.linkedin.com/';
    if ($value !== '') {
      if (!UrlHelper::isValid($value, TRUE)) {
        $form_state->setErrorByName('field_linkedin_profile', t('The URL %url is not valid.', ['%url' => $value]));
      }
      elseif (substr($value, 0, strlen($val1)) !== $val1
        && substr($value, 0, strlen($val2)) !== $val2) {
        $form_state->setErrorByName('field_linkedin_profile', t('The URL %url is not valid LinkedIn url.', ['%url' => $value]));
      }
      elseif (strlen($value) < 26) {
        $form_state->setErrorByName('field_linkedin_profile', t('The URL %url is not valid LinkedIn url.', ['%url' => $value]));
      }
    }

    $field_mobile_number = $form_state->getValue('field_mobile_number');
    $mobile_pattern = "/^[6-9][0-9]{9}$/" ;
    if (!empty($field_mobile_number)
      && preg_match($mobile_pattern, $field_mobile_number) !== 1) {
      $form_state->setErrorByName('field_mobile_number', t('Please enter the valid mobile number.'));
    }
    $field_whatsapp_number = $form_state->getValue('field_whatsapp_number');
    if (!empty($field_whatsapp_number)
      && preg_match($mobile_pattern, $field_whatsapp_number) !== 1) {
      $form_state->setErrorByName('field_whatsapp_number', t('Please enter the valid number.'));
    }

    $field_pin_code = $form_state->getValue('field_pin_code');
    $postal_pin_pattern = "/^[0-9]{6}$/";
    if (!empty($field_pin_code)
      && preg_match($postal_pin_pattern, $field_pin_code) !== 1) {
      $form_state->setErrorByName('field_pin_code', t('Please enter the valid pin code number.'));
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
    $user['field_t_shirt_size'] = $values->getValue('field_t_shirt_size');
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
    if (!empty($field_com_org_uni)) {
      if ($this->checkCompanyUniversity($field_com_org_uni)) {
        $term = $this->checkCompanyUniversity($field_com_org_uni);
      }
      else {
        $term = $this->createCompanyUniversity($field_com_org_uni);
      }
      $user['field_com_org_uni'] = $term;
    }

    $email = $values->getValue('email');
    $pass = $values->getValue('pass');

    $new_user = User::create();
    $new_user->setEmail($email);
    $new_user->setUsername($email);
    $new_user->set('init', $email);
    $new_user->setPassword($pass);
    $new_user->activate();

    foreach ($user as $field => $value) {
      if ($value) {
        $new_user->set($field, $value);
      }
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
