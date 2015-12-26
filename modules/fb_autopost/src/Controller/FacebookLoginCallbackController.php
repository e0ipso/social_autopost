<?php

/**
 * @file
 * Contains \Drupal\fb_autopost\Controller\FacebookLoginCallbackController.
 */

namespace Drupal\fb_autopost\Controller;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\social_autopost\SocialAutopostException;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class FacebookLoginCallbackController.
 *
 * @package Drupal\fb_autopost\Controller
 */
class FacebookLoginCallbackController extends ControllerBase {

  /**
   * Controller callback.
   */
  public function saveAccessToken() {
    $manager = \Drupal::service('plugin.network.manager');
    $helper = $manager
      ->createInstance('facebook')
      ->getSdk()
      ->getRedirectLoginHelper();

    try {
      if ($access_token = $helper->getAccessToken()) {
        drupal_set_message($this->t('Access token successfully acquired'));
      }
      else {
        drupal_set_message($this->t('Access token could not be acquired'), 'warning');
      }
    }
    catch (\Facebook\Exceptions\FacebookSDKException $e) {
      throw new SocialAutopostException($e->getMessage(), $e->getCode());
    }

    return new RedirectResponse(Url::fromRoute('fb_autopost.settings')->toString());
  }

}
