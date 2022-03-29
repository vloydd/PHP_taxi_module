<?php

namespace Drupal\taxi\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Controller Class.
 */
class TaxiPage extends ControllerBase {

  /**
   * Shows Our Content.
   *
   * @return array
   *   Returns Our TaxiForm and Theme.
   */
  public function content(): array {
    $form = \Drupal::formBuilder()->getForm('Drupal\taxi\Form\TaxiForm');
    $theme = 'taxi-theme';
    return [
      '#theme' => $theme,
      '#form' => $form,
    ];
  }

}
