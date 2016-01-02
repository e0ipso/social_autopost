<?php

/**
 * @file
 * Contains \Drupal\social_autopost\AutopostAccessControlHandler.
 */

namespace Drupal\social_autopost;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Autopost entity.
 *
 * @see \Drupal\social_autopost\Entity\Autopost.
 */
class AutopostAccessControlHandler extends EntityAccessControlHandler {
  /**
   * {@inheritdoc}
   */
  protected function checkAccess(AutopostInterface $entity, $operation, AccountInterface $account) {
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished autopost entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published autopost entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit autopost entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete autopost entities');
    }

    return AccessResult::allowed();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add autopost entities');
  }

}
