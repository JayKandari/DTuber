<?php
/**
 * @file
 * Contains \Drupal\dtuber\YouTubeService.
 */

namespace Drupal\dtuber;

use \Google;
use \Google\Service\YouTube;

class YouTubeService {
	protected $client_id;
	protected $client_secret;
	protected $redirect_uri;
	protected $client;

	/*
	 * returns if all the 3 credentials available. 
	 */
	public function getCredentials(){
		$client_id = $this->getConfig('client_id');
		$client_secret = $this->getConfig('client_secret');
		$redirect_uri = $this->getConfig('redirect_uri');

		if(isset($client_id) && isset($client_secret) && isset($redirect_uri)) {
			// credentials present. 
			$this->client_id = $client_id;
			$this->client_secret = $client_secret;
			$this->redirect_uri = $redirect_uri;
			return array(
				'client_id' => $client_id, 'client_secret' => $client_secret, "redirect_uri" => $redirect_uri,
			);
		}else{
			drupal_set_message('DTuber\YouTubeService: Credentials not present.', 'warning');
			return false;
		}
	}

	public function __construct() {
		
		if(! $this->getCredentials()){
			// credentials present
			return false;
		}
		// set client;
		$this->client = new \Google_Client();

		// initialize client
		// $this->initializeClient();
		$this->client->setClientId($this->client_id);
		$this->client->setClientSecret($this->client_secret);
		$this->client->setScopes('https://www.googleapis.com/auth/youtube');
		$this->client->setRedirectUri($this->redirect_uri);
		// These two are required to get refresh_token.
		$this->client->setAccessType("offline");
		$this->client->setApprovalPrompt("force");

	}

	protected function manage_tokens() {
		# Calculate token expiry 
		$token = $this->getConfig('access_token');
		$this->client->setAccessToken($token);
		# and perform required action.
		if($this->client->isAccessTokenExpired()){
			// if Token expired. 
			// we need to refresh token in this case. 
			drupal_set_message('Token Expired. Trying to refresh token', 'warning');
			// Check whether we have a refresh token or not. 
			$refreshToken = $this->getConfig('refresh_token');
			if($refreshToken != NULL){
				// if refresh token present. 
				$this->client->refreshToken($refreshToken);
				$newToken = $this->client->getAccessToken();
				$config = \Drupal::service('config.factory')->getEditable('dtuber.settings');
				$config->set('access_token', $newToken)->save();
				drupal_set_message('access_token Refreshed!');
			}else{
				// if refresh token isn't present.
				$this->client->refreshToken($refreshToken);
				$newToken = $this->client->getAccessToken();
				$config = \Drupal::service('config.factory')->getEditable('dtuber.settings');
				$config->set('access_token', $newToken)->save();
				drupal_set_message('access_token refreshed for first Time. ');
			}
		}else{
			// Good TOken. Continue..
			// drupal_set_message('Good Token');
		}
	}

	protected function getConfig($config){
		// return config from dtuber config settings.
		return \Drupal::config('dtuber.settings')->get($config);
	}

	// This code is generating error. {{ $value is passed NULL. }}
	// protected function setConfig($config, $value) {
	// 	$config = \Drupal::service('config.factory')->getEditable('dtuber.settings');
	// 	$config->set($config, $value)->save();
	// }

	public function revokeAuth(){
		/**
		 * Revoke an OAuth2 access token or refresh token. This method will revoke the current access
		 * token, if a token isn't provided.
		 * @param string|null $token The token (access token or a refresh token) that should be revoked.
		 * @return boolean Returns True if the revocation was successful, otherwise False.
		 */
		return $this->client->revokeToken();
	}

	public function getAuthUrl(){
		return $this->client->createAuthUrl();
	}

	public function authorizeClient($code) {
		$this->client->setAccessType("offline");
		$this->client->authenticate($code);

		// store token into database.
		$config = \Drupal::service('config.factory')->getEditable('dtuber.settings');
		$config->set('access_token', $this->client->getAccessToken())->save();
		$config->set('refresh_token', $this->client->getRefreshToken())->save();

		drupal_set_message('New Token Authorized!! ');

	}

