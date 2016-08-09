<?php
/*
 * @file
 * Contains \Drupal\dtuber\Form\TestUploadForm
 */

namespace Drupal\dtuber\Form;

use \Drupal\Core\Form\FormBase;
use \Drupal\Core\Form\FormStateInterface;


class TestUploadForm extends FormBase {
	/*
	 * {@inheritdocs}
	 */
	public function getFormId(){
		return 'dtuber_test_upload_form';
	}
	/*
	 * {@inheritdocs}
	 */
	public function buildForm(array $form, FormStateInterface $form_state) {
		$form['test_video'] = array(
			'#type' => 'managed_file',
			'#title' => $this->t('Upload a Video'),
			'#upload_location' => 'public://dtuber_files',
			'#upload_validators' => array(
				'file_validate_extensions' => array('mov mp4 avi'),
			),
		);

		$form['submit'] = array(
			'#type' => 'submit',
			'#value' => $this->t('Upload to YouTube'),
		);
		return $form;
	}

	/*
	 * {@inheritdocs}
	 */
	public function submitForm(array &$form, FormStateInterface $form_state) {
		$file = $form_state->get('test_video');
		dpm($file);
		// $_SESSION['message'] = $file;
		drupal_set_message('TestUploadForm form Submitted');

		return array(
			'#markup' => 'Form Submitted',
		);
	}
}