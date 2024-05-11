<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class ServiceStatusController extends AbstractController
{
    public function ping(): Response
    {
        return new Response("server is up", Response::HTTP_OK);
    }
}