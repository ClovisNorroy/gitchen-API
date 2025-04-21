<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AuthController extends AbstractController
{
    public function logout(): Response
    {
        $response = new Response('logout successful', 204);
        
        $response->headers->clearCookie(
            'BEARER', // Cookie name
            '/',      // Path
            null,     // Domain (null means current)
            true,     // Secure
            true,     // HttpOnly
            false,    // Raw
            'strict'  // SameSite
        );

        return $response;
    }
}