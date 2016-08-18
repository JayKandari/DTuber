<?php

namespace Drupal\dtuber\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RemoveXFrameOptionsSubscriber implements EventSubscriberInterface {

  public function RemoveXFrameOptions(FilterResponseEvent $event) {
    $response = $event->getResponse();
    // $response->headers->remove('X-Frame-Options');
    $response->headers->set('X-Frame-Options', 'ALLOW-FROM *');
    // drupal_set_message('removing xframeoptions');
  }

  public static function getSubscribedEvents() {
    $events[KernelEvents::RESPONSE][] = array('RemoveXFrameOptions');
    return $events;
  }
}