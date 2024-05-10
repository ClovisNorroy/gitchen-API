<?php

namespace App\Controller;

use App\Repository\MenuRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class MenuController extends AbstractController
{
    private $security;
    /** @var \App\Entity\User $currentUser */
    private $currentUser;
    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function getMenu(Request $request, MenuRepository $menuRepository): JsonResponse{
        /**
         * @param $menu Menu
         */
        /** @var \App\Entity\User $currentUser */
        $currentUser = $this->getUser();
        $menu = $currentUser->getMenu()->first();
        $completeMenu = $menu->getMenuDays();
        $menuArray = array();
        foreach($completeMenu as $daylyMenu){
            $menuArray[] = $daylyMenu->getMeal();
        }
        return new JsonResponse($menuArray);
    }

    public function saveMenu(Request $request, EntityManagerInterface $entityManager): JsonResponse{
        $menuData = $request->toArray();
        /** @var \App\Entity\User $currentUser */
        $currentUser = $this->getUser();
        $menu = $currentUser->getMenu()->first();
        $menuDays = $menu->getMenuDays();
        foreach($menuDays->getIterator() as $index => $menuDay){
            $menuDay->setMeal($menuData[$index]);
            $entityManager->persist($menuDay);
        }
        $entityManager->flush();
        return new JsonResponse("OK");
    }

    public function resetMenu(EntityManagerInterface $entityManager): JsonResponse{
        /** @var \App\Entity\User $currentUser */
        $currentUser = $this->getUser();
        $menu = $currentUser->getMenu()->first();
        $menuDays = $menu->getMenuDays();
        foreach($menuDays->getIterator() as $index => $menuDay){
            $menuDay->setMeal("");
            $entityManager->persist($menuDay);
        }
        $entityManager->flush();
        return new JsonResponse("OK");
    }

    public function switchLockState(Request $request, EntityManagerInterface $entityManager, MenuRepository $menuRepository){
        $menu =  $menuRepository->find($request->get("menuid"));
        $menu->setIsLocked($request->get("menuState"));
        $entityManager->persist($menu);
        $entityManager->flush();
    }
}