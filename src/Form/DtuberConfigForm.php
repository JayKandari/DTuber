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
   * Check is item empty.
   */
  public function isEmpty($item) {
    return ($item === NULL || $item === '');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    global $base_url;
    // Get config.
    $config = $this->config('dtuber.settings');
    $form['authentication'] = array(
      '#type' => 'details',
      '#title' => 'Google Authentication',
      '#description' => $this->t('DTuber requires Google Account authentication to upload videos to YouTube.'),
      '#open' => TRUE,
    );
    $form['credentials'] = array(
      '#type' => 'details',
      '#title' => 'Credentials',
      '#open' => TRUE,
    );
    $hasAccessToken = $config->get('access_token');
    if ($hasAccessToken) {
      $authorized = '<p>Status: <strong>Authorized</strong>.</p><p><a class="button" href="' . $base_url . '/dtuber/revoke">Revoke Current Authentication</a></a>';
      $form['authentication']['dtuber_access_token'] = array(
        '#type' => 'markup',
        '#markup' => $authorized,
      );

      // Channel Details.
      $form['channel'] = array(
        '#type' => 'details',
        '#title' => 'YouTube Details',
        '#open' => TRUE,
      );

      $channelSettings = $this->dtuberYtService->youTubeAccount();

      $details = '<p><strong>Channel Name:</strong> ' . $channelSettings->title . '.</p>';
      $details .= '<p><strong>Channel Description:</strong> ' . $channelSettings->description . '.</p>';
      $details .= '<p><strong>Channel Keywords:</strong> ' . $channelSettings->keywords . '.</p>';
      $form['channel']['details'] = array(
        '#type' => 'markup',
        '#markup' => $details,
      );

      $form['authentication']['#open'] = FALSE;
      $form['credentials']['#open'] = FALSE;

    }
    else {
      $hasClientIds = $config->get('client_id');
      $hasClientSecret = $config->get('client_secret');
      $hasRedirectUri = $config->get('redirect_uri');

      if (!$this->isEmpty($hasClientIds) && !$this->isEmpty($hasClientSecret) && !$this->isEmpty($hasRedirectUri)) {
        $auth_url = $this->dtuberYtService->getAuthUrl();
        $unauthorized = '<p>Status: <strong>Unauthorized</strong>.</p><p><a class="button" href="' . $auth_url . '">Authorize</a></p>';
        $form['authentication']['authorize'] = array(
          '#type' => 'markup',
          '#markup' => $unauthorized,
        );
      }
      else {
        $status = '<p>Status: <strong>Credentials required</strong>.</p><p>Provide values for Client ID, Secret and Redirect Uri</p>';
        $form['authentication']['authorize'] = array(
          '#type' => 'markup',
          '#markup' => $status,
        );
      }
    }

    $form['credentials']['dtuber_client_id'] = array(
      '#type' => 'textfield',
      '#title' => 'Client ID',
      '#default_value' => $config->get('client_id'),
      '#description' => $this->t('Set Client Id'),
      '#disabled' => $hasAccessToken,
    );

    $form['credentials']['dtuber_client_secret'] = array(
      '#type' => 'textfield',
      '#title' => 'Client Secret',
      '#default_value' => $config->get('client_secret'),
      '#description' => $this->t('Set Client Secret'),
      '#disabled' => $hasAccessToken,
    );

    $redirect_uri = $base_url . '/dtuber/authorize';
    $form['credentials']['dtuber_redirect_uri'] = array(
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
    );

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
