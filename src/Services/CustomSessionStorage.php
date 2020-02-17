<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Storage\MetadataBag;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;

use Psr\Log\LoggerInterface;

class CustomSessionStorage extends NativeSessionStorage
{
    public function __construct(array $options = array(), $handler = null, MetadataBag $metaBag = null, RequestStack $requestStack)
    {
        $host = $requestStack->getMasterRequest()->getHost();
        $result= substr($host, strpos($host, '.'));
        
        $options['cookie_domain'] = $result;
        $options['name'] = "CBAUTHSESSID";
        $options['cookie_secure'] = 'auto';
        $options['cookie_samesite'] = 'lax';

        parent::__construct($options, $handler, $metaBag);
    }
}