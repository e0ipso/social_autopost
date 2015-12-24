<?php

/**
 * @file
 * Contains \Drupal\fb_autopost\Settings\FacebookSettingsInterface.
 */

namespace Drupal\fb_autopost\Settings;

/**
 * Class FacebookSettingsInterface.
 *
 * @package Drupal\fb_autopost\Settings
 */
interface FacebookSettingsInterface {

  /**
   * Gets the application ID.
   *
   * @return mixed
   *   The application ID.
   */
  public function getAppId();

  /**
   * Gets the application secret.
   *
   * @return string
   *   The application secret.
   */
  public function getAppSecret();

  /**
   * Gets the version.
   *
   * @return string
   *   The version.
   */
  public function getVersion();

  /**
   * Gets the default access token.
   *
   * @return string
   *   The default access token.
   */
  public function getDefaultToken();

}
