<?php

namespace Drupal\taxi\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Database\Database;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Our Form Edit Class.
 */
class TaxiEditAdmin extends TaxiForm {
  /**
   * ID of the item to edit.
   *
   * @var int
   */
  public $id;

  /**
   * Gets an ID  of Editing Form.
   */
  public function getFormId() :string {
    return 'taxi_edit';
  }

  /**
   * Builds Editing Form.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $id = NULL): array {
    $this->id = \Drupal::routeMatch()->getParameter('id');
    $conn = Database::getConnection();
    $data = [];
    if (isset($this->id)) {
      $query = $conn->select('taxi', 't')
        ->condition('id', $this->id)
        ->fields('t');
      $data = $query->execute()->fetchAssoc();
    }

    $form = parent::buildForm($form, $form_state);
    $form['message'] = [
      '#type' => 'markup',
      '#markup' => '<p class="error"></p>',
    ];
    $form['name']['#default_value'] = (isset($data['name'])) ? $data['name'] : '';
    $form['email']['#default_value'] = (isset($data['email'])) ? $data['email'] : '';
    $form['adults']['#default_value'][] = (isset($data['adults'])) ? $data['adults'] : '';
    $form['children']['#default_value'][] = (isset($data['children'])) ? $data['children'] : '';
    $form['road']['#default_value'][] = (isset($data['road'])) ? $data['road'] : '';
    $form['tariff']['#default_value'][] = (isset($data['tariff'])) ? $data['tariff'] : '';
    $form['actions']['submit']['#value'] = $this->t('Edit');
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

    if (isset($this->id)) {
      \Drupal::database()->update('taxi')->fields($data)->condition('id', ($this->id))->execute();
    }
    else {
      \Drupal::database()->insert('taxi')->fields($data)->execute();
    }
    $this->messenger()
      ->addStatus($this->t('You Edited Book Request on %time.', ['%time' => $form_state->getValue('time')]));

  }

  /**
   * This func is for AJAX Redirect or Errors.
   *
   * @param array $form
   *   Comment smth.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Comment smth.
   */
  public function setMessage(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    if (!$form_state->hasAnyErrors()) {
      $url = Url::fromRoute('taxi.admin-page');
      $command = new RedirectCommand($url->toString());
      $response->addCommand($command);
      return $response;
    }
    return $form;
  }

}
