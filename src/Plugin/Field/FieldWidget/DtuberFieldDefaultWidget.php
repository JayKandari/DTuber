<?php
/**
 * @file
 * Contains \Drupal\dtuber\Plugin\Field\FieldWidget\DtuberFieldDefaultWidget.
 */
namespace Drupal\dtuber\Plugin\Field\FieldWidget;

use \Drupal;
use \Drupal\Core\Field\FieldItemListInterface;
use \Drupal\Core\FIeld\WidgetBase;
use \Drupal\Core\Form\FormStateInterface;
use \Drupal\Core\Routing\RouteMatchInterface;

/**
 * Plugin implementation of the 'dtuber_field_default_widget' widget.
 *
 * @FieldWidget(
 *   id = "dtuber_field_default_widget",
 *   label = @Translation("Default Dtuber Widget"),
 *   field_types = {
 *       "dtuber_field"
 *   }
 * )
 */
class DtuberFieldDefaultWidget extends WidgetBase
{
    /**
     * {@inheritdocs}
     */
    public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) 
    {
        // $dtuber_fields = \Drupal::service('dtuber_field_manager')->getList();
        // kint($items);
        $config = \Drupal::config('dtuber.settings');

        $item = $items[$delta];
        $fid = $item->getValue('fid');
        $element['fid'] = [
         '#type' => 'managed_file',
         '#title' => $this->t('Upload Video'),
         '#description' => $this->t('this video will get uploaded to YouTube'),
         '#upload_location' => 'public://dtuber_files',
         // '#default_value' => (!$item->getValue('fid'))? [13] : NULL,
         // '#default_value' => ($item->isEmpty())? NULL : [$item->getValue('fid')['fid']],
         '#default_value' => null,
         '#upload_validators' => array(
          'file_validate_extensions' => (!empty($config->get('allowed_exts')))? $config->get('allowed_exts') : array('mov mp4 avi mkv 3gp '),
          // Pass the maximum file size in bytes
          // 'file_validate_size' => array(MAX_FILE_SIZE*1024*1024),
         ),
        ];
        $element['fid_revision'] = [
            '#type' => 'hidden',
            '#default_value' => null,
            // '#default_value' => (!$item->isEmpty() )? [$item->fid] : NULL,
        ];

        // drupal_set_message('Hello ::'.$item->getValue('fid')['fid']);
        // kint($item->get('fid'));
        if(!$item->isEmpty()) {
            // when field is NOT empty. Set a default value for fid.
            $element['fid']['#default_value'] = [$item->get('fid')->getValue()];
            $element['fid_revision']['#default_value'] = [$item->get('fid')->getValue()];
        }


        $element['yt_uploaded'] = [
         '#type'  => 'hidden',
         '#default_value' => ($item->get('yt_uploaded')->getValue())? $item->get('yt_uploaded')->getValue() : 0,
        ];

        $element['yt_videoid'] = [
         '#type'  => 'hidden',
         '#default_value' => ($item->get('yt_videoid')->getValue())? $item->get('yt_videoid')->getValue() : '',
        ];

        $element += [
         '#type' => 'fieldset',
         '#description' => $this->t('DTuber Field: This video will get upload to YouTube'),
        ];

        return $element;
    }

    /**
     * {@inheritdoc}
     */
    public function massageFormValues(array $values, array $form, FormStateInterface $form_state)
    {
        // Help From: http://stackoverflow.com/questions/38996037/drupal-8-field-plugin-with-field-type-managed-file
        foreach ($values as &$value) {
            if (count($value['fid'])) {
                foreach ($value['fid'] as $fid) {
                    $value['fid'] = $fid;
                }
            } else {
                $value['fid'] = $value['fid_revision'] !== '' ? $value['fid_revision'] : null;
            }

        }


        return $values;
    }

    /**
     * {@inheritdocs}
     */
    // public static function defaultSettings(){
    // 	return array(
    // 		'size' => '60',
    // 		'placeholder' => '',
    // 	) + parent::defaultSettings();
    // }
}

/**
 * Implements hook_help().
 */
// function dtuber_help($route_name, RouteMatchInterface $route_match) {
// 	switch ($route_name) {
// 		case 'help.page.dtuber':
// 			$output = '';
// 			$output .= '&lt;h3&gt;'. t('DTuber') . '&lt;/h3&gt;';
// 			$output .= '&lt;p&gt;'. t('DTuber module creates a Dtuber Field, which uploads video to YouTube & provide a YouTube play to play same video. ') . '&lt;/p&gt;';
// 			return $output;
// 	}
// }
