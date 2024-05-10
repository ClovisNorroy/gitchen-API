<?php

namespace App\Controller ;

use App\Entity\Menu;
use App\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationController extends AbstractController
{
    public function signup(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $userData = $request->toArray();
        $user = new User();
        $user->setPassword(
            $userPasswordHasher->hashPassword(
                $user,
                $userData['password']
            )
        );
        if(!filter_var($userData['email'], FILTER_VALIDATE_EMAIL)){
            return new Response("Input Error", Response::HTTP_BAD_REQUEST, ['content-type' => 'text/html']);
        }
        $user->setEmail($userData['email']);
        $user->setUsername(preg_replace("/[^a-zA-Z0-9\_-]/", "", $userData['username']));
        $user->setRoles(['ROLE_USER']);

        $newMenu = new Menu();
        $newMenu->setDate(new DateTime());
        $user->addMenu($newMenu);
        $entityManager->persist($user);
        $entityManager->persist($newMenu);

        $entityManager->flush();
        return new Response("User created successfully", Response::HTTP_CREATED,['content-type' => 'text/html']);
    }
}