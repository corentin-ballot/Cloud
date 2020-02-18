<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;

class AccessDeniedHandler implements AccessDeniedHandlerInterface
{
    public function handle(Request $request, AccessDeniedException $accessDeniedException)
    {
        // If user is login
        if($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            // Access denied page
            return new Response("Access denied", 403);
        }
        // If user isn't fully login, redirect to login page
        else {
            // Redirect to the login page define in .env file
            return $this->redirect($_SERVER['LOGIN_URL']);
        }
    }
}