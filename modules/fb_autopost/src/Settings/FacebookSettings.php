<?php

/**
 * @file
 * Contains \Drupal\fb_autopost\Settings\FacebookSettings.
 */

namespace Drupal\fb_autopost\Settings;
use Drupal\social_autopost\Settings\SettingsBase;

/**
 * Class FacebookSettings.
 *
 * @package Drupal\fb_autopost\Settings
 */
class FacebookSettings extends SettingsBase implements FacebookSettingsInterface {

  /**
   * Application ID.
   *
   * @var string
   */
  protected $appId;

  /**
   * Application secret.
   *
   * @var string
   */
  protected $appSecret;

  /**
   * The default graph version.
   *
   * @var string
   */
  protected $version;

  /**
   * The default access token.
   *
   * @var string
   */
  protected $defaultToken;

  /**
   * {@inheritdoc}
   */
  public function getAppId() {
    if ($this->appId) {
      $this->appId = $this->config->get('app_id');
    }
    return $this->appId;
  }

  /**
   * {@inheritdoc}
   */
  public function getAppSecret() {
    if ($this->appSecret) {
      $this->appSecret = $this->config->get('app_secret');
    }
    return $this->appSecret;
  }

  /**
   * {@inheritdoc}
   */
  public function getVersion() {
    if ($this->version) {
      $this->version = $this->config->get('version');
    }
    return $this->version;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultToken() {
    if ($this->defaultToken) {
      $this->defaultToken = $this->config->get('default_token');
    }
    return $this->defaultToken;
  }

}
