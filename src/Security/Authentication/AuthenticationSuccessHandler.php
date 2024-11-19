<?php
namespace App\Security\Authentication;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class AuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    private $jwtManager;

    public function __construct(JWTTokenManagerInterface $jwtManager)
    {
        $this->jwtManager = $jwtManager;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): JsonResponse
    {

        $bearerToken = $this->jwtManager->create($token->getUser());

        $userAgent = $request->headers->get('User-Agent');
        $isMobile = preg_match('/(android|iphone|ipad|ipod|blackberry|phone)/i', $userAgent);

        $cookieOptions = [
            'name' => 'BEARER',
            'value' => $bearerToken,
            'expiration' => new \DateTime('+1 month'),
            'path' => '/',
            'domain' => null,
            'secure' => true,
            'httpOnly' => true,
            'sameSite' => 'Strict',
            'raw' => false,
            'partitioned' => false
        ];



        $response = new JsonResponse(['message' => 'Login successful'], 200);

        $authCookie = new Cookie(
            $cookieOptions['name'],
            $cookieOptions['value'],
            $cookieOptions['expiration'],
            $cookieOptions['path'],
            $cookieOptions['domain'],
            $cookieOptions['secure'],
            $cookieOptions['httpOnly'],
            $cookieOptions['raw'],
            $cookieOptions['sameSite'],
            $cookieOptions['partitioned'],
        );

        if($isMobile){
            $response->setData(['BEARER' => $bearerToken]);
        } else{
            $response->headers->setCookie($authCookie);
        }

        return $response;
    }
}