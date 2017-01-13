<?php

namespace Drupal\dtuber\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\GetResponseEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;

class DtuberSubscribers implements EventSubscriberInterface
{
    /**
     * {@inhertidocs}
     */
    static function getSubscribedEvents() 
    {
        $events[KernelEvents::REQUEST][] = array('onPageRequest');
        return $events;
    }

    /**
     * This method is called whenever the KernelEvents::REQUEST event is dispatched.
     */
    public function onPageRequest()
    {
        if(isset($_SESSION['message'])) {
            kint($_SESSION['message']);
            unset($_SESSION['message']);
            // drupal_set_message($_SESSION['message']->getValue());
        }
        // drupal_set_message('KernelEvents::REQUEST Fired.');
    }
}
