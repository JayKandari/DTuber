<?php

namespace Drupal\dtuber\Controller;
use Drupal\Core\Controller\ControllerBase;
// use Symfony\Component\DependencyInjection\ContainerInterface;
use Google\Service\YouTube;
use Symfony\Component\HttpFoundation;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;

class DTuberController extends ControllerBase {

	/**
	 * {@inhertidocs}
	 */
	public function content(){

		// $service = \Drupal::service('youtube_service');
		// $results = $service->getDemoValue();
		// $result = $results->getItems();
		// kint($result);
		// $ids = array();
		// foreach($result as $item) {
		// 	array_push($ids, $item->getId());
		// }
		// $html = '';
		// try{
		// 	$config = \Drupal::config('dtuber.settings');
		// 	function get($q){
		// 		return \Drupal::request()->query->get($q);
		// 	}

		// 	$client = new \Google_Client();
		// 	$client->setApplicationName('BOB-testApp');

		// 	$client->setClientId($config->get('client_id'));
		// 	$client->setClientSecret($config->get('client_secret'));
		// 	$client->setScopes('https://www.googleapis.com/auth/youtube');
		// 	$client->setRedirectUri($config->get('redirect_uri'));

		// 	// -----------------
		// 	// drupal_set_message("Message: ".get('code'));
			
		// 	$stored_token = $config->get('access_token');
			// kint();

			// if($stored_token) {
			// 	// token is stored in db. 
			// 	$client->setAccessToken($stored_token);
			// 	$authorized = TRUE;
			// }else if(get('code')){
			// 	// that means returning after user "Allowd" this request.
			// 	$client->authenticate(get('code'));
			// 	// store token into database.
			// 	$config = \Drupal::service('config.factory')->getEditable('dtuber.settings');
			// 	$config->set('access_token', $client->getAccessToken())->save();
			// 	$authorized = TRUE;
			// }else {
			// 	$authorized = FALSE;
			// }

		// 	if(!$authorized){
		// 		$auth_url = $client->createAuthUrl();
		// 		$html .= '<p><strong>Unauthorized: </strong>Click <a href="'. $auth_url . '">Here</a> to Authorize.</p>';
		// 	}else{
		// 		$html .= '<p><strong>Client Authorized: </strong></p>';
		// 		$youtube = new \Google_Service_YouTube($client);
		// 		// $searchResult = $youtube->search->listSearch('id,snippet', array('q'=> 'india', 'maxResults'=>5));	
		// 		// $ids = array();
		// 		// foreach($searchResult->getItems() as $item) {
		// 		// 	array_push($ids, $item->getId());
		// 		// }
		// 		// kint($ids);
		// 		// kint($searchResult->getItems());
		// 		//  ====================================
		// 		// Everything here now is working... lets do a simple file upload. :) 
		// 		$videoPath = "./videos.mp4";

		// 	    // Create a snippet with title, description, tags and category ID
		// 	    // Create an asset resource and set its snippet metadata and type.
		// 	    // This example sets the video's title, description, keyword tags, and
		// 	    // video category.
		// 	    $snippet = new \Google_Service_YouTube_VideoSnippet();
		// 	    $cur_time = date('h:i a, M-d-Y', time());
		// 	    $snippet->setTitle("Test title - ". $cur_time);
		// 	    $snippet->setDescription("Test description lorem ipsum... ". $cur_time);
		// 	    $snippet->setTags(array("tag1", "tag2"));

		// 	    // Numeric video category. See
		// 	    // https://developers.google.com/youtube/v3/docs/videoCategories/list
		// 	    $snippet->setCategoryId("22");

		// 	    // Set the video's status to "public". Valid statuses are "public",
		// 	    // "private" and "unlisted".
		// 	    $status = new \Google_Service_YouTube_VideoStatus();
		// 	    $status->privacyStatus = "public";

		// 	    // Associate the snippet and status objects with a new video resource.
		// 	    $video = new \Google_Service_YouTube_Video();
		// 	    $video->setSnippet($snippet);
		// 	    $video->setStatus($status);

		// 	    // Specify the size of each chunk of data, in bytes. Set a higher value for
		// 	    // reliable connection as fewer chunks lead to faster uploads. Set a lower
		// 	    // value for better recovery on less reliable connections.
		// 	    $chunkSizeBytes = 1 * 1024 * 1024;

		// 	    // Setting the defer flag to true tells the client to return a request which can be called
		// 	    // with ->execute(); instead of making the API call immediately.
		// 	    $client->setDefer(true);

		// 	    // Create a request for the API's videos.insert method to create and upload the video.
		// 	    $insertRequest = $youtube->videos->insert("status,snippet", $video);

		// 	    // Create a MediaFileUpload object for resumable uploads.
		// 	    $media = new \Google_Http_MediaFileUpload(
		// 	        $client,
		// 	        $insertRequest,
		// 	        'video/*',
		// 	        null,
		// 	        true,
		// 	        $chunkSizeBytes
		// 	    );
		// 	    $media->setFileSize(filesize($videoPath));


		// 	    // Read the media file and upload it chunk by chunk.
		// 	    $status = false;
		// 	    $handle = fopen($videoPath, "rb");
		// 	    while (!$status && !feof($handle)) {
		// 	      $chunk = fread($handle, $chunkSizeBytes);
		// 	      $status = $media->nextChunk($chunk);
		// 	    }

		// 	    fclose($handle);

		// 	    // If you want to make other calls after the file upload, set setDefer back to false
		// 	    $client->setDefer(false);


		// 	    $html .= "<h3>Video Uploaded</h3><ul>";
		// 	    $html .= sprintf('<li>%s (%s)</li>',
		// 	        $status['snippet']['title'],
		// 	        $status['id']);

		// 	    $html .= '</ul>';

		// 	}
		// 	// -------------------
		// 	// get configs for this module. 


		// }catch(\Exception $e){
		// 	// kint($e);
		// 	drupal_set_message('Exception Occured - ' . $e->getMessage(), 'error');
		// }

		// $options = array(
		// 	'authorized' => $authorized,
		// 	'auth_url' => $auth_url,
		// );

		$myservice = \Drupal::service('youtube_service');

		$html = $myservice->uploadVideo();
		
		return array(
			'#markup' => $html,
			// '#theme' => 'dtuber_youtube_service_example',
			// '#options' => $options,
		);
	}

	public function revoke(){
		$config = \Drupal::service('config.factory')->getEditable('dtuber.settings');
		$config->set('access_token', null)->save();

		$myservice = \Drupal::service('youtube_service');
		$myservice->revokeAuth();

		drupal_set_message('Authentication Revoked. Need re authorization from Google.');

		return new RedirectResponse(\Drupal::url('dtuber.configform'));
  		// $response->send();
	}

	public function authorize(){
		// handles dtuber/authorize authorization from google.
		$code = \Drupal::request()->query->get('code');
		$error = \Drupal::request()->query->get('error');
		if($code){
			$myservice = \Drupal::service('youtube_service');
			$access_token = $myservice->authorizeClient($code);
			// store token into database.
			$config = \Drupal::service('config.factory')->getEditable('dtuber.settings');
			$config->set('access_token', $access_token)->save();
			drupal_set_message('New Token Authorized!! ');
		}else if($error == 'access_denied'){
			drupal_set_message('Access Rejected. Kindly Allow Application to use your account.', 'error');
		}
		// redirect to configform.
		return new RedirectResponse(\Drupal::url('dtuber.configform'));

	}
}