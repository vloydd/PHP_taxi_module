<?php

namespace Drupal\taxi\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'Taxi Form' Block.
 *
 * @Block(
 *   id = "taxi_block",
 *   admin_label = @Translation("Taxi Form"),
 *   category = @Translation("Taxi"),
 * )
 */
class TaxiBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $form = \Drupal::formBuilder()->getForm('Drupal\taxi\Form\TaxiForm');
    return $form;
  }

}
