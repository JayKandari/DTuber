<?php

namespace Drupal\dtuber\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;

/**
 * Dtuber controller for Authorization & Revoking user access.
 */
class DTuberController extends ControllerBase {

  protected $dtuberYtService;
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public function __construct($dtuberYoutube, $configFactory) {
    $this->dtuberYtService = $dtuberYoutube;
    $this->configFactory = $configFactory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static (
      $container->get('dtuber_youtube_service'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function content() {
    $html = $this->dtuberYtService->uploadVideo();

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
    $config = $this->configFactory->getEditable('dtuber.settings');
    $config->set('access_token', NULL)->save();

    $this->dtuberYtService->revokeAuth();

    if ($showmsg) {
      drupal_set_message($this->t('Authentication Revoked. Need re authorization from Google.'));
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
    // Authorize current request.
    $this->dtuberYtService->authorizeClient($code);

    if ($code) {
      if ($this->dtuberYtService->youTubeAccount() === FALSE) {
        drupal_get_messages();
        drupal_set_message($this->t('YouTube account not configured properly.'), 'error');
        $this->revoke(FALSE);
      }

    }
    elseif ($error == 'access_denied') {
      drupal_set_message($this->t('Access Rejected! grant application to use your account.'), 'error');
    }
    // Redirect to configform.
    return new RedirectResponse(\Drupal::url('dtuber.configform'));

  }

}
