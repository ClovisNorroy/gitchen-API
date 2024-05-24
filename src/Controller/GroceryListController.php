<?php

namespace App\Controller;

use App\Entity\GroceryList;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GroceryListController extends AbstractController
{
    public function getGroceryList(EntityManagerInterface $entityManager): JsonResponse
    {
        /**
         * @param $groceryList GroceryList
         */
        /** @var \App\Entity\User $currentUser */
        $currentUser = $this->getUser();
        $groceryList = $currentUser->getGroceryLists()->first();
        if($groceryList)
            return $this->json($groceryList->getList());
        else {
            $newList = new GroceryList();
            $newList->addUser($currentUser);
            $entityManager->persist($newList);
            $entityManager->flush();
            return $this->json($newList->getList());
        }
            
    }

    public function saveGroceryList(Request $request, EntityManagerInterface $entityManager): Response
    {
        $newGroceryList = $request->toArray();
        /**
         * @param $groceryList GroceryList
         */
        /** @var \App\Entity\User $currentUser */
        $currentUser = $this->getUser();
        $groceryList = $currentUser->getGroceryLists()->first();
        $groceryList->setList($newGroceryList);
        $entityManager->persist($groceryList);
        $entityManager->flush();

        return new Response("Menu saved", 200);
    }
}
