<?php

namespace Drupal\dtuber;

use Drupal\Core\Render\Markup;

/**
 * YouTube Service.
 */
class YouTubeService {
  protected $clientId;
  protected $clientSecret;
  protected $redirectUri;
  protected $client;

  /**
   * Returns if all the 3 credentials available.
   */
  public function getCredentials() {
    $clientId = $this->getConfig('client_id');
    $clientSecret = $this->getConfig('client_secret');
    $redirectUri = $this->getConfig('redirect_uri');

    if (isset($clientId) && isset($clientSecret) && isset($redirectUri)) {
      // Credentials present.
      $this->client_id = $clientId;
      $this->client_secret = $clientSecret;
      $this->redirect_uri = $redirectUri;
      return array(
        'client_id' => $clientId,
        'client_secret' => $clientSecret,
        "redirect_uri" => $redirectUri,
      );
    }
    else {
      drupal_set_message(t('DTuber\YouTubeService: Credentials not present.'), 'warning');
      return FALSE;
    }
  }

  /**
   * Constructor for YouTube service.
   */
  public function __construct() {
    try {
      if (!$this->getCredentials()) {
        // Credentials present.
        return FALSE;
      }
      // Set client.
      $this->client = new \Google_Client();

      // Initialize client.
      $this->client->setClientId($this->client_id);
      $this->client->setClientSecret($this->client_secret);
      $this->client->setScopes('https://www.googleapis.com/auth/youtube');
      $this->client->setRedirectUri($this->redirect_uri);
      // These two are required to get refresh_token.
      $this->client->setAccessType("offline");
      $this->client->setApprovalPrompt("force");

    }
    catch (\Exception $e) {
      drupal_set_message(t('Dtuber Error : @e', ['@e' => $e->getMessage()]), 'error');
    }
  }

  /**
   * Manages google token.
   */
  public function manageTokens() {
    // Calculate token expiry.
    $token = $this->getConfig('access_token');
    $this->client->setAccessToken($token);
    // And perform required action.
    if ($this->client->isAccessTokenExpired()) {
      // If Token expired.
      // we need to refresh token in this case.
      // Check whether we have a refresh token or not.
      $refreshToken = $this->getConfig('refresh_token');
      if ($refreshToken != NULL) {
        // If refresh token present.
        $this->client->refreshToken($refreshToken);
        $newToken = $this->client->getAccessToken();
        $config = \Drupal::service('config.factory')->getEditable('dtuber.settings');
        $config->set('access_token', $newToken)->save();
      }
      else {
        // If refresh token isn't present.
        $this->client->refreshToken($refreshToken);
        $newToken = $this->client->getAccessToken();
        $config = \Drupal::service('config.factory')->getEditable('dtuber.settings');
        $config->set('access_token', $newToken)->save();
      }
    }
    else {
      // Good TOken. Continue.
    }
  }

  /**
   * Return config from dtuber config settings.
   */
  protected function getConfig($config) {
    return \Drupal::config('dtuber.settings')->get($config);
  }

  /**
   * Revoke an OAuth2 access token or refresh token.
   *
   * This method will revoke current access token, if a token isn't provided.
   */
  public function revokeAuth() {
    return $this->client->revokeToken();
  }

  /**
   * Gets AuthURL.
   */
  public function getAuthUrl() {
    return $this->client->createAuthUrl();
  }

  /**
   * Authorizes client.
   */
  public function authorizeClient($code) {
    $this->client->setAccessType("offline");
    $this->client->authenticate($code);

    // Store token into database.
    $config = \Drupal::service('config.factory')->getEditable('dtuber.settings');
    $config->set('access_token', $this->client->getAccessToken())->save();
    $config->set('refresh_token', $this->client->getRefreshToken())->save();

    drupal_set_message(t('New Token Authorized!!'));

  }

