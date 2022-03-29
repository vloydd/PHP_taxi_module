<?php

namespace Drupal\taxi\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Our Form Admin Class.
 */
class TaxiList extends ConfirmFormBase {
  /**
   * ID of the item to edit.
   *
   * @var int
   */
  public $id;

  /**
   * Func for Setting an Question to Delete.
   */
  public function getQuestion(): string {
    return $this->t('Do You Want to Delete  this Book Request(s)?');
  }

  /**
   * Func for Get Back to Our Form.
   */
  public function getCancelUrl(): Url {
    return new Url('taxi.admin-page');
  }

  /**
   * Func for Getting ID.
   */
  public function getFormId(): string {
    return 'taxi_admin';
  }

  /**
   * Func for Setting Description.
   */
  public function getDescription() {
    return $this->t("Are You Really Sure to Delete this Request(s)?");
  }

  /**
   * Func for Submitting Our Deleting.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValue('table');
    $delete = array_filter($values);
    if (empty($delete)) {
      $this->messenger()->addError($this->t("Choose Something to Delete."));
    }
    else {
      \Drupal::database()->delete('taxi')->condition('id', $values, 'in')->execute();
      $form_state->setRedirect('taxi.admin-page');
      $this->messenger()->addStatus($this->t("Book Requests Are Deleted."));
    }
  }

  /**
   * Func for Building Our Admin Form.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $id = NULL) {
    $this->id = \Drupal::routeMatch()->getParameter('id');
    $queries = \Drupal::database()->select('taxi', 't');
    $queries->fields('t', ['id', 'name', 'email', 'time', 'timestamp']);
    $queries->orderBy('t.time', 'ASC');
    $results = $queries->execute()->fetchAll();
    $requests = [];
    foreach ($results as $data) {
      $delete_url = Url::fromRoute('taxi.delete-admin', ['id' => $data->id], []);
      $edit_url = Url::fromRoute('taxi.edit-admin', ['id' => $data->id], []);
      $delete = [
        'data' => [
          '#type' => 'link',
          '#title' => $this->t('Delete'),
          '#url' => $delete_url,
          '#options' => [
            'attributes' => [
              'class' => [
                'taxi-item',
                'taxi-delete',
                'use-ajax',
              ],
              'data-dialog-type' => 'modal',
            ],
          ],
        ],
      ];
      $edit = [
        'data' => [
          '#type' => 'link',
          '#title' => $this->t('Edit'),
          '#url' => $edit_url,
          '#options' => [
            'attributes' => [
              'class' => [
                'taxi-edit',
                'taxi-item',
                'use-ajax',
              ],
              'data-dialog-type' => 'modal',
            ],
          ],
        ],
      ];
      $requests[$data->id] = [
        'time' => date('d.m.y H:i:s', $data->time),
        'id' => $data->id,
        'name' => $data->name,
        'email' => $data->email,
        'timestamp' => date('d.m.y H:i:s', $data->timestamp),
        'delete' => $delete,
        'edit'  => $edit,
      ];
      $header = [
        'time' => $this->t('Request Date'),
        'id' => $this->t('ID'),
        'name' => $this->t('Name'),
        'email' => $this->t('Email'),
        'timestamp' => $this->t('Date Creation'),
        'edit' => $this->t('Edit'),
        'delete' => $this->t('Delete'),
      ];
      $form['table'] = [
        '#type' => 'tableselect',
        '#header' => $header,
        '#options' => $requests,
        '#tree' => TRUE,
        '#empty' => $this->t('Nothing there.'),
      ];
      $form['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t("Delete"),
      ];
    }
    return $form;
  }

}
