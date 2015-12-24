<?php

/**
 * @file
 * Contains \Drupal\social_autopost\Plugin\NetworkInterface.
 */

namespace Drupal\social_autopost;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Executable\ExecutableInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * Defines an interface for Social Network plugins.
 */
interface NetworkInterface extends PluginInspectionInterface, ExecutableInterface, ContainerFactoryPluginInterface {

  /**
   * Authenticates the request with the SDK library.
   *
   * Most of the time this will just mean settings some state properties for so
   * the publish method can pass them along to the external SDK library. The
   * authentication is considered to be at the plugin level. If your network
   * implementation needs the authentication to happen at every request,
   * implement that business logic in doPost.
   */
  public function authenticate();

  /**
   * Execute the posting action.
   *
   * Uses the underlying SDK library to publish to the social network.
   */
  public function doPost();

}
