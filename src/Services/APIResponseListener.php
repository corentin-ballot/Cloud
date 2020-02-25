<?php

namespace App\Services;
 
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
 
class APIResponseListener
{
    public function onKernelResponse(FilterResponseEvent $event)
    {   
        $request = $event->getRequest();
        if(preg_match('/^\/api.*/', $request->getPathInfo())) {
            // WARNING ! Unauthaurized api call are redirected to login (other sub-domain)
            // As origin is set to null (fetch API), after login redirection (to this sub-domain), 
            // CORS fails. Set Access-Control-Allow-Origin to null work BUT DEFEATS THE PURPOSE
            // OF Access-Control-Allow-Origin header.
            // Source : https://stackoverflow.com/a/40784639
            $event->getResponse()->headers->set('Access-Control-Allow-Origin', "null");
            $event->getResponse()->headers->set('Access-Control-Allow-Credentials', 'true');
            $event->getResponse()->headers->set('Vary', 'Origin');
        }                                                                                                                                                                                                                         
    }   
}