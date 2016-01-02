<?php

/**
 * @file
 * Contains \Drupal\social_autopost\Form\AutopostTypeForm.
 */

namespace Drupal\social_autopost\Form;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class AutopostTypeForm.
 *
 * @package Drupal\social_autopost\Form
 */
class AutopostTypeForm extends EntityForm {
  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $autopost_type = $this->entity;
    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $autopost_type->label(),
      '#description' => $this->t("Label for the Autopost type."),
      '#required' => TRUE,
    );

    $form['id'] = array(
      '#type' => 'machine_name',
      '#default_value' => $autopost_type->id(),
      '#machine_name' => array(
        'exists' => '\Drupal\social_autopost\Entity\AutopostType::load',
      ),
      '#disabled' => !$autopost_type->isNew(),
    );

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $autopost_type = $this->entity;
    $status = $autopost_type->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Autopost type.', [
          '%label' => $autopost_type->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Autopost type.', [
          '%label' => $autopost_type->label(),
        ]));
    }
    $form_state->setRedirectUrl($autopost_type->urlInfo('collection'));
  }

}
