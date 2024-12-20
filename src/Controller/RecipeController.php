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
    /**
     * @var string $originalImageData decoded base64 image
     * @var string $newImageFilePath filepath for the resized image
     */
    private function createResizedImage($originalImageData, $newImageFilePath){
        
        $originalImage = imagecreatefromstring($originalImageData);
        $originalImageWidth = imagesx($originalImage);
        $originalImageHeight = imagesy($originalImage);
        $resizedRecipeImage = imagecreatetruecolor(225, 225);
        imagecopyresampled($resizedRecipeImage, $originalImage, 0, 0, 0, 0, 225, 225, $originalImageWidth, $originalImageHeight);
        //file_put_contents($newImageFilePath,$resizedRecipeImage);
        imagejpeg($resizedRecipeImage, $newImageFilePath, 90);
    }

    public function getUserRecipes(EntityManagerInterface $entityManager): JsonResponse{
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $userId= $user->getId();
        $query = $entityManager->createQuery(
            'SELECT r.id, r.name, r.instructions, r.ingredients, r.imagePath FROM App\Entity\Recipe r
            WHERE r.User = :userID
            ORDER BY r.name'
        )->setParameter('userID', $userId);
        /**
         * @var array $recipes
         */
        $recipes = $query->getResult();

        $recipesWithImages = array_map(function ($recipe) {
            /** @var \App\Entity\User $user */
            $user = $this->getUser();
            $userId= $user->getId();
            $recipe['ingredients'] = explode(';', $recipe['ingredients']);
            $recipeImage = file_get_contents('../public/images/user_'.$userId.'/'.$recipe["imagePath"].'.jpg');
            $recipeImageMini = file_get_contents('../public/images/user_'.$userId.'/'.$recipe["imagePath"].'_mini.jpg');
            $recipe['image'] = base64_encode($recipeImage);
            $recipe['image_mini'] = base64_encode($recipeImageMini);
            return $recipe;
        }, $recipes);

        return new JsonResponse($recipesWithImages, 200);

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
        $recipeImageData = base64_decode($dataNewRecipe["image"]);
        
        //TODO: Check data
        /** @var \App\Entity\User $currentUser */
        $currentUser = $this->getUser();
        $currentUserID = $currentUser->getId();
        $recipeImageFolderPath = '../public/images/user_'.$currentUserID;
        $recipeImageFilePath = '../public/images/user_'.$currentUserID."/".$uniqId;
        $newRecipe = new Recipe();
        $newRecipe->setName($dataNewRecipe["title"]);
        $newRecipe->setIngredients($dataNewRecipe["ingredients"]);
        $newRecipe->setInstructions($dataNewRecipe["instructions"]);
        if(!file_exists($recipeImageFolderPath)){
            mkdir($recipeImageFolderPath, 0777, false);
        }
        file_put_contents($recipeImageFilePath.".jpg", $recipeImageData);
        $this->createResizedImage($recipeImageData, $recipeImageFilePath."_mini.jpg");
        $newRecipe->setImagePath($uniqId);
        $newRecipe->setUser($currentUser);
        $entityManager->persist($newRecipe);
        $entityManager->flush();
        
        return new Response("New recipe saved", 200);
    }
}