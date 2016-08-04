<?php

namespace Drupal\dtuber\Controller;
use Drupal\Core\Controller\ControllerBase;
// use Symfony\Component\DependencyInjection\ContainerInterface;

class DTuberController extends ControllerBase {

	/**
	 * {@inhertidocs}
	 */
	public function content(){

		$service = \Drupal::service('youtube_service');
		$results = $service->getDemoValue();
		kint($results);
		return array(
			'#markup' => "hello !!!",
		);
	}
}