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
        $response = new RedirectResponse($_SERVER['LOGIN_URL']."?source=".urlencode($request->getUri()));
        $host=$request->getHost();
        foreach($request->cookies->all() as $key => $value) {
            $response->headers->setCookie(Cookie::create($key, $value, 0, null, substr($host, strpos($host, '.'))));
        }

        return $response;
    }
}