<?php

namespace Drupal\taxi\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Defines a Form to Confirm Deletion of Something by ID for Admin.
 */
class TaxiDeleteAdmin extends ConfirmFormBase {

  /**
   * ID of the Item to Delete and Choose.
   *
   * @var int
   */
  public $id;

  /**
   * Gets an ID of the Deleting Form.
   */
  public function getFormId(): string {
    return 'taxi_delete';
  }

  /**
   * Builds Deleting Form.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $id = NULL): array {
    $this->id = $id;
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritDoc}
   */
  public function getQuestion():string {
    return $this->t('Delete This Taxi Request?');
  }

  /**
   * Sets Description for the Deleting Form.
   */
  public function getDescription():string {
    return $this->t('Dear Admin, Do You Really Want to Delete Taxi Request With id %id?', ['%id' => $this->id]);
  }

  /**
   * Sets Text that Confirms Deleting.
   */
  public function getConfirmText():string {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelText():string {
    return $this->t('Cancel');
  }

  /**
   * {@inheritDoc}
   */
  public function getCancelUrl() {
    return new Url('taxi.admin-page');
  }

  /**
   * Submits Deletion.
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    \Drupal::database()->delete('taxi')->condition('id', $this->id)->execute();
    $this->messenger()
      ->addStatus($this->t('You Deleted Book(s)'));
    $form_state->setRedirect('taxi.admin-page');
  }

}
