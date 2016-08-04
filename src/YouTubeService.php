<?php
/**
 * @file
 * Contains \Drupal\dtuber\YouTubeService.
 */

namespace Drupal\dtuber;

use \Google;
use \Google\Service\YouTube;

class YouTubeService {
	protected $youtube;

	public function __construct() {

		$DEVELOPER_KEY = 'AIzaSyAEW1d3ZoWYYMktMjslLR_aa1fNwH7eZ6I';

		$client = new \Google_Client();
		$client->setDeveloperKey($DEVELOPER_KEY);
		// printData($client);

		$this->youtube = new \Google_Service_YouTube($client);
	}

	public function getDemoValue() {
		$result = $this->youtube->search->listSearch('id,snippet', array('q'=>'Jugaad', 'maxResults'=>10));
		return $result;
	}
}