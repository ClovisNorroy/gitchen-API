<?php

namespace App\Controller;

//require(__DIR__.'/../../Serices/WebScraping.php');

use App\Entity\Recipe;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class RecipeController extends AbstractController
{

    public function getUserRecipes(EntityManagerInterface $entityManager): JsonResponse{
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $userId= $user->getId();
        $query = $entityManager->createQuery(
            'SELECT r.id, r.name, r.instructions, r.ingredients, r.imagePath FROM App\Entity\Recipe r
            WHERE r.User = :userID
            ORDER BY r.name'
        )->setParameter('userID', $userId);
        $recipes = $query->getResult();
        return new JsonResponse($recipes, 200);

    }

    public function scrape(Request $request): JsonResponse
    {
        $requestParameters = $request->toArray();
        $URLToScrape = $requestParameters['URL'];
        //TODO : Check URL is Marmitton and correct
        $browser = new HttpBrowser(HttpClient::create());

        $crawler = $browser->request('GET', $URLToScrape);
        //TODO: Two possibilities #recipe-media-viewer-main-picture or #recipe-media-viewer-thumbnail-0
        //$link = $crawler->filter("#recipe-media-viewer-thumbnail-0")->first();
        //TODO: Make images private
        $imageLink = $crawler->filter("#recipe-media-viewer-main-picture")->attr("data-src");
        $recipeImage = file_get_contents($imageLink);
        
        $title = $crawler->filter("h1")->first()->text();
        $ingredientsContent = $crawler->filter('div.card-ingredient-content')->each(function(Crawler $node, $i): string{
            return $node->text();
        });

        $stepList = $crawler->filter('div.recipe-step-list__container')->each(function(Crawler $node, $i): string{
            $text = $node->text();
            return  preg_replace('/Étape\s[1-9]*/', '', $text);
        });

        return new JsonResponse(array("title" => $title,
        "ingredients" => $ingredientsContent, "instructions" => $stepList,
        "image" => base64_encode($recipeImage)), Response::HTTP_OK);
    }

    public function saveNewRecipe(Request $request, EntityManagerInterface $entityManager): Response
    {
        $uniqId = uniqid();
        $dataNewRecipe = $request->toArray();
        //TODO: Check data
        /** @var \App\Entity\User $currentUser */
        $currentUser = $this->getUser();
        $currentUserID = $currentUser->getId();
        $newRecipe = new Recipe();
        $newRecipe->setName($dataNewRecipe["title"]);
        $newRecipe->setIngredients($dataNewRecipe["ingredients"]);
        $newRecipe->setInstructions($dataNewRecipe["instructions"]);
        if(!file_exists('../public/images/user_'.$currentUserID)){
            mkdir('../public/images/user_'.$currentUserID, 0777, false);
        }
        file_put_contents('../public/images/user_'.$currentUserID."/".$uniqId.".jpg", base64_decode($dataNewRecipe["image"]));
        $newRecipe->setImagePath($currentUser->getUserIdentifier()."/".$uniqId.".jpg");
        $newRecipe->setUser($currentUser);
        $entityManager->persist($newRecipe);
        $entityManager->flush();
        
        return new Response("New recipe saved", 200);
    }
}