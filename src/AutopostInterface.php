<?php

/**
 * @file
 * Contains \Drupal\social_autopost\AutopostInterface.
 */

namespace Drupal\social_autopost;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Autopost entities.
 *
 * @ingroup social_autopost
 */
interface AutopostInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {
  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Autopost type.
   *
   * @return string
   *   The Autopost type.
   */
  public function getType();

  /**
   * Gets the Autopost name.
   *
   * @return string
   *   Name of the Autopost.
   */
  public function getName();

  /**
   * Sets the Autopost name.
   *
   * @param string $name
   *   The Autopost name.
   *
   * @return \Drupal\social_autopost\AutopostInterface
   *   The called Autopost entity.
   */
  public function setName($name);

  /**
   * Gets the Autopost creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Autopost.
   */
  public function getCreatedTime();

  /**
   * Sets the Autopost creation timestamp.
   *
   * @param int $timestamp
   *   The Autopost creation timestamp.
   *
   * @return \Drupal\social_autopost\AutopostInterface
   *   The called Autopost entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Autopost published status indicator.
   *
   * Unpublished Autopost are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Autopost is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Autopost.
   *
   * @param bool $published
   *   TRUE to set this Autopost to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\social_autopost\AutopostInterface
   *   The called Autopost entity.
   */
  public function setPublished($published);

  /**
   * Returns the Autopost posted status indicator.
   *
   * Uposted Autopost are not available in the remote social network.
   *
   * @return bool
   *   TRUE if the Autopost is posted.
   */
  public function isPosted();

  /**
   * Sets the published status of a Autopost.
   *
   * @param bool $posted
   *   TRUE to set this Autopost to posted, FALSE to set it to unposted.
   *
   * @return \Drupal\social_autopost\AutopostInterface
   *   The called Autopost entity.
   */
  public function setPosted($posted);

}