  /**
   * Uploads video to YouTube.
   */
  public function uploadVideo($options = NULL) {
    try {
      // Will set tokens & refresh token when necessary.
      $this->manageTokens();

      $html = '<p><strong>Client Authorized: </strong></p>';
      $youtube = new \Google_Service_YouTube($this->client);
      if (isset($options)) {
        $videoPath = '.' . urldecode($options['path']);
      }
      else {
        $videoPath = "./videos.mp4";
      }

      // Video category.
      $snippet = new \Google_Service_YouTube_VideoSnippet();
      $cur_time = date('h:i a, M-d-Y', time());
      $title = (isset($options)) ? $options['title'] : "Test title - " . $cur_time;
      $snippet->setTitle($title);
      $description = (isset($options)) ? $options['description'] : $options['title'] . ' \n-- ' . $cur_time;
      $snippet->setDescription($description);
      $tags = (isset($options)) ? $options['tags'] : explode(' ', $options['title']);
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

      // Specify the size of each chunk of data, in bytes. Set a higher value
      // for reliable connection as fewer chunks lead to faster uploads.
      // Set a lower value for better recovery on less reliable connections.
      $chunkSizeBytes = 1 * 1024 * 1024;

      // Setting the defer flag to true tells the client to return a request
      // which can be called with ->execute(); instead of making
      // the API call immediately.
      $this->client->setDefer(TRUE);

      // Create a request for the API's videos.insert method to create
      // and upload the video.
      $insertRequest = $youtube->videos->insert("status,snippet", $video);

      // Create a MediaFileUpload object for resumable uploads.
      $media = new \Google_Http_MediaFileUpload(
        $this->client,
        $insertRequest,
        'video/*',
        NULL,
        TRUE,
        $chunkSizeBytes
      );
      $media->setFileSize(filesize($videoPath));

      // Read the media file and upload it chunk by chunk.
      $status = FALSE;
      $handle = fopen($videoPath, "rb");
      while (!$status && !feof($handle)) {
        $chunk = fread($handle, $chunkSizeBytes);
        $status = $media->nextChunk($chunk);
      }

      fclose($handle);

      // If you want to make other calls after the file upload,
      // set setDefer back to false.
      $this->client->setDefer(FALSE);

      $html .= "<h3>Video Uploaded</h3><ul>";
      $html .= sprintf(
           '<li>%s (%s)</li>',
           $status['snippet']['title'],
           $status['id']
       );

      $html .= '</ul>';

      $youtubelink = 'http://youtube.com/watch?v=' . $status['id'];
      $message = 'Upload Successful. Video might take a while to process. <a href="' . $youtubelink . '" target="_Blank">Watch in YouTube</a>';
      $rendered_message = Markup::create($message);
      drupal_set_message($rendered_message);

      // Returns an array of important values;.
      return [
      // Status of OK means video successfully uploaded.
        'status' => 'OK',
        'markup' => $html,
        'video_id' => $status['id'],
      ];
    }
    catch (\Exception $e) {
      drupal_set_message(t('Dtuber Error : @e', ['@e' => $e->getMessage()]), 'error');
    }
    // By default it sends false value.
    return [
    // Status of ERROR means, video not uploaded.
      'status' => 'ERROR',
    ];
  }

  /**
   * Service to retrive YouTube Account Owner details.
   */
  public function youTubeAccount() {
    try {

      // Will set tokens & refresh token when necessary.
      $this->manageTokens();

      $youtube = new \Google_Service_YouTube($this->client);

      $channelsResponse = $youtube->channels->listChannels(
        'brandingSettings', array(
          'mine' => 'true',
        )
      );

      $branding = $channelsResponse->getItems()[0]->getBrandingSettings();
      $channel = (!empty($branding)) ? $branding->getChannel() : NULL;
      if ($channel == NULL) {
        return FALSE;
      }

      return $channel;
    }
    catch (\Exception $e) {
      drupal_set_message(t('DTuber Error : @e', ['@e' => $e->getMessage()]), 'error');
    }
  }

}
