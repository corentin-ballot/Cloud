<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\BasicAuthenticationEntryPoint;

class Authenticator extends BasicAuthenticationEntryPoint
{
    public function __construct(){}

    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new RedirectResponse($_SERVER['LOGIN_URL']."?source=".urlencode($request->getUri()));
    }
}