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

		$config->set('example', $form_state->getValue('dtuber_example'))->save();

		drupal_set_message('Configuration saved !!');

		parent::submitForm($form, $form_state);
	}
}
