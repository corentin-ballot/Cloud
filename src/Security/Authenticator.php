<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\BasicAuthenticationEntryPoint;

class Authenticator extends BasicAuthenticationEntryPoint
{
    public function __construct(){}

    public function start(Request $request, AuthenticationException $authException = null)
    {
        if (null !== $qs = $request->getQueryString()) { $qs = '?'.$qs; }
        $source="https://".$request->getHttpHost().$request->getBaseUrl().$request->getPathInfo().$qs;
        $response = new RedirectResponse($_SERVER['LOGIN_URL']."?source=".urlencode($source), 307);

        return $response;
    }
}
