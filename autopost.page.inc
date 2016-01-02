<?php

/**
 * @file
 * Contains autopost.page.inc..
 *
 * Page callback for Autopost entities.
 */

use Drupal\Core\Render\Element;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Prepares variables for Autopost templates.
 *
 * Default template: autopost.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_autopost(array &$variables) {
  // Fetch Autopost Entity Object.
  $autopost = $variables['elements']['#autopost'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}

/**
 * Prepares variables for a custom entity type creation list templates.
 *
 * Default template: autopost-content-add-list.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - content: An array of autopost-types.
 *
 * @see block_content_add_page()
 */
function template_preprocess_autopost_content_add_list(&$variables) {
  $variables['types'] = array();
  $query = \Drupal::request()->query->all();
  foreach ($variables['content'] as $type) {
    /* @var \Drupal\Core\Config\Entity\ConfigEntityInterface $type */
    $variables['types'][$type->id()] = array(
      'link' => Link::fromTextAndUrl($type->label(), new Url('entity.autopost.add_form', array(
        'autopost_type' => $type->id(),
      ), array('query' => $query))),
      'description' => array(
        '#markup' => $type->label(),
      ),
      'title' => $type->label(),
      'localized_options' => array(
        'query' => $query,
      ),
    );
  }
}
