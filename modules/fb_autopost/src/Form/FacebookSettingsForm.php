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
  protected static function getNetworkMachineName() {
    return 'facebook';
  }

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
      list($status, $link, $fb_user) = $this->getAuthenticationState($config);
      $form['authentication'] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Authentication'),
        '#description' => $this->t('Use this section to authenticate with Facebook.'),
        'table' => [
          '#id' => 'facebook-summary',
          '#type' => 'table',
          '#rows' => [
            [
              ['header' => TRUE, 'data' => $this->t('Status')],
              $status ? $this->t('Enabled') : $this->t('Disabled'),
            ],
            [
              ['header' => TRUE, 'data' => $this->t('Action')],
              ['data' => $link],
            ],
          ],
        ],
      ];
      if (isset($fb_user)) {
        $form['authentication']['table']['#rows'][] = [
          [
            'colspan' => 2,
            'data' => $this->t('Logged in as @name.', [
              '@name' => $fb_user->getName(),
            ]),
          ],
        ];
      }

    }

    // Build the permissions form.
    $default_permissions = $config->get('permissions');
    $form['permissions'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Permissions'),
      '#description' => $this->t('Check the Facebook permissions you want to request from the user.'),
      '#tree' => TRUE,
    ];
    $permission_groups = $this->permissionsList();
    foreach ($permission_groups as $group_id => $permission_group) {
      $form['permissions'][$group_id] = [
        '#type' => 'fieldset',
        '#title' => $permission_group['#title'],
      ];
      foreach ($permission_group['permissions'] as $permission => $permission_info) {
        $form['permissions'][$group_id][$permission] = $permission_info;
        $form['permissions'][$group_id][$permission]['#type'] = 'checkbox';
        $form['permissions'][$group_id][$permission]['#default_value'] = !empty($default_permissions[$group_id]) && in_array($permission, $default_permissions[$group_id]) ? TRUE : FALSE;
      }
    }

    return $form;
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

  /**
   * Explores that state of the authentication and outputs the needed variables.
   *
   * @return array
   *   An array of output variables.
   */
  protected function getAuthenticationState($config) {
    $fb_user = NULL;
    $fb = $this->network->getSdk();
    /* @var \Facebook\Helpers\FacebookRedirectLoginHelper $helper */
    $helper = $fb->getRedirectLoginHelper();

    // Build the permission list without permission groups.
    $selected_permissions = [];
    $permission_groups = $config->get('permissions') ?: [];
    foreach ($permission_groups as $permission_list) {
      $selected_permissions = array_merge($permission_list, $selected_permissions);
    }
    $url_object = Url::fromRoute('fb_autopost.login_callback', [], [
      'absolute' => TRUE,
    ]);
    $permissions_summary = [
      'permissions' => [
        '#theme' => 'item_list',
        '#items' => $selected_permissions,
        '#title' => $this->t('Facebook permissions'),
      ],
      '#attached' => [
        'library' => ['fb_autopost/fb_autopost.admin'],
      ],
    ];
    $status = FALSE;
    if ($access_token = \Drupal::state()->get('fb_autopost.access_token')) {
      $url = $helper->getLogoutUrl($access_token, $url_object->toString());
      $text = $this->t('Logout');
      try {
        $response = $fb->get('/me', $access_token);
        $fb_user = $response->getGraphUser();
        $status = TRUE;
      }
      catch (FacebookSDKException $e) {
        // If there is an exception it may mean that the user revoked access
        // to the app in the Facebook UI.
        $url = $helper->getLoginUrl($url_object->toString(), $selected_permissions);
        $text = $this->t('Login');
      }
      $now = new \DateTime();
      $period = $now->diff($access_token->getExpiresAt());
      $link = [
        '#type' => 'link',
        '#url' => Url::fromUri($url),
        '#title' => $text,
        'expiration' => [
          '#type' => 'html_tag',
          '#tag' => 'span',
          '#attributes' => ['class' => 'additional-info'],
          '#value' => $this->t('expires in @time days', ['@time' => $period->format('%a')]),
        ],
      ] + $permissions_summary;
      return array($status, $link, $fb_user);
    }
    else {
      $url = $helper->getLoginUrl($url_object->toString(), $selected_permissions);
      $text = $this->t('Login');
      $link = [
        '#type' => 'link',
        '#url' => Url::fromUri($url),
        '#title' => $text,
      ] + $permissions_summary;
      return array($status, $link, $fb_user);
    }
  }

  /**
   * Gets a list of sections with permissions.
   *
   * @return array
   *   An structured array to build a set of checkboxes.
   */
  protected function permissionsList() {
    return array(
      'public_profile' => array(
        '#title' => $this->t('Public Profile'),
        'permissions' => array(
          'public_profile' => array(
            '#title' => $this->t('Public Profile.'),
            '#description' => $this->t("Gives access to a subset of a person's public profile. Required when requesting permissions on iOS and Android."),
          ),
        ),
      ),
      'friends' => array(
        '#title' => $this->t('Friends'),
        'permissions' => array(
          'user_friends' => array(
            '#title' => $this->t('Friends.'),
            '#description' => $this->t("This permission grants the app permission to read a list of this person's friends who also use your app. If any of this person's friends have chosen not to share their list of friends with your app, they will not show up in the list of friends for this person. Both people must have enable the user_friends permission enabled for a friend to show up in either friend list."),
          ),
        ),
      ),
      'email_permissions' => array(
        '#title' => $this->t('Email Permissions'),
        'permissions' => array(
          'email' => array(
            '#title' => $this->t('Email.'),
            '#description' => $this->t("Provides access to the user's primary email address in the email property. Do not spam users. Your use of email must comply both with Facebook policies and with the CAN-SPAM Act."),
          ),
        ),
      ),
      'extended_profile_properties' => array(
        '#title' => $this->t('Extended Profile Properties'),
        'permissions' => array(
          'user_about_me' => array(
            '#title' => $this->t('About Me.'),
            '#description' => $this->t("This permission only provides access to the about property on the user node, which is the value that's contained in the 'About Me' field in their Facebook profile. This permission is not needed to access the person's public profile information such as their name, gender or age range. Use the public_profile permission for those values."),
          ),
          'user_activities' => array(
            '#title' => $this->t('Activities.'),
            '#description' => $this->t("Provides access to the user's list of activities as the activities connection"),
          ),
          'user_birthday' => array(
            '#title' => $this->t('Birthday.'),
            '#description' => $this->t("Provides access to the birthday with year as the birthday property. Note that your app may determine if a user is 'old enough' to use an app by obtaining the age_range public profile property"),
          ),
          'user_education_history' => array(
            '#title' => $this->t('Education History.'),
            '#description' => $this->t("Provides access to education history as the education property"),
          ),
          'user_events' => array(
            '#title' => $this->t('Events'),
            '#description' => $this->t("Provides access to the list of events the user is attending as the events connection"),
          ),
          'user_groups' => array(
            '#title' => $this->t('Groups.'),
            '#description' => $this->t("Provides access to the list of groups the user is a member of as the groups connection. This permission is reserved for apps that replicate the Facebook client on platforms that don’t have a native client. It may only be used to provide people with access to this content. It make take up to 14 days for your app to be reviewed."),
          ),
          'user_hometown' => array(
            '#title' => $this->t('Hometown.'),
            '#description' => $this->t("Provides access to the user's hometown in the hometown property"),
          ),
          'user_interests' => array(
            '#title' => $this->t('Interests.'),
            '#description' => $this->t("Provides access to the user's list of interests as the interests connection"),
          ),
          'user_likes' => array(
            '#title' => $this->t('Likes.'),
            '#description' => $this->t("Provides access to the list of all of the pages the user has liked as the likes connection"),
          ),
          'user_location' => array(
            '#title' => $this->t('Location.'),
            '#description' => $this->t("Provides access to the user's current city as the location property"),
          ),
          'user_photos' => array(
            '#title' => $this->t('Photos.'),
            '#description' => $this->t("Provides access to the photos the user has uploaded, and photos the user has been tagged in"),
          ),
          'user_relationships' => array(
            '#title' => $this->t('Relationships.'),
            '#description' => $this->t("Provides access to the user's family and personal relationships and relationship status"),
          ),
          'user_relationship_details' => array(
            '#title' => $this->t('Relationship Details.'),
            '#description' => $this->t("Provides access to the user's relationship preferences"),
          ),
          'user_religion_politics' => array(
            '#title' => $this->t('Religion & Politics.'),
            '#description' => $this->t("Provides access to the user's religious and political affiliations"),
          ),
          'user_status' => array(
            '#title' => $this->t('Status'),
            '#description' => $this->t("Provides access to the user's status messages and checkins. Please see the documentation for the location_post table for information on how this permission may affect retrieval of information about the locations associated with posts."),
          ),
          'user_tagged_places' => array(
            '#title' => $this->t('Tagged Places.'),
            '#description' => $this->t("Provides access to posts, photos and checkins that the person has been tagged in."),
          ),
          'user_videos' => array(
            '#title' => $this->t('Videos.'),
            '#description' => $this->t("Provides access to the videos the user has uploaded, and videos the user has been tagged in"),
          ),
          'user_website' => array(
            '#title' => $this->t('Website'),
            '#description' => $this->t("Provides access to the user's web site URL"),
          ),
          'user_work_history' => array(
            '#title' => $this->t('Work History.'),
            '#description' => $this->t("Provides access to work history as the work property"),
          ),
        ),
      ),
      'extended_permissions' => array(
        '#title' => $this->t('Extended Permissions'),
        'permissions' => array(
          'read_friendlists' => array(
            '#title' => $this->t('Read Friendlists.'),
            '#description' => $this->t("Provides access to the names of the custom lists a person has created to manage their friends. This is useful for creating a custom audience selection tool when sharing content. Note: to access the person's friends you should request the user_friends permission, not this permission."),
          ),
          'read_insights' => array(
            '#title' => $this->t('Read Insights.'),
            '#description' => $this->t("Provides read access to Facebook Insights data for pages, applications, and domains the user owns."),
          ),
          'read_mailbox' => array(
            '#title' => $this->t('Read Mailbox.'),
            '#description' => $this->t("Provides the ability to read from a user's Facebook Inbox. This permission is reserved for apps that replicate the Facebook client on platforms that don’t have a native client. It may only be used to provide people with access to this content. It may take up to 14 days for your app to be reviewed."),
          ),
          'read_stream' => array(
            '#title' => $this->t('Read Stream.'),
            '#description' => $this->t("Provides access to all the posts in the person's news feed. This permission is reserved for apps that replicate the Facebook client on platforms that don’t have a native client. It may only be used to provide people with access to this content. It may take up to 14 days for your app to be reviewed."),
          ),
        ),
      ),
      'extended_permissions_publish' => array(
        '#title' => $this->t('Extended Permissions - Publish'),
        'permissions' => array(
          'manage_notifications' => array(
            '#title' => $this->t('Manage Notifications.'),
            '#description' => $this->t("Enables your app to read notifications and mark them as read. Intended usage: This permission should be used to let users read and act on their notifications; it should not be used to for the purposes of modeling user behavior or data mining. Apps that misuse this permission may be banned from requesting it. This permissions is reserved for apps that replicate the Facebook client on platforms that don’t have a native client. It may only be used to provide people with access to this content. It may take up 14 days for your app to be reviewed."),
          ),
          'publish_actions' => array(
            '#title' => $this->t('Publish Actions.'),
            '#description' => $this->t("Enables your app to post content, comments and likes to a user's stream and requires extra permissions from a person using your app. Because this permission lets you publish on behalf of a user please read the Platform Policies to ensure you understand how to properly use this permission. Note, you do not need to request the publish_actions permission in order to use the Feed Dialog, the Requests Dialog or the Send Dialog. Facebook used to have a permission called publish_stream. publish_actions replaces it in all cases. This permission also replaces photo_upload."),
          ),
          'rsvp_event' => array(
            '#title' => $this->t('RSVP Event.'),
            '#description' => $this->t("Enables your application to RSVP to events on the user's behalf"),
          ),
        ),
      ),
      'open_graph_permissions' => array(
        '#title' => $this->t('Open Graph Permissions'),
        'permissions' => array(
          'publish_actions' => array(
            '#title' => $this->t('Publish Actions.'),
            '#description' => $this->t("Allows your app to publish to the Open Graph using Built-in Actions, Achievements, Scores, or Custom Actions. Your app can also publish other activity which is detailed in the Publishing Permissions doc."),
          ),
          'user_actions.books' => array(
            '#title' => $this->t('User Actions: Books.'),
            '#description' => $this->t("Allows you to retrieve the actions published by all applications using the built-in books actions."),
          ),
          'user_actions.fitness' => array(
            '#title' => $this->t('User Actions: Fitness.'),
            '#description' => $this->t("Allows you to retrieve the actions published by all applications using the built-in fitness actions."),
          ),
          'user_actions.music' => array(
            '#title' => $this->t('User Actions: Music.'),
            '#description' => $this->t("Allows you to retrieve the actions published by all applications using the built-in music actions."),
          ),
          'user_actions.news' => array(
            '#title' => $this->t('User Actions: News.'),
            '#description' => $this->t("Allows you to retrieve the actions published by all applications using the built-in news.reads action."),
          ),
          'user_actions.video' => array(
            '#title' => $this->t('User Actions: Video.'),
            '#description' => $this->t("Allows you to retrieve the actions published by all applications using the built-in video.watches action."),
          ),
          'user_actions:APP_NAMESPACE' => array(
            '#title' => $this->t('User Actions: APP_NAMESPACE.'),
            '#description' => $this->t("Allows you to retrieve the actions published by another application as specified by the app namespace. For example, to request the ability to retrieve the actions published by an app which has the namespace awesomeapp, prompt the user for the user_actions: awesomeapp permission."),
          ),
        ),
      ),
      'pages' => array(
        '#title' => $this->t('Pages'),
        'permissions' => array(
          'manage_pages' => array(
            '#title' => $this->t('Manage Pages.'),
            '#description' => $this->t("Enables your application to retrieve access_tokens for Pages and Applications that the user administrates. The access tokens can be queried by calling /{user_id}/accounts via the Graph API. See here for generating long-lived Page access tokens that do not expire after 60 days."),
          ),
          'publish_pages' => array(
            '#title' => $this->t('Publish Pages.'),
            '#description' => $this->t("This permission is required to publish as a Page as of API version 2.3. Previously publish_actions was required."),
          ),
          'read_page_mailboxes' => array(
            '#title' => $this->t('Read Page Mailboxes.'),
            '#description' => $this->t("Enables your application to retrieve the Facebook Messages conversations for Pages. You must use a Page Access Token to do this. Conversations are retrieved by calling /{page_id}/conversations via the Graph API."),
          ),
        ),
      ),
    );
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

    // Build the list of selected permissions.
    $permissions = array_map('array_filter', $form_state->getValue('permissions'));
    $permissions = array_map('array_keys', $permissions);
    $permissions = array_filter($permissions);
    $config->set('permissions', $permissions);

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
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['fb_autopost.settings'];
  }

}
