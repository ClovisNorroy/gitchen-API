<?php

use App\Kernel;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

header("Access-Control-Allow-Credentials: true");

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
