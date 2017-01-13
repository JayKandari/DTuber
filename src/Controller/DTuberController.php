<?php

namespace Drupal\dtuber\Controller;
use Drupal\Core\Controller\ControllerBase;
// use Symfony\Component\DependencyInjection\ContainerInterface;
use Google\Service\YouTube;
use Symfony\Component\HttpFoundation;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;

class DTuberController extends ControllerBase
{

    /**
     * {@inhertidocs}
     */
    public function content()
    {
        $myservice = \Drupal::service('dtuber_youtube_service');

        $html = $myservice->uploadVideo();

        return array(
         '#markup' => $html,
         // '#theme' => 'dtuber_youtube_service_example',
         // '#options' => $options,
        );
    }

    public function revoke($showmsg = true)
    {
        $config = \Drupal::service('config.factory')->getEditable('dtuber.settings');
        $config->set('access_token', null)->save();

        $myservice = \Drupal::service('dtuber_youtube_service');
        $myservice->revokeAuth();

        if($showmsg) {
            drupal_set_message('Authentication Revoked. Need re authorization from Google.');
        }
        return new RedirectResponse(\Drupal::url('dtuber.configform'));
          // $response->send();
    }

    public function authorize()
    {
        // handles dtuber/authorize authorization from google.
        $code = \Drupal::request()->query->get('code');
        $error = \Drupal::request()->query->get('error');
        if($code) {
            $myservice = \Drupal::service('dtuber_youtube_service');
            $access = $myservice->authorizeClient($code);

            if($myservice->youTubeAccount() === false) {
                drupal_get_messages();
                drupal_set_message('YouTube account not configured properly.', 'error');
                $this->revoke(false);
            }

        }else if($error == 'access_denied') {
            drupal_set_message('Access Rejected! grant application to use your account.', 'error');
        }
        // redirect to configform.
        return new RedirectResponse(\Drupal::url('dtuber.configform'));

    }
}
