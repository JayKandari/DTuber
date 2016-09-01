<?php
/**
* @file
* Contains \Drupal\dtuber\Plugin\Field\FieldType\DtuberField.
*/

namespace Drupal\dtuber\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\File;

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
				// 'value' => array(
				// 	'type' => 'char',
				// 	'length' => 225,
				// 	'not null' => FALSE,
				// ),
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
		$properties['yt_uploaded'] = DataDefinition::create('integer')->setLabel(t('Video uploaded to YouTube? 1=y/0=n'));
		$properties['yt_videoid'] = DataDefinition::create('string')->setLabel(t('YouTube Video ID'));
		// $properties['value'] = DataDefinition::create('string')->setLabel(t('Sample Value'));

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
		// kint([ 'fid' => $fid, 'vid' =>$vid,]);
		return ($fid === NULL && ($vid === '' || $vid === NULL));
		// return ($vid === '' || $vid === NULL);
		// return ($fid === NULL);
	}

	/**
	 * {@inheritdocs}
	 */
	// public function getConstraints() {
	// 	$constraint_manager = \Drupal::typedDataManager()->getValidationConstraintManager();
	// 	$constriants = parent::getConstraints();
	// 	$constraints[] = $constraint_manager->create('ComplexData', array(
	// 		'value' => array(
	// 			'Length' => array(
	// 				'max' => 255,
	// 				'maxMessage' => $this->t('%name: the dtuber field value may not be longer than @max characters.', array('%name' => $this->getFieldDefinition()->getLabel(), '@max'=>255)),
	// 			),
	// 		),
	// 	));

	// 	return $constraints;
	// }

	/**
	 * {@inheritdocs}
	 */
	public function postSave($data){
		// $_SESSION['message'] = $this;
		// drupal_set_message("DtuberField->postSave() Fired. ");
		// make file permananet.
		/* Fetch the array of the file stored temporarily in database */ 

		// $file = $this->get('fid')->getValue();

		// /* Load the object of the file by it's fid */ 
		// $file = File::load( $file[0] );
		// // $file = file_load($file[0]);

		// /* Set the status flag permanent of the file object */
		// $file->setPermanent();

		// /* Save the file in database */
		// $file->save();

		// send file to Youtube. 
		// $file = $this->getValue('fid');
		$entity = $this->getEntity();
		$node = \Drupal\node\Entity\Node::load($entity->id());
		$field_id = $this->getParent()->getName();
		// $field_val = $entity->{$field_id}->getValue()[0];
		$field_val = $node->get($field_id)->getValue()[0];
		// $_SESSION['message'] = [ 'value' => $field_val, 'entity'=>$entity];

		$file = $field_val['fid'];
		$file = file_load($file);
		// if($this->isEmpty()) {
		if($field_val && isset($file) ) {
			// If file is there... 
			// print_r($field_val);
			
			// // // drupal_set_message('File ID'. $file[0]);
			
			$path = file_create_url($file->getFileUri());
			// // // drupal_set_message('file: '. $path);
			// // // exit();
			global $base_url;
			$dtuberYouTubeService = \Drupal::service('dtuber_youtube_service');
			$options = array(
				'path' => str_replace($base_url, '', $path),
				'title' => $node->title->value,
				// data sources required for description & tags fields. 
				'description' => $node->title->value,
				'tags' => [],
			);

			// Check if video is already uploaded. 
			if($field_val['yt_uploaded'] != 1){
				// send a video upload request to 
				$video = $dtuberYouTubeService->uploadVideo($options);
				// $video = ['status' => 'OK'];
				if($video['status'] === 'OK') {
					// if upload successful.
					// update field
					$node->{$field_id} = [
						'fid' => $field_val['fid'], 
						// if youtube Id Isnt set.
						'yt_videoid' => $video['video_id'],
						'yt_uploaded' => 1,
					];
					// drupal_set_message('Video Uploaded to YouTube : '. $video['video_id']);
					// save entity here.
					$node->save();
					// $_SESSION['message'] = $node->get($field_id)->getValue()[0];
				}else{
					$node->{$field_id} += [
						'yt_videoid' => NULL,
						'yt_uploaded' => 0,
					];
					$node->save();
					drupal_set_message('Unable to Upload video to YouTube.'. $video['status']);
				}
			}else{
				// drupal_set_message('YouTUbe Video ID already Exists... '. $field_val['yt_videoid']);
			}
		}else{
			// When fid is empty... remove any extra video ids and uploaded flag
			$node->{$field_id} = [
				'fid' => NULL, 
				'yt_videoid' => NULL,
				'yt_uploaded' => 0,
			];
			$node->save();
			// drupal_set_message($field_id. ' Empty !!');
		}
	}
}