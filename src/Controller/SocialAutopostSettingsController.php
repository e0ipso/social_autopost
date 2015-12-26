<?php

/**
 * @file
 * Contains \Drupal\social_autopost\Controller\SocialAutopostSettingsController.
 */

namespace Drupal\social_autopost\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\social_autopost\Plugin\NetworkManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SocialAutopostSettingsController.
 *
 * @package Drupal\social_autopost\Controller
 */
class SocialAutopostSettingsController extends ControllerBase {

  /**
   * The network manager.
   *
   * @var NetworkManager
   */
  protected $networkManager;

  /**
   * Instantiates a SocialAutopostSettingsController object.
   *
   * @param NetworkManager $network_manager
   *   The plugin manager.
   */
  public function __construct(NetworkManager $network_manager) {
    $this->networkManager = $network_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /* @var NetworkManager $manager */
    $manager = $container->get('plugin.network.manager');
    return new static($manager);
  }

  /**
   * Page callback for route: fb_autopost.settings.
   */
  public function integrations() {
    $networks = $this->networkManager->getDefinitions();
    $header = [
      $this->t('Machine name'),
      $this->t('Label'),
    ];
    $data = [];
    foreach ($networks as $network) {
      $data[] = [
        $network['id'],
        $network['label'],
      ];
    }
    return [
      '#theme' => 'table',
      '#header' => $header,
      '#rows' => $data,
      '#empty' => $this->t('There are no social integrations enabled.'),
    ];
  }
}
