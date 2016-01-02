<?php

/**
 * @file
 * Contains \Drupal\social_autopost\Entity\AutopostType.
 */

namespace Drupal\social_autopost\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\social_autopost\AutopostTypeInterface;

/**
 * Defines the Autopost type entity.
 *
 * @ConfigEntityType(
 *   id = "autopost_type",
 *   label = @Translation("Autopost type"),
 *   handlers = {
 *     "list_builder" = "Drupal\social_autopost\AutopostTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\social_autopost\Form\AutopostTypeForm",
 *       "edit" = "Drupal\social_autopost\Form\AutopostTypeForm",
 *       "delete" = "Drupal\social_autopost\Form\AutopostTypeDeleteForm"
 *     }
 *   },
 *   config_prefix = "autopost_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "autopost",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/autopost_type/{autopost_type}",
 *     "edit-form" = "/admin/structure/autopost_type/{autopost_type}/edit",
 *     "delete-form" = "/admin/structure/autopost_type/{autopost_type}/delete",
 *     "collection" = "/admin/structure/visibility_group"
 *   }
 * )
 */
class AutopostType extends ConfigEntityBundleBase implements AutopostTypeInterface {

  /**
   * The Autopost type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Autopost type label.
   *
   * @var string
   */
  protected $label;

}
