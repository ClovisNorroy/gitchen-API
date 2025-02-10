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
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\PantherTestCase;

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
        $filters = [
            'www.marmiton.org' => [
                'title' => 'div.main-title > h1',
                'ingredients' => 'div.card-ingredient',
                'instructions' => 'div.recipe-step-list__container p',
                'image' => ['img#recipe-media-viewer-thumbnail-0', 'src'],
                'imageBackup' => ['img#recipe-media-viewer-main-picture', 'src']
            ],
            'cuisine.journaldesfemmes.fr' => [
                'title' => 'h1.app_recipe_title_page',
                'ingredients' => '.app_recipe_ing_title',
                'instructions' => 'ol li div.grid_last',
                'image' => ['img.bu_cuisine_img_noborder', 'src'],
                'imageBackup' => ['picture > source', 'srcset']
            ],
            'www.750g.com' => [
                'title' => 'header > span.u-title-page',
                'ingredients' => 'li.recipe-ingredients-item',
                'instructions' => 'div.recipe-steps-text > p',
                'image' => ['div.glide__slide.recipe-cover:nth-of-type(2) picture:nth-of-type(2) img', 'src'],
                'imageBackup' => ['div.glide__slide.recipe-cover:nth-of-type(2) picture:nth-of-type(2) img', 'src']
            ],
            'www.papillesetpupilles.fr' => [ // Blocked
                'title' => 'h1.title',
                'ingredients' => 'div.post_content > ul > li',
                'instructions' => 'div.post_content p',
                'image' => ['div.post_content > div:nth-child(1) > img.size-full', 'src'],
                'imageBackup' => ['div.post_content > div:nth-child(1) > img.size-full', 'src']
            ],
            'www.cuisineaz.com' => [
                'title' => 'h1.recipe-title',
                'ingredients' => 'li.ingredient_item',
                'instructions' => 'ul.preparation_steps > li.preparation_step > p',
                'image' => ['section#recipe_image > picture > img', 'src'],
                'imageBackup' => []
            ],
            'www.meilleurduchef.com' => [
                'title' => 'div#page-content h1',
                'ingredients' => 'li.ingredient',
                'instructions' => 'div.instruction p',
                'image' => ['div.media-display img:nth-child(1)', 'src'],
                'imageBackup' => []
            ],
            'www.hervecuisine.com' => [
                'title' => 'h1.post-title',
                'ingredients' => 'div.recipe-ingredient-list > ul li',
                'instructions' => 'div.recipe-steps > ul li',
                'image' => ['img.attachment-main-full.size-main-full.wp-post-image ', 'data-src'],
                'imageBackup' => []
            ]
        ];

        $requestParameters = $request->toArray();
        $host = parse_url($requestParameters['URL'], PHP_URL_HOST);
        if(!array_key_exists($host, $filters)){
            return new JsonResponse("Missing host", Response::HTTP_EXPECTATION_FAILED);
        }
        $URLToScrape = $requestParameters['URL'];
        $pantherClient = Client::createChromeClient();
        $pantherCrawler = $pantherClient->request('GET', $URLToScrape);
        sleep(1);
        $browserKitClient = new HttpBrowser(HttpClient::create());
        $browserKitCrawler = $browserKitClient->request('GET', $URLToScrape);
        $pantherClient->takeScreenshot('tiram.png');

        $titleCrawler = $browserKitCrawler->filter($filters[$host]['title']);

        if($titleCrawler->count()>0){
            $title = $titleCrawler->first()->text();
        }
        else{
            return new JsonResponse("Scraping is impossible", Response::HTTP_EXPECTATION_FAILED); 
        }

        // For Marmitton, sometimes there is a video instead of a main picture, we will take the first thumbnail instead
        $imageCrawler = $pantherCrawler->filter($filters[$host]['image'][0]);
        if($imageCrawler->count() > 0){
            $imageLink = $imageCrawler->attr($filters[$host]['image'][1]);
        }
        else{
            $imageLink = $pantherCrawler->filter($filters[$host]['imageBackup'][0])->attr($filters[$host]['imageBackup'][1]);
        }
        if(strcmp($host, 'www.meilleurduchef.com') == 0){
            $recipeImage = file_get_contents('https:'.$imageLink);
        }
        else {
            $recipeImage = file_get_contents($imageLink);
        }

        file_put_contents("recipeImage.jpg", $recipeImage);


        // Avoid being too quick to simulate human behavior
        sleep(1);
        $ingredientList = $browserKitCrawler->filter($filters[$host]['ingredients'])->each(function(Crawler $node, $i): string{
            return $node->text("no value");
        });

        $instructionsList = $browserKitCrawler->filter($filters[$host]['instructions'])->each(function(Crawler $node, $i): string{
            return $text = $node->text();
        });

        $ingredients = array_map(function ($ingredient, $index){ 
            return ['id'=> $index, 'item' => $ingredient];
        }, $ingredientList, array_keys($ingredientList));

        return new JsonResponse(array("title" => $title,
        "ingredients" => $ingredients, "instructions" => $instructionsList,
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