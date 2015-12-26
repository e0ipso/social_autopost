<?php

/**
 * @file
 * Contains \Drupal\fb_autopost\Form\FacebookSettingsForm.
 */

namespace Drupal\fb_autopost\Form;

use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\social_autopost\Form\NetworkFormBase;
use Facebook\Exceptions\FacebookSDKException;

/**
 * Class FacebookSettingsForm.
 *
 * @package Drupal\fb_autopost\Form
 */
class FacebookSettingsForm extends NetworkFormBase implements FormInterface {

  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'fb_autopost_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected static function getNetworkMachineName() {
    return 'facebook';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('fb_autopost.settings');
    $form = parent::buildForm($form, $form_state);
    $form['app_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Application ID'),
      '#description' => $this->t('The application ID as retrieved from the Facebook APP Dashboard.'),
      '#default_value' => $config->get('app_id') ?: '',
      '#required' => TRUE,
    ];
    $form['app_secret'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Application Secret'),
      '#description' => $this->t('The application secret as retrieved from the Facebook APP Dashboard.'),
      '#default_value' => $config->get('app_secret') ?: '',
      '#required' => TRUE,
    ];
    $form['version'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Graph Version'),
      '#description' => $this->t('The default graph version to use. It can be overridden per request.'),
      '#default_value' => $config->get('version') ?: '',
      '#required' => TRUE,
    ];

    $form['destinations'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Available destinations'),
      '#description' => $this->t("Enter a destination per line. Each line should contain the label of the destination and the Facebook's feed ID. Add the special feed ID 'me' to post to the currently visitor's Facebook feed."),
      '#default_value' => $this::encodeDestinations($config->get('destinations')),
    ];

    if ($config->get('app_id') && $config->get('app_secret')) {
      $fb = $this->network->getSdk();
      $helper = $fb->getRedirectLoginHelper();
      if ($access_token = $helper->getAccessToken()) {
        $url = $helper->getLogoutUrl($access_token);
        $text = $this->t('Logout');
      }
      else {
        $url_object = Url::fromRoute('fb_autopost.login_callback', [], [
          'absolute' => TRUE,
        ]);
        $url = $helper->getLoginUrl($url_object->toString(), []);
        $text = $this->t('Login');
      }
      try {
        $response = $fb->get('/me');
        $status = TRUE;
      }
      catch (FacebookSDKException $e) {
        $status = FALSE;
      }
      $form['authentication'] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Authentication'),
        '#description' => $this->t('Use this section to authenticate with Facebook.'),
        'table' => [
          '#type' => 'table',
          '#rows' => [
            [
              ['header' => TRUE, 'data' => $this->t('Status')],
              $status ? $this->t('Enabled') : $this->t('Disabled'),
            ],
            [
              ['header' => TRUE, 'data' => $this->t('Action')],
              [
                'data' => [
                  '#type' => 'link',
                  '#url' => Url::fromUri($url),
                  '#title' => $text,
                ],
              ],
            ],
          ],
        ],
      ];

    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['fb_autopost.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('fb_autopost.settings');
    $properties = ['app_id', 'app_secret', 'version'];
    array_walk($properties, function ($property) use ($config, $form_state) {
      $config->set($property, $form_state->getValue($property));
    });

    $destinations = $form_state->getValue('destinations');
    $config->set('destinations', $this::parseDestinations($destinations));

    $config->save();
    parent::submitForm($form, $form_state);
  }

  /**
   * Parses text form destinations.
   *
   * @param string $destinations
   *   The pipe separated destinations configuration.
   *
   * @return array
   *   The structured configuration.
   */
  protected static function parseDestinations($destinations) {
    // Decompose the destinations into structured data.
    $stuctured = [];
    $destinations = explode("\n", $destinations);
    foreach ($destinations as $item) {
      // Remove possible extra white spaces.
      $item = trim($item);
      if (empty($item)) {
        continue;
      }
      $parts = explode('|', $item);
      if (count($parts) == 1) {
        $parts[] = $parts[0];
      }
      $stuctured[$parts[1]] = [
        'label' => $parts[0],
        'feed_id' => $parts[1],
      ];
    }

    return $stuctured;
  }

  /**
   * Transform the structured config into something for a text area.
   *
   * @param array $destinations
   *   The structured configuration.
   *
   * @return string
   *   The configuration to be used in a text area.
   */
  protected static function encodeDestinations(array $destinations) {
    return implode("\n", array_map(function ($item) {
      return $item['label'] . '|' . $item['feed_id'];
    }, $destinations));
  }

}
