<?php

/**
 * @file
 * Contains \Drupal\social_autopost\Entity\Autopost.
 */

namespace Drupal\social_autopost\Entity;

use Drupal\views\EntityViewsData;
use Drupal\views\EntityViewsDataInterface;

/**
 * Provides Views data for Autopost entities.
 */
class AutopostViewsData extends EntityViewsData implements EntityViewsDataInterface {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['autopost']['table']['base'] = array(
      'field' => 'id',
      'title' => $this->t('Autopost'),
      'help' => $this->t('The Autopost ID.'),
    );

    return $data;
  }

}
