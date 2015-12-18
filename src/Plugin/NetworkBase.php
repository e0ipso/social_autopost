<?php

/**
 * @file
 * Contains \Drupal\social_autopost\Plugin\NetworkBase.
 */

namespace Drupal\social_autopost;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\social_autopost\Settings\SettingsInterface;

/**
 * Base class for Social Network plugins.
 */
abstract class NetworkBase extends PluginBase implements NetworkInterface {

  /**
   * Stores the settings wrapper object.
   *
   * @var SettingsInterface
   */
  protected $settings;

  /**
   * The 3rd party SDK library that will be used to do the publication.
   *
   * Every network will have a different object class.
   *
   * @var mixed
   */
  protected $sdk;

  /**
   * {@inheritdoc}
   *
   * By default assume that no action needs to happen to authenticate a request.
   */
  public function authenticate() {
    // Do nothing by default.
  }

  /**
   * {@inheritdoc}
   *
   * Instantiates the settings wrapper.
   */
  public function init(ConfigFactoryInterface $config_factory) {
    $definition = $this->getPluginDefinition();
    if (empty($definition['handlers']['settings']['class']) || empty($definition['handlers']['settings']['config_id']) || !class_exists($this->hadlers['settings'])) {
      throw new SocialAutopostException('There is no class for the settings. Please check your plugin annotation.');
    }
    $config = $config_factory->get($definition['handlers']['settings']['config_id']);
    $settings = call_user_func(array($definition['handlers']['settings'], 'create'), $config);
    if (!$settings instanceof SettingsInterface) {
      throw new SocialAutopostException('The provided settings class does not implement the expected settings interface.');
    }
    $this->settings = $settings;
  }

  /**
   * Gets the underlying SDK library.
   */
  protected function getSdk() {
    if (empty($this->sdk)) {
      $this->sdk = $this->initSdk();
    }
    return $this->sdk;
  }

  /**
   * Sets the underlying SDK library.
   *
   * @return mixed $library_instance
   *   The initialized 3rd party library instance.
   */
  abstract protected function initSdk();

}
