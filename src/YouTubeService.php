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

	public function __construct() {
		
		$this->client_id = $this->getConfig('client_id');
		$this->client_secret = $this->getConfig('client_secret');
		$this->redirect_uri = $this->getConfig('redirect_uri');

		// set client;
		$this->client = new \Google_Client();

		// initialize client
		$this->initializeClient();

		# set access token
		$this->access_token();
	}

	protected function access_token(){
		// $this->client->authenticate(get('code'));
		$this->client->setAccessToken(json_encode($this->getConfig('access_token')));
	}

	protected function getConfig($config){
		// return config from dtuber config settings.
		return \Drupal::config('dtuber.settings')->get($config);
	}

	protected function initializeClient(){
		$this->client->setClientId($this->client_id);
		$this->client->setClientSecret($this->client_secret);
		$this->client->setScopes('https://www.googleapis.com/auth/youtube');
		$this->client->setRedirectUri($this->redirect_uri);
		// These two are required to get refresh_token.
		$this->client->setApprovalPrompt("force");
		$this->client->setAccessType("offline");
	}

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

	protected function refreshToken(){
		$token = $this->client->refreshToken($this->getConfig('refresh_token'));

		$config = \Drupal::service('config.factory')->getEditable('dtuber.settings');
		$config->set('access_token', $token)->save();
		$config->set('refresh_token', $this->client->getRefreshToken())->save();
		
		drupal_set_message('Token Refreshed.');
	}

	/**
	 * Uploads video to YouTube.
	 */
	public function uploadVideo(){
		// check whether token expired or not. 
			$token = $this->getConfig('access_token');
			$time_created = $token['created'];
			$t=time();
			$timediff=$t - $time_created;
			if($timediff > 3600){
				// refresh token.
				drupal_set_message('Applying for token refresh.. ');
				$this->refreshToken();
			}
		// kint($this->client);
		try{
			$html = '<p><strong>Client Authorized: </strong></p>';
			$youtube = new \Google_Service_YouTube($this->client);
			
			$videoPath = "./videos.mp4";

		    // video category.
		    $snippet = new \Google_Service_YouTube_VideoSnippet();
		    $cur_time = date('h:i a, M-d-Y', time());
		    $snippet->setTitle("Test title - ". $cur_time);
		    $snippet->setDescription("Test description lorem ipsum... ". $cur_time);
		    $snippet->setTags(array("tag1", "tag2"));

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
		}catch(\Exception $e) {
			drupal_set_message('\Drupal\dtuber\YouTube : ' . $e->getMessage(), 'error');
		}

		# returns 
		return $html;
	}
}