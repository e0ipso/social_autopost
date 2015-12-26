<?php

/**
 * @file
 * Contains \Drupal\fb_autopost\Plugin\Network\Facebook.
 */

namespace Drupal\fb_autopost\Plugin\Network;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\social_autopost\NetworkBase;
use Drupal\social_autopost\SocialAutopostException;

/**
 * Class Facebook.
 *
 * @package Drupal\fb_autopost\Plugin\Network
 *
 * @Network(
 *   id = "facebook",
 *   label = "Facebook",
 *   handlers = {
 *     "settings": {
 *       "class": "\Drupal\fb_autopost\Settings\FacebookSettings",
 *       "config_id": "fb_autopost.settings"
 *     }
 *   }
 * )
 */
class Facebook extends NetworkBase implements FacebookInterface {

  /**
   * {@inheritdoc}
   */
  protected function initSdk() {
    $class_name = '\Facebook\Facebook';
    if (!class_exists($class_name)) {
      throw new SocialAutopostException(sprintf('The PHP SDK for Facebook could not be found. Class: %s.', $class_name));
    }
    /* @var \Drupal\fb_autopost\Settings\FacebookSettingsInterface $settings */
    $settings = $this->settings;
    // All these settings are mandatory.
    $facebook_settings = [
      'app_id' => $settings->getAppId(),
      'app_secret' => $settings->getAppSecret(),
      'default_graph_version' => $settings->getVersion(),
    ];
    if ($default_token = $settings->getDefaultToken()) {
      $facebook_settings['default_access_token'] = $default_token;
    }
    return new \Facebook\Facebook($facebook_settings);
  }

  /**
   * {@inheritdoc}
   */
  public function doPost() {
    // FIXME: Implement doPost() method.
  }


  /**
   * {@inheritdoc}
   *
   * Checks for the required settings.
   */
  protected function init(ConfigFactoryInterface $config_factory) {
    parent::init($config_factory);

    // Validate the required configurations.
    /* @var \Drupal\fb_autopost\Settings\FacebookSettingsInterface $settings */
    $settings = $this->settings;
    $errors = [];
    if (!$settings->getAppId()) {
      $errors[] = $this->t('The Facebook Application ID is required.');
    }
    if (!$settings->getAppSecret()) {
      $errors[] = $this->t('The Facebook Application secret is required.');
    }
    if (!$settings->getVersion()) {
      $errors[] = $this->t('The default Graph Version for Facebook is required.');
    }
    if ($errors) {
      throw new SocialAutopostException(sprintf('Please check your Facebook Autopost configuration page. The following errors were found: %s', implode("\n", $errors)));
    }
  }

}
