<?php

/**
 * @file
 * Contains \Drupal\fb_autopost\Form\FacebookSettingsForm.
 */

namespace Drupal\fb_autopost\Form;

use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class FacebookSettingsForm.
 *
 * @package Drupal\fb_autopost\Form
 */
class FacebookSettingsForm extends ConfigFormBase implements FormInterface {

  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'fb_autopost_settings';
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
    ];
    $form['app_secret'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Application Secret'),
      '#description' => $this->t('The application secret as retrieved from the Facebook APP Dashboard.'),
      '#default_value' => $config->get('app_secret') ?: '',
    ];
    $form['version'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Graph Version'),
      '#description' => $this->t('The default graph version to use. It can be overridden per request.'),
      '#default_value' => $config->get('version') ?: '',
    ];
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
    $config->save();
    parent::submitForm($form, $form_state);
  }

}
