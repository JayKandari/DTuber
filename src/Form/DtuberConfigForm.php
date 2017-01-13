<?php
/**
 * @file
 * Contains \Drupal\dtuber\Form\DtuberConfigForm.
 */

namespace Drupal\dtuber\Form;

use \Drupal\Core\Form\ConfigFormBase;
use \Drupal\Core\Form\FormStateInterface;
// use \Drupal\Core\Entity;

class DtuberConfigForm extends ConfigFormBase
{
    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'dtuber_config_form';
    }

    /**
     * {@inheritdoc}
     */
    protected function getEditableConfigNames()
    {
        return ['dtuber.settings'];
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) 
    {
        global $base_url;
        // get config
        $config = $this->config('dtuber.settings');
        $hasAccessToken = $config->get('access_token');
        if($hasAccessToken) {
            $revoke = '<p><a href="'.$base_url. '/dtuber/revoke">Revoke Current Authentication</a></p>';
            $form['dtuber_access_token'] = array(
             '#type' => 'markup',
             // '#title' => 'Access Token',
             '#markup' => '
				<p><strong>Access Token : </strong>'. json_encode($config->get('access_token')) . '</p>'. 
             '<p><strong>Refresh Token : </strong>'. json_encode($config->get('refresh_token')). '</p>'. 
             $revoke,
            );

            $myservice = \Drupal::service('dtuber_youtube_service');
            // Channel Details.
            $form['channel_details'] = array(
             '#type' => 'markup',
             '#markup' => $myservice->youTubeAccount(),
            );

        }else{
            $myservice = \Drupal::service('dtuber_youtube_service');
            $hasClientIds = $config->get('client_id');
            $hasClientSecret = $config->get('client_secret');
            $hasRedirectUri = $config->get('redirect_uri');
            function isEmpty($item)
            {
                return ($item === null || $item === '');
            }
            if(!isEmpty($hasClientIds) && !isEmpty($hasClientSecret) && !isEmpty($hasRedirectUri)  ) {
                $auth_url = $myservice->getAuthUrl();
                $form['authorize'] = array(
                 '#type' => 'markup',
                 '#markup' => '<strong>UnAuthorized : Click <a href="'.$auth_url.'">Here</a> to Authorize.</strong>',
                );
            }else{
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
         '#default_value' => ($config->get('redirect_uri'))? $config->get('redirect_uri') : $redirect_uri,
         '#description' => $this->t("Redirect uri should be set to '%redirect_uri'", array('%redirect_uri'=>$redirect_uri)),
         '#disabled' => $hasAccessToken,
        );

        $form['dtuber_allowed_exts'] = array(
         '#type' => 'textfield',
         '#title' => 'Allowed Extensions',
         '#default_value' => $config->get('allowed_exts'),
         '#description' => $this->t('Provide allowed extensions separated by a space. Eg: "mov mp4 avi mkv 3gp".'),
         // '#disabled' => $hasAccessToken,
        );
        // if(isset($_SESSION['file'])){
        // 	$file = $_SESSION['file'];
        // 	kint($file);
        // 	drupal_set_message($file->getFileUri());
        // }

        return parent::buildForm($form, $form_state);
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) 
    {
        $config = \Drupal::service('config.factory')->getEditable('dtuber.settings');

        $config->set('client_id', $form_state->getValue('dtuber_client_id'))->save();

        $config->set('client_secret', $form_state->getValue('dtuber_client_secret'))->save();

        $config->set('redirect_uri', $form_state->getValue('dtuber_redirect_uri'))->save();

        $config->set('allowed_exts', $form_state->getValue('dtuber_allowed_exts'))->save();

        // drupal_set_message('Configuration saved !!');

        // $fid = $form_state->getValue('test_file')[0];
        // $file = \Drupal\Core\Entity\File::load($fid);
        // $_SESSION['file'] = file_load($fid);
        // kint($file);

        parent::submitForm($form, $form_state);
    }
}
