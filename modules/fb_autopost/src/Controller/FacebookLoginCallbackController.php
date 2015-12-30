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
    /* @var \Facebook\Helpers\FacebookRedirectLoginHelper $helper */
    $helper = $manager
      ->createInstance('facebook')
      ->getSdk()
      ->getRedirectLoginHelper();

    try {
      if ($helper->getError()) {
        drupal_set_message($this->t('Access token could not be acquired: @error. Reason: @reason.', [
          '@error' => $helper->getErrorDescription(),
          '@reason' => $helper->getErrorReason(),
        ]), 'warning');
      }
      else {
        if ($access_token = $helper->getAccessToken()) {
          $access_token = $access_token->isLongLived() ? $access_token : $this->makeTokenLongLived($access_token);
          drupal_set_message($this->t('Access token successfully acquired'));
          \Drupal::state()->set('fb_autopost.access_token', $access_token);
        }
        else {
          // If there was no error and there is no token it means that we are
          // logging out. In that scenario, remove the token.
          drupal_set_message($this->t('Access token successfully deleted.'));
          \Drupal::state()->delete('fb_autopost.access_token');
        }
      }
    }
    catch (\Facebook\Exceptions\FacebookSDKException $e) {
      throw new SocialAutopostException($e->getMessage(), $e->getCode());
    }

    return new RedirectResponse(Url::fromRoute('fb_autopost.settings')->toString());
  }

}
