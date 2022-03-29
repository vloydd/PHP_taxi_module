<?php

namespace Drupal\taxi\Form;

use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Url;

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

    $form['#prefix'] = '<div id="form-wrapper">';
    $form['#suffix'] = '</div>';
    $form['message'] = [
      '#type' => 'markup',
      '#markup' =>
      '<h3>Now You Can Book a Taxi</h3>',
    ];
    $form['#prefix'] = '<div id="form-wrapper" class="col-md-6 col-xs-12 ml-auto mr-auto">';
    $form['#suffix'] = '</div>';
    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#placeholder' => $this->t("Enter Your Name"),
      '#required' => TRUE,
      '#wrapper_attributes' => ['class' => 'col-xs-12'],
      '#maxlength' => 100,
      '#ajax' => [
        'callback' => '::validateFormAjaxName',
        'event' => 'change',
        'progress' => [
          'type' => 'none',
        ],
      ],
      '#attributes' => [
        'data-disable-refocus' => 'true',
        'autocomplete' => 'off',
      ],
      '#suffix' => '<p class="false_form false_name"></p>',
    ];
    $form['email'] = [
      '#required' => TRUE,
      '#type' => 'email',
      '#title' => $this->t('Email'),
      '#description' => $this->t('Only Alpha, ., _, - and @ Allowed'),
      '#placeholder' => $this->t("Enter Your Email"),
      '#maxlength' => 100,
      '#ajax' => [
        'callback' => '::validateFormAjaxEmail',
        'event' => 'change',
        'progress' => [
          'type' => 'none',
        ],
      ],
      '#attributes' => [
        'data-disable-refocus' => 'true',
        'autocomplete' => 'off',
      ],
      '#suffix' => '<p class="false_form false_email"></p>',
    ];
    $form['time'] = [
      '#type' => 'datetime',
      '#size' => 20,
      '#required' => TRUE,
      '#date_date_format' => 'd/m/Y',
      '#date_time_format' => 'H:m',
      '#suffix' => '<p class="false_form false_time"></p>',
      '#ajax' => [
        'callback' => '::validateFormAjaxTime',
        'event' => 'change',
        'progress' => [
          'type' => 'none',
        ],
      ],
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => $this->t('Add Review'),
      '#attributes' => [
        'class' => ['btn', 'btn-warning'],
      ],
      '#ajax' => [
        'callback' => '::setMessage',
        'wrapper' => 'form-wrapper',
        'effect' => 'fade',
        'event' => 'click',
      ],
    ];
    return $form;
  }

  /**
   * Validates Our Form.
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    $name = $form_state->getValue('name');
    $email = $form_state->getValue('email');
    $requiers_name = "/[-_'A-Za-z0-9 ]/";
    $requiers_email = '/[-_@A-Za-z.]/';
    $length_name = strlen($name);
    $length_email = strlen($email);
    $time = strtotime($form_state->getValue('time'));
    $timestamp = time();
    if ($time - $timestamp < 3 * 60 * 60) {
      $form_state->setErrorByName(
        'time',
        $this->t(
          'Time: The Difference Should Be at Least 3 Hours(.'
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
    for ($i = 0; $i < $length_name; $i++) {
      if (!preg_match($requiers_name, $name[$i])) {
        $form_state->setErrorByName('name',
          $this->t(
            "Name: Oh No! In Your Name %title You False Symbols(. Acceptable is: A-Z, 0-9 _ and '.", ['%title' => $name]
          )
        );
      }
    }
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
      for ($i = 0; $i < $length_email; $i++) {
        if (!preg_match($requiers_email, $email[$i])) {
          $form_state->setErrorByName('email',
            $this->t(
              'Mail: Oh No! Your Email %title is Invalid(', ['%title' => $email]
            )
          );
        }
      }
    }
  }

  /**
   * Validates Our Name with AJAX.
   *
   * @param array $form
   *   Comment smth.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Comment smth.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Comment smth.
   */
  public function validateFormAjaxName(array &$form, FormStateInterface $form_state): AjaxResponse {
    $name = $form_state->getValue('name');
    $emptyname = empty($name);
    $response = new AjaxResponse();
    $length_name = strlen($name);
    $requiers_name = "/[-_'A-Za-z0-9 ]/";
    if (($length_name > 100 || $length_name < 2) && $length_name != 0) {
      $message = $this->t('Name: Oh No! Your Name %name  Have False Length. The Length: %length.',
        ['%name' => $name, '%length' => $length_name]);
      $response->addCommand(
        new HtmlCommand(
          '.false_name',
          $message
        )
      );
      return $response;
    }
    for ($i = 0; $i < $length_name; $i++) {
      if (!preg_match($requiers_name, $name[$i])) {
        $message = $this->t("Name: Oh No! Your Name %name is Invalid(. You Should Use A-z, 0-9, and special symbols (-_').", ['%name' => $name]);
        $response->addCommand(
          new HtmlCommand(
            '.false_name',
            $message
          )
        );
        return $response;
      }
      else {
        $message = '';
        $response->addCommand(
          new HtmlCommand(
            '.false_name',
            $message
          )
        );
        return $response;
      }
    }
    if (($length_name == 0) || ($emptyname) || ($length_name <= 100 && $length_name >= 2)) {
      $message = '';
      $response->addCommand(
        new HtmlCommand(
          '.false_name',
          $message
        )
      );
      return $response;
    }
    return $response;
  }

  /**
   * Validates Our Email with AJAX.
   *
   * @param array $form
   *   Comment smth.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Comment smth.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Comment smth.
   */
  public function validateFormAjaxEmail(array &$form, FormStateInterface $form_state): AjaxResponse {
    $email = $form_state->getValue('email');
    $length_email = strlen($email);
    $emptyemail = empty($email);
    $response = new AjaxResponse();
    $requiers = '/[-_@A-Za-z.]/';
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $tmp = 0;
      for ($i = 0; $i < (strlen($email)); $i++) {
        if (!preg_match($requiers, $email[$i])) {
          $message = $this->t('Mail: Oh No! Your Email %title is Invalid(', ['%title' => $email]);
          $tmp++;
          $response->addCommand(
            new HtmlCommand(
              '.false_email',
              $message
            )
          );
          break;
        }
      }
      if ($tmp == 0) {
        $message = '';
        $response->addCommand(
          new HtmlCommand(
            '.false_email',
            $message
          )
        );
      }
    }
    else {
      $message =
        $this->t('Mail: Oh No! Your Email %title is Invalid(', ['%title' => $email]);
      $response->addCommand(
        new HtmlCommand(
          '.false_email',
          $message
        )
      );
    }
    if ($length_email > 255) {
      $message = $this->t(
        'Mail: On No, Your Email is too Long. MaxLength - 255. Please, Cut it Off. Your Length: %length.',
        ['%length' => $length_email]);
      $response->addCommand(
        new HtmlCommand(
          '.false_review',
          $message
        )
      );
      return $response;
    }
    if (($emptyemail)) {
      $message = '';
      $response->addCommand(
        new HtmlCommand(
          '.false_email',
          $message
        )
      );
      return $response;
    }
    return $response;
  }

  /**
   * Validates Our DateTime with AJAX.
   *
   * @param array $form
   *   Comment smth.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Comment smth.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Comment smth.
   */
  public function validateFormAjaxTime(array &$form, FormStateInterface $form_state): AjaxResponse {
    $response = new AjaxResponse();
    $time = strtotime($form_state->getValue('time'));
    $timestamp = time();
    if ($time - $timestamp <= 3 * 60 * 60) {
      $message = $this->t('Time: The Difference Should Be at Least 3 Hours(.');
      $response->addCommand(
        new HtmlCommand(
          '.false_time',
          $message
        )
      );
      return $response;
    }
    return $response;
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
    $response = new AjaxResponse();
    if (!$form_state->hasAnyErrors()) {
      $url = Url::fromRoute('taxi.main-page');
      $command = new RedirectCommand($url->toString());
      $response->addCommand($command);
      return $response;
    }
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
      'timestamp' => time(),
    ];

    \Drupal::database()->insert('taxi')->fields($data)->execute();

    $this->messenger()
      ->addStatus($this->t('You Booked a Taxi on %time.', ['%time' => $form_state->getValue('time')]));
  }

}
