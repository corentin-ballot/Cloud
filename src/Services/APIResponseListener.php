<?php

namespace App\Services;
 
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
 
class APIResponseListener
{
    public function onKernelResponse(FilterResponseEvent $event)
    {   
        $request = $event->getRequest();
        if(preg_match('/^\/api.*/', $request->getPathInfo())) {
            // Add 'Access-Control-Allow-Origin: *' header to API routes
            $event->getResponse()->headers->set('Access-Control-Allow-Origin', "https://".$request->getHost());
            $event->getResponse()->headers->set('Access-Control-Allow-Credentials', 'true');
            $event->getResponse()->headers->set('Vary', 'Origin');
        }                                                                                                                                                                                                                         
    }   
}