	/**
	 * Uploads video to YouTube.
	 */
	public function uploadVideo($options = null){
		try{
			// Will set tokens & refresh token when necessary.
			$this->manage_tokens();

			$html = '<p><strong>Client Authorized: </strong></p>';
			$youtube = new \Google_Service_YouTube($this->client);
			if(isset($options)){
				$videoPath = './'.$options['path'];
			}else{
				$videoPath = "./videos.mp4";
			}

		    // video category.
		    $snippet = new \Google_Service_YouTube_VideoSnippet();
		    $cur_time = date('h:i a, M-d-Y', time());
		    $title = (isset($options))? $options['title'] : "Test title - ". $cur_time;
		    $snippet->setTitle($title);
		    $description = (isset($options))? $options['description'] : "Test description - ". $cur_time;
		    $snippet->setDescription($description);
		    $tags = (isset($options))? $options['tags'] : array("tag1", "tag2");
		    $snippet->setTags($tags);

		    // Numeric video category. See
		    // https://developers.google.com/youtube/v3/docs/videoCategories/list
		    $snippet->setCategoryId("22");

		    // Set the video's status to "public". Valid statuses are "public",
		    // "private" and "unlisted".
		    $status = new \Google_Service_YouTube_VideoStatus();
		    $status->privacyStatus = "public";

		    // Associate the snippet and status objects with a new video resource.
		    $video = new \Google_Service_YouTube_Video();
		    $video->setSnippet($snippet);
		    $video->setStatus($status);

		    // Specify the size of each chunk of data, in bytes. Set a higher value for
		    // reliable connection as fewer chunks lead to faster uploads. Set a lower
		    // value for better recovery on less reliable connections.
		    $chunkSizeBytes = 1 * 1024 * 1024;

		    // Setting the defer flag to true tells the client to return a request which can be called
		    // with ->execute(); instead of making the API call immediately.
		    $this->client->setDefer(true);

		    // Create a request for the API's videos.insert method to create and upload the video.
		    $insertRequest = $youtube->videos->insert("status,snippet", $video);

		    // Create a MediaFileUpload object for resumable uploads.
		    $media = new \Google_Http_MediaFileUpload(
		        $this->client,
		        $insertRequest,
		        'video/*',
		        null,
		        true,
		        $chunkSizeBytes
		    );
		    $media->setFileSize(filesize($videoPath));


		    // Read the media file and upload it chunk by chunk.
		    $status = false;
		    $handle = fopen($videoPath, "rb");
		    while (!$status && !feof($handle)) {
		      $chunk = fread($handle, $chunkSizeBytes);
		      $status = $media->nextChunk($chunk);
		    }

		    fclose($handle);

		    // If you want to make other calls after the file upload, set setDefer back to false
		    $this->client->setDefer(false);


		    $html .= "<h3>Video Uploaded</h3><ul>";
		    $html .= sprintf('<li>%s (%s)</li>',
		        $status['snippet']['title'],
		        $status['id']);

		    $html .= '</ul>';

		    $youtubelink = 'http://youtube.com/watch?v='.$status['id'];
		    drupal_set_message('Video Upload Successful. <a href="'.$youtubelink.'">Watch Video</a>');
		    # returns 
			return $html;
		}catch(\Exception $e) {
			drupal_set_message('\Drupal\dtuber\YouTube : ' . $e->getMessage(), 'error');
		}
		
	}

	/**
	 * Service to retrive YouTube Account Owner details.
	 */
	public function youTubeAccount() {
		// Will set tokens & refresh token when necessary.
		$this->manage_tokens();

		$youtube = new \Google_Service_YouTube($this->client);

		$channelsResponse = $youtube->channels->listChannels('brandingSettings', array(
	      'mine' => 'true',
	    ));

		$channel = $channelsResponse->getItems()[0]->getBrandingSettings()->getChannel();
		// kint($channelsResponse->getItems()[0]);
		$channelTitle= $channel->title;
		$channelDesc = $channel->description;

		$htmlBody = "<h3>Channel Details:</h3>";
		$htmlBody .= sprintf('<p><strong>YouTube Channel Name:</strong> %s </p>',$channelTitle);
		$htmlBody .= sprintf('<p><strong>Description:</strong> %s </p>',$channelDesc);
		global $base_url;
		$htmlBody .= '<p><a href="'.$base_url.'/dtuber/testform">Test Upload Form</a></p>';

		return $htmlBody;
	}
}