<?php

/**
 * @file
 * Contains \Drupal\social_autopost\Form\NetworkFormBase.
 */
namespace Drupal\social_autopost\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Utility\Error;
use Drupal\social_autopost\Plugin\NetworkInterface;
use Drupal\social_autopost\SocialAutopostException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class NetworkFormBase.
 *
 * @package Drupal\social_autopost\Form
 */
abstract class NetworkFormBase extends ConfigFormBase {

  /**
   * The network plugin.
   *
   * @var NetworkInterface
   */
  protected $network;

  /**
   * Constructs a \Drupal\system\ConfigFormBase object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   */
  public function __construct(ConfigFactoryInterface $config_factory, NetworkInterface $network = NULL) {
    parent::__construct($config_factory);
    $this->network = $network;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $network = NULL;
    try {
      $network = $container->get('plugin.network.manager')
        ->createInstance(static::getNetworkMachineName());
    }
    catch (SocialAutopostException $exception) {
      $message = '%type: @message in %function (line %line of %file).';
      $variables = Error::decodeException($exception);

      $container
        ->get('logger.factory')
        ->get('social_autopost')
        ->log('warning', $message, $variables);
    }
    return new static($container->get('config.factory'), $network);
  }

  /**
   * Get the machine name of the network implementing the form.
   *
   * @return string
   *   The plugin ID.
   *
   * @throws SocialAutopostException
   *   When the method is not implemented.
   */
  protected static function getNetworkMachineName() {
    throw new SocialAutopostException('This method needs to be implemented.');
  }

}
