<?php

namespace Drupal\dtuber\Form;

use \Drupal\Core\Form\FormBase;
use \Drupal\Core\Form\FormStateInterface;

/**
 * Test upload form for Dtuber.
 */
class TestUploadForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dtuber_test_upload_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['title'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Video Title'),
      '#description' => $this->t('Provide Title for this Video.'),
      '#required' => TRUE,
    );
    $form['description'] = array(
      '#type' => 'textarea',
      '#title' => 'Video Description',
    );
    $allowed_exts = array('mov mp4 avi mkv ogv webm 3gp flv');
    $form['video'] = array(
      '#type' => 'managed_file',
      '#title' => $this->t('Upload a Video'),
      '#description' => 'Allowed Extensions: ' . implode(', ', explode(' ', $allowed_exts[0])),
      '#upload_location' => 'public://dtuber_files',
      '#upload_validators' => array(
        'file_validate_extensions' => $allowed_exts,
      ),
      '#required' => TRUE,
    );
    $form['tags'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Video Tags'),
      '#description' => $this->t('Enter comma separated tags'),
    );

    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Upload to YouTube'),
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $file = $form_state->getValue('video');
    // drupal_set_message('File ID'. $file[0]);.
    $file = file_load($file[0]);
    $path = file_create_url($file->getFileUri());
    // drupal_set_message('file: '. $path);
    // exit();
    global $base_url;
    $myservice = \Drupal::service('dtuber_youtube_service');
    $options = array(
      'path' => str_replace($base_url, '', $path),
      'title' => $form_state->getValue('title'),
      'description' => $form_state->getValue('description'),
      'tags' => explode(',', $form_state->getValue('tags')),
    );
    // $html = $myservice->uploadVideo($options);
    // $_SESSION['message'] = $file;.
    drupal_set_message($this->t('Form Submitted'));

    // Return array(
    // '#markup' => 'Form Submitted',
    // );.
  }

}
