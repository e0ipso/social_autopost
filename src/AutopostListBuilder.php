<?php

/**
 * @file
 * Contains \Drupal\social_autopost\AutopostListBuilder.
 */

namespace Drupal\social_autopost;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Routing\LinkGeneratorTrait;
use Drupal\Core\Url;

/**
 * Defines a class to build a listing of Autopost entities.
 *
 * @ingroup social_autopost
 */
class AutopostListBuilder extends EntityListBuilder {

  use LinkGeneratorTrait;

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Autopost ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\social_autopost\Entity\Autopost */
    $row['id'] = $entity->id();
    $row['name'] = $this->l(
      $entity->label(),
      new Url(
        'entity.autopost.edit_form', array(
          'autopost' => $entity->id(),
        )
      )
    );
    return $row + parent::buildRow($entity);
  }

}
