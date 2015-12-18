<?php

/**
 * @file
 * Contains \Drupal\social_autopost\Plugin\NetworkManager.
 */

namespace Drupal\social_autopost;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Provides the Social Network plugin manager.
 */
class NetworkManager extends DefaultPluginManager {

  /**
   * Constructor for NetworkManager objects.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/Network', $namespaces, $module_handler, 'Drupal\social_autopost\Plugin\NetworkInterface', 'Drupal\social_autopost\Annotation\Network');

    $this->alterInfo('social_autopost_network_info');
    $this->setCacheBackend($cache_backend, 'social_autopost_network_plugins');
  }

}
