<?php

namespace Drupal\dtuber\Controller;

use Drupal\Core\Controller\ControllerBase;
// Use Symfony\Component\DependencyInjection\ContainerInterface;.
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;

/**
 * Dtuber controller for Authorization & Revoking user access.
 */
class DTuberController extends ControllerBase {

  /**
   * {@inheritdoc}
   */
  public function content() {
    $myservice = \Drupal::service('dtuber_youtube_service');

    $html = $myservice->uploadVideo();

    return array(
      '#markup' => $html,
     // '#theme' => 'dtuber_youtube_service_example',
     // '#options' => $options,.
    );
  }

  /**
   * Revokes Google Authorization.
   */
  public function revoke($showmsg = TRUE) {
    $config = \Drupal::service('config.factory')->getEditable('dtuber.settings');
    $config->set('access_token', NULL)->save();

    $myservice = \Drupal::service('dtuber_youtube_service');
    $myservice->revokeAuth();

    if ($showmsg) {
      drupal_set_message('Authentication Revoked. Need re authorization from Google.');
    }
    return new RedirectResponse(\Drupal::url('dtuber.configform'));
    // $response->send();
  }

  /**
   * Authorizes User.
   */
  public function authorize() {
    // Handles dtuber/authorize authorization from google.
    $code = \Drupal::request()->query->get('code');
    $error = \Drupal::request()->query->get('error');
    if ($code) {
      $myservice = \Drupal::service('dtuber_youtube_service');
      $access = $myservice->authorizeClient($code);

      if ($myservice->youTubeAccount() === FALSE) {
        drupal_get_messages();
        drupal_set_message('YouTube account not configured properly.', 'error');
        $this->revoke(FALSE);
      }

    }
    elseif ($error == 'access_denied') {
      drupal_set_message('Access Rejected! grant application to use your account.', 'error');
    }
    // Redirect to configform.
    return new RedirectResponse(\Drupal::url('dtuber.configform'));

  }

}
