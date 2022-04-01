<?php

namespace Drupal\taxi\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Our Form Class.
 */
class TaxiForm extends FormBase {

  /**
   * Gets a Form ID.
   */
  public function getFormId() :string {
    return 'taxi_form';
  }

  /**
   * Builds Our Taxi Form.
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {

    $form['#prefix'] = '<div id="form-wrapper" class="col-md-6 col-xs-12 ml-auto mr-auto">';
    $form['#suffix'] = '</div>';
    $form['message'] = [
      '#type' => 'markup',
      '#markup' =>
      '<h3>Now You Can Book a Taxi</h3>',
    ];
    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#placeholder' => $this->t("Enter Your Name"),
      '#required' => TRUE,
      '#wrapper_attributes' => ['class' => 'col-xs-12'],
      '#maxlength' => 100,
      '#attributes' => [
        'data-disable-refocus' => 'true',
        'autocomplete' => 'off',
      ],
    ];
    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email'),
      '#description' => $this->t('Only Alpha, ., _, - and @ Allowed'),
      '#placeholder' => $this->t("Enter Your Email"),
      '#maxlength' => 100,
      '#required' => TRUE,
      '#wrapper_attributes' => ['class' => 'col-xs-12'],
      '#attributes' => [
        'data-disable-refocus' => 'true',
        'autocomplete' => 'off',
      ],
    ];
    $form['time'] = [
      '#type' => 'datetime',
      '#size' => 20,
      '#required' => TRUE,
      '#date_date_format' => 'd/m/Y',
      '#date_time_format' => 'H:m',
      '#suffix' => '<p class="false_form false_time"></p>',
    ];
    $form['adults'] = [
      '#title' => $this->t("Amount of Adults"),
      '#type' => 'number',
      '#min' => 0,
      '#max' => 10,
      '#required' => TRUE,
      '#wrapper_attributes' => ['class' => 'col-6'],
      '#description' => $this->t('Only 0-10 Allowed'),
      '#placeholder' => $this->t("Enter Amount of Adults"),
    ];
    $form['children'] = [
      '#title' => $this->t("Amount of Children"),
      '#type' => 'number',
      '#required' => FALSE,
      '#min' => 0,
      '#max' => 10,
      '#default_value' => 0,
      '#wrapper_attributes' => ['class' => 'col-6'],
      '#description' => $this->t('Only 0-10 Allowed'),
      '#placeholder' => $this->t("Enter Amount of Children"),
    ];
    $form['road'] = [
      '#type' => 'select',
      '#title' => $this->t("To/From Airport"),
      '#required' => TRUE,
      '#wrapper_attributes' => ['class' => 'col-6'],
      '#options' => [
        'To' => $this->t('To'),
        'From' => $this->t('From'),
      ],
    ];
    $form['tariff'] = [
      '#type' => 'select',
      '#title' => $this->t("Your Tariff"),
      '#required' => FALSE,
      '#wrapper_attributes' => ['class' => 'col-6'],
      '#options' => [
        'Eco' => $this->t('Eco'),
        'Fast' => $this->t('Fast'),
        'Super-Fast' => $this->t('Super Fast'),
      ],
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => $this->t('Order Now'),
      '#attributes' => [
        'class' => ['btn', 'btn-danger'],
      ],
      '#ajax' => [
        'callback' => '::setMessage',
        'wrapper' => 'form-wrapper',
        'effect' => 'fade',
        'event' => 'click',
      ],
    ];
    $form['#attached']['library'][] = 'taxi/taxi_library';
    return $form;
  }

  /**
   * Validates Our Form.
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    $name = $form_state->getValue('name');
    $email = $form_state->getValue('email');
    $adults = $form_state->getValue('adults');
    $children = $form_state->getValue('children');
    $road = $form_state->getValue('road');
    $requires_name = "/[-_'A-Za-z0-9 ]/";
    $requires_email = '/\w+@[a-zA-Z_]+?\.[a-zA-Z]{2,6}/';
    $length_name = strlen($name);
    $time = strtotime($form_state->getValue('time'));
    $timestamp = time();
    $queries = \Drupal::database()->select('taxi', 't');
    $queries->fields('t', ['name', 'email','time', 'road']);
    $results = $queries->execute()->fetchAll();
    $requests = [];
    foreach ($results as $data) {
      $requests[] = [
        'name' => $data->name,
        'email' => $data->email,
        'time' => $data->time,
        'road' => $data->road,
      ];
    }
    for ($i = 0; $i < count($requests); $i++) {
      if ($time == $requests[$i]['time']) {
        $form_state->setErrorByName(
          'time',
          $this->t(
            "Time: Sorry, We Already Have a Request on This Time(."
          )
        );
      }
      if ($name == $requests[$i]['name'] && $email == $requests[$i]['email'] && $road == $requests[$i]['road']) {
        $form_state->setErrorByName(
          'name',
          $this->t(
            "Taxi: Sorry, We Already Have an Order From You. You Can't Ride in Two Cars in Same Direction(."
          )
        );
      }
    }
    if ($adults == 0 && $children == 0) {
      $form_state->setErrorByName(
        'adults',
        $this->t(
          "Taxi: You Can't Book an Empty Taxi(."
        )
      );
    }
    if ($adults == 0 && $children != 0) {
      $form_state->setErrorByName(
        'children',
        $this->t(
          "Taxi: You Can't Let a Child Go Alone(."
        )
      );
    }
    if ($time < $timestamp) {
      $form_state->setErrorByName(
        'time',
        $this->t(
          "Time: You Cannot Book a Taxi in the Past(."
        )
      );
    }
    if ($time - $timestamp < 30 * 60) {
      $form_state->setErrorByName(
        'time',
        $this->t(
          'Time: The Difference Should Be at Least 30 Minutes(.'
        )
      );
    }
    if ($length_name < 2) {
      $form_state->setErrorByName(
        'name',
        $this->t(
          "Name: Oh No! Your Name is Shorter Than 2 Symbols(. Don't be Shy, it's Alright."
        )
      );
    }
    elseif ($length_name > 100) {
      $form_state->setErrorByName(
        'name',
        $this->t(
          'Name: Oh No! Your Name is Longer Than 100 Symbols(. Can You Cut it a Bit?'
        )
      );
    }
    if (!preg_match($requires_name, $name)) {
      $form_state->setErrorByName('name',
        $this->t(
          "Name: Oh No! In Your Name %title You False Symbols(. Acceptable is: A-Z, 0-9 _ and '.", ['%title' => $name]
        )
      );
    }
    if (!preg_match($requires_email, $email)) {
      $form_state->setErrorByName('email',
        $this->t(
          'Mail: Oh No! Your Email %title is Invalid(', ['%title' => $email]
        )
      );
    }
  }

  /**
   * This Func is for AJAX Redirect if Everything Fine or Setting Errors If it's Not.
   *
   * @param array $form
   *   Comment smth.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Comment smth.
   */
  public function setMessage(array &$form, FormStateInterface $form_state) {
    return $form;
  }

  /**
   * Submits Form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $data = [
      'name' => $form_state->getValue('name'),
      'email' => $form_state->getValue('email'),
      'time' => strtotime($form_state->getValue('time')),
      'adults' => $form_state->getValue('adults'),
      'children' => $form_state->getValue('children'),
      'road' => $form_state->getValue('road'),
      'tariff' => $form_state->getValue('tariff'),
      'timestamp' => time(),
    ];

    \Drupal::database()->insert('taxi')->fields($data)->execute();

    $newMail = \Drupal::service('plugin.manager.mail');
    $test['name'] = $form_state->getValue('name');
    $test['email'] = $form_state->getValue('email');
    $test['time'] = strtotime($form_state->getValue('time'));
    $test['adults'] = $form_state->getValue('adults');
    $test['children'] = $form_state->getValue('children');
    $test['road'] = $form_state->getValue('road');
    $test['tariff'] = $form_state->getValue('tariff');

    $mail = $newMail->mail('taxi', 'ordered', $test['email'], 'en', $test, $reply = NULL, $send = TRUE);

    $this->messenger()
      ->addStatus($this->t('You Booked a Taxi on %time.', ['%time' => $form_state->getValue('time')]));
  }

}
