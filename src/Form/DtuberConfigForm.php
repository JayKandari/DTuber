<?php
/**
 * @file
 * Contains \Drupal\dtuber\Form\DtuberConfigForm.
 */

namespace Drupal\dtuber\Form;

use \Drupal\Core\Form\ConfigFormBase;
use \Drupal\Core\Form\FormStateInterface;


class DtuberConfigForm extends ConfigFormBase {
	/**
	 * {@inheritdoc}
	 */
	public function getFormId(){
		return 'dtuber_config_form';
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getEditableConfigNames(){
		return ['dtuber.settings'];
	}

	/**
	 * {@inheritdoc}
	 */
	public function buildForm(array $form, FormStateInterface $form_state) {
		// get config
		$config = $this->config('dtuber.settings');

		if($config->get('access_token')){
			global $base_url;
			$revoke = '<p><a href="'.$base_url. '/dtuber/revoke">Revoke Current Authentication</a></p>';
			$form['dtuber_access_token'] = array(
				'#type' => 'markup',
				// '#title' => 'Access Token',
				'#markup' => '<strong>Access Token : </strong>'. json_encode($config->get('access_token')) . $revoke,
			);
		}else{
			$myservice = \Drupal::service('youtube_service');
			$auth_url = $myservice->getAuthUrl();
			$form['authorize'] = array(
				'#type' => 'markup',
				'#markup' => '<strong>UnAuthorized : Click <a href="'.$auth_url.'">Here</a> to Authorize.</strong>',
			);
		}

		$form['dtuber_client_id'] = array(
			'#type' => 'textfield',
			'#title' => 'Client ID',
			'#default_value' => $config->get('client_id'),
		);

		$form['dtuber_client_secret'] = array(
			'#type' => 'textfield',
			'#title' => 'Client Secret',
			'#default_value' => $config->get('client_secret'),
		);

		$form['dtuber_redirect_uri'] = array(
			'#type' => 'textfield',
			'#title' => 'Redirect uri',
			'#default_value' => $config->get('redirect_uri'),
		);


		$form['dtuber_example'] = array(
			'#type' => 'textfield',
			'#title' => 'Example Field',
			'#default_value' => $config->get('example'),
		);

		return parent::buildForm($form, $form_state);
	}

	/**
	 * {@inheritdoc}
	 */
	public function submitForm(array &$form, FormStateInterface $form_state) {
		$config = \Drupal::service('config.factory')->getEditable('dtuber.settings');

		$config->set('client_id', $form_state->getValue('dtuber_client_id'))->save();

		$config->set('client_secret', $form_state->getValue('dtuber_client_secret'))->save();

		$config->set('redirect_uri', $form_state->getValue('dtuber_redirect_uri'))->save();

		$config->set('example', $form_state->getValue('dtuber_example'))->save();

		drupal_set_message('Configuration saved !!');

		parent::submitForm($form, $form_state);
	}
}
