<?php
/**
* @file
* Contains \Drupal\dtuber\Plugin\Field\FieldType\DtuberField.
*/

namespace Drupal\dtuber\Plugin\Field\FieldType;

use \Drupal\Core\Field\FieldItemBase;
use \Drupal\Core\TypedData\DataDefinition;
use \Drupal\Core\Field\FieldStorageDefinitionInterface;
use \Drupal\Core\Field\FieldItemInterface;

/**
 * Plugin implementation of 'Dtuber Field' field type
 *
 * @FieldType(
 *   id = "dtuber_field",
 *   label = @Translation("Dtuber - Upload to YouTube"),
 *   description = @Translation("Uploads videos to YouTube"),
 *   category = @Translation("Media"),
 *   default_widget = "dtuber_field_default_widget",
 *   default_formatter = "dtuber_field_default_formatter",
 * )
 */
class DtuberField extends FieldItemBase {
	/**
	 * {@inheritdocs}
	 */
	public static function schema(FieldStorageDefinitionInterface $field_definition) {
		return array(
			'columns' => array(
				'value' => array(
					'type' => 'char',
					'length' => 255,
					'not null' => FALSE,
				),
			),
			'indexes' => array(
				'value' => array('value'),
			),
		);
	}

	/**
	 * {@inheritdocs}
	 */
	public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition){
		$properties['value'] = DataDefinition::create('string')->setLabel(t('DTuber Field'));

		return $properties;
	}

	/**
	 * {@inheritdocs}
	 */
	public function isEmpty(){
		$value = $this->get('value')->getValue();
		return $value === NULL || $value === '';
	}

	/**
	 * {@inheritdocs}
	 */
	public function getConstraints() {
		$constraint_manager = \Drupal::typedDataManager()->getValidationConstraintManager();
		$constriants = parent::getConstraints();
		$constraints[] = $constraint_manager->create('ComplexData', array(
			'value' => array(
				'Length' => array(
					'max' => 255,
					'maxMessage' => $this->t('%name: the dtuber field value may not be longer than @max characters.', array('%name' => $this->getFieldDefinition()->getLabel(), '@max'=>255)),
				),
			),
		));

		return $constraints;
	}

	/**
	 * {@inheritdocs}
	 */
	public function postSave($data){
		$_SESSION['message'] = $data;
		drupal_set_message("DtuberField->postSave() Fired. ");
	}
}