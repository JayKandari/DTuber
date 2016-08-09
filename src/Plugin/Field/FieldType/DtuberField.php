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
				# reference: http://drupal.stackexchange.com/questions/13211/database-schema-for-image-field
				# FID to store managed_file in db.
				'fid' => array(
					'type' => 'int',
					'not null' => FALSE,
				),
				# reference: http://drupal.stackexchange.com/questions/87962/which-type-to-use-for-checkbox-fields-in-hook-field-schema
				# file_uploaded_to_youtube : yes/no 
				'yt_uploaded' => array(
					'type' => 'int',
					'size' => 'tiny',
					'not null' => FALSE,
					'default' => 0,
				),

				# youtube_videoid : youtube VIDEO ID 
				'yt_videoid' => array(
					'type' => 'varchar',
					'length' => 255,
					'not null' => FALSE,
				),
				'value' => array(
					'type' => 'char',
					'length' => 225,
					'not null' => FALSE,
				),
			),
			// 'indexes' => array(
			// 	'fid' => array('fid'),
			// ),
		);
	}

	/**
	 * {@inheritdocs}
	 */
	public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition){
		$properties['fid'] = DataDefinition::create('integer')->setLabel(t('Upload Video'));
		$properties['yt_uploaded'] = DataDefinition::create('string')->setLabel(t('Video uploaded to YouTube?'));
		$properties['yt_videoid'] = DataDefinition::create('string')->setLabel(t('YouTube Video ID'));
		$properties['value'] = DataDefinition::create('string')->setLabel(t('Sample Value'));

		return $properties;
	}

	/**
	 * {@inheritdocs}
	 */
	public function isEmpty(){
		// $value = $this->get('value')->getValue();
		$fid = $this->get('fid')->getValue();
		$vid = $this->get('yt_videoid')->getValue();
		// If none of fid or youtube VId is present then it is considered empty.
		return ($fid === NULL && ($vid === '' || $vid === NULL));
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
	// public function postSave($data){
	// 	$_SESSION['message'] = $data;
	// 	drupal_set_message("DtuberField->postSave() Fired. ");
	// }
}