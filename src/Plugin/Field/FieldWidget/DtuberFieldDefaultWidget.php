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
class DtuberFieldDefaultWidget extends WidgetBase {
	/**
	 * {@inheritdocs}
	 */
	public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
		// $dtuber_fields = \Drupal::service('dtuber_field_manager')->getList();
		$element['value'] = $element + array(
			'#type' => 'textfield',
			'#empty_value' => '',
			'#default_value' => (isset($items[$delta]->value)) ? $items[$delta]->value : NULL,
			'#description' => $this->t('Provide Video'),
			'#maxlength' => 255,
			'#size' => $this->getSetting('size'),
		);

		return $element;
	}

	/**
	 * {@inheritdocs}
	 */
	public static function defaultSettings(){
		return array(
			'size' => '60',
			'placeholder' => '',
		) + parent::defaultSettings();
	}
}

/**
 * Implements hook_help().
 */
function dtuber_help($route_name, RouteMatchInterface $route_match) {
	switch ($route_name) {
		case 'help.page.dtuber':
			$output = '';
			$output .= '&lt;h3&gt;'. t('DTuber') . '&lt;/h3&gt;';
			$output .= '&lt;p&gt;'. t('DTuber module creates a Dtuber Field, which uploads video to YouTube & provide a YouTube play to play same video. ') . '&lt;/p&gt;';
			return $output;
	}
}