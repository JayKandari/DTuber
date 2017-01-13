<?php

namespace Drupal\dtuber\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Config form for Dtuber.
 */
class DtuberConfigForm extends ConfigFormBase {

  protected $dtuberYtService;

  /**
   * {@inheritdoc}
   */
  public function __construct($dtuberYoutube) {
    $this->dtuberYtService = $dtuberYoutube;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('dtuber_youtube_service'));
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dtuber_config_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['dtuber.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    global $base_url;
    // Get config.
    $config = $this->config('dtuber.settings');
    $hasAccessToken = $config->get('access_token');
    if ($hasAccessToken) {
      $revoke = '<p><a href="' . $base_url . '/dtuber/revoke">Revoke Current Authentication</a></p>';
      $form['dtuber_access_token'] = array(
        '#type' => 'markup',
      // '#title' => 'Access Token',.
        '#markup' => '
				<p><strong>Access Token : </strong>' . json_encode($config->get('access_token')) . '</p>' .
        '<p><strong>Refresh Token : </strong>' . json_encode($config->get('refresh_token')) . '</p>' .
        $revoke,
      );

      // Channel Details.
      $form['channel_details'] = array(
        '#type' => 'markup',
        '#markup' => $this->dtuberYtService->youTubeAccount(),
      );

    }
    else {
      $hasClientIds = $config->get('client_id');
      $hasClientSecret = $config->get('client_secret');
      $hasRedirectUri = $config->get('redirect_uri');

      /**
       * Check is item empty.
       */
      function isEmpty($item) {
        return ($item === NULL || $item === '');
      }

      if (!isEmpty($hasClientIds) && !isEmpty($hasClientSecret) && !isEmpty($hasRedirectUri)) {
        $auth_url = $this->dtuberYtService->getAuthUrl();
        $form['authorize'] = array(
          '#type' => 'markup',
          '#markup' => '<strong>UnAuthorized : Click <a href="' . $auth_url . '">Here</a> to Authorize.</strong>',
        );
      }
      else {
        $form['authorize'] = array(
          '#type' => 'markup',
          '#markup' => '<strong>Provide Client Details : </strong>fill in Client id, secret & redirect uri to get auth_url',
        );
      }

    }

    $form['dtuber_client_id'] = array(
      '#type' => 'textfield',
      '#title' => 'Client ID',
      '#default_value' => $config->get('client_id'),
      '#description' => $this->t('Set Client Id'),
      '#disabled' => $hasAccessToken,
    );

    $form['dtuber_client_secret'] = array(
      '#type' => 'textfield',
      '#title' => 'Client Secret',
      '#default_value' => $config->get('client_secret'),
      '#description' => $this->t('Set Client Secret'),
      '#disabled' => $hasAccessToken,
    );

    $redirect_uri = $base_url . '/dtuber/authorize';
    $form['dtuber_redirect_uri'] = array(
      '#type' => 'textfield',
      '#title' => 'Redirect uri',
      '#default_value' => ($config->get('redirect_uri')) ? $config->get('redirect_uri') : $redirect_uri,
      '#description' => $this->t("Redirect uri should be set to '%redirect_uri'", array('%redirect_uri' => $redirect_uri)),
      '#disabled' => $hasAccessToken,
    );

    $form['dtuber_allowed_exts'] = array(
      '#type' => 'textfield',
      '#title' => 'Allowed Extensions',
      '#default_value' => $config->get('allowed_exts'),
      '#description' => $this->t('Provide allowed extensions separated by a space. Eg: "mov mp4 avi mkv 3gp".'),
     // '#disabled' => $hasAccessToken,.
    );
    // if(isset($_SESSION['file'])){
    // $file = $_SESSION['file'];
    // kint($file);
    // drupal_set_message($file->getFileUri());
    // }
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->config('dtuber.settings')
      ->set('client_id', $values['dtuber_client_id'])
      ->set('client_secret', $values['dtuber_client_secret'])
      ->set('redirect_uri', $values['dtuber_redirect_uri'])
      ->set('allowed_exts', $values['dtuber_allowed_exts'])
      ->save();
  }

}
