<?php

namespace App\Controller;

//require(__DIR__.'/../../Serices/WebScraping.php');

use App\Entity\Recipe;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Dom\Entity;
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

    public function getUserRecipes(EntityManagerInterface $entityManager): JsonResponse
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $userId= $user->getId();
        $recipes = $user->getRecipes();
        $recipes = $recipes->toArray();
        if(count($recipes)){
            $parsedRecipes = [];
            foreach($recipes as $recipe){
                $recipeImage = file_get_contents('../public/images/user_'.$userId.'/'.$recipe->getImagePath().'.png');
                $parsedRecipes[] = [
                    'id' => $recipe->getId(),
                    'name' => $recipe->getName(),
                    'instructions' => explode(';', $recipe->getInstructions()),
                    'ingredients' => explode(';', $recipe->getIngredients()),
                    'image' => base64_encode($recipeImage)
                ];
            }
            return new JsonResponse($parsedRecipes, 200);
        }
        else{
            return new JsonResponse("No data found", Response::HTTP_NO_CONTENT);
        }
    }

    public function getRecipe(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $recipeID = $request->get('recipeid');
        /** @var \App\Entity\User $currentUser */
        $currentUser = $this->getUser();
        $recipe = $entityManager->getRepository(Recipe::class)->findOneBy([
            'id' => $recipeID,
            'user' => $currentUser
        ]);
        if($recipe){
            $recipeImage = file_get_contents('../public/images/user_'.$currentUser->getId().'/'.$recipe->getImagePath().'.png');
            return new JsonResponse([
                'id' => $recipe->getId(),
                'name' => $recipe->getName(),
                'instructions' => explode(';', $recipe->getInstructions()),
                'ingredients' => explode(';', $recipe->getIngredients()),
                'image' => base64_encode($recipeImage)
            ], 200);
        }
        else{
            return new JsonResponse("Recipe not found", 404);
        }
    }

    public function deleteRecipe(Request $request, EntityManagerInterface $entityManager): Response
    {
        $recipeID = $request->get('recipeid');
        /** @var \App\Entity\User $currentUser */
        $currentUser = $this->getUser();
        $recipe = $entityManager->getRepository(Recipe::class)->findOneBy([
            'id' => $recipeID,
            'User' => $currentUser
        ]);
        if($recipe){
            $recipeImagePath = '../public/images/user_'.$currentUser->getId().'/'.$recipe->getImagePath().'.png';
            unlink($recipeImagePath);
            $entityManager->remove($recipe);
            $entityManager->flush();
            return new Response("Recipe deleted", 200);
        }
        else{
            return new Response("Recipe not found", 404);
        }
    }

    public function scrape(Request $request): JsonResponse
    {
        $filters = [
            'www.marmiton.org' => [
                'title' => 'div.main-title > h1',
                'ingredients' => 'div.card-ingredient',
                'instructions' => 'div.recipe-step-list__container p',
                'image' => ['img#recipe-media-viewer-thumbnail-0', 'data-src'],
                'imageBackup' => ['img#recipe-media-viewer-main-picture', 'data-src']
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
        //$pantherClient = Client::createFirefoxClient();
        //$pantherClient->request('GET', $URLToScrape);
        //$pantherCrawler = $pantherClient->waitFor($filters[$host]['image'][0]);
        //$pantherCrawler = $pantherClient->request('GET', $URLToScrape);
        sleep(1);
        $browserKitClient = new HttpBrowser(HttpClient::create());
        $browserKitCrawler = $browserKitClient->request('GET', $URLToScrape);
        //$pantherClient->takeScreenshot('tiram.png');

        $titleCrawler = $browserKitCrawler->filter($filters[$host]['title']);

        if($titleCrawler->count()>0){
            $title = $titleCrawler->first()->text();
        }
        else{
            return new JsonResponse("Scraping is impossible", Response::HTTP_EXPECTATION_FAILED); 
        }

        // For Marmitton, sometimes there is a video instead of a main picture, we will take the first thumbnail instead
        $imageCrawler = $browserKitCrawler->filter($filters[$host]['image'][0]);
        if($imageCrawler->count() > 0){
            $imageLink = $imageCrawler->attr($filters[$host]['image'][1]);
        }
        else{
            $imageLink = $browserKitCrawler->filter($filters[$host]['imageBackup'][0])->attr($filters[$host]['imageBackup'][1]);
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

        $instructions = array_map(function ($ingredient, $index){ 
            return ['id'=> $index, 'item' => $ingredient];
        }, $instructionsList, array_keys($instructionsList));

        return new JsonResponse(array("name" => $title,
        "ingredients" => $ingredients, "instructions" => $instructions,
        "image" => base64_encode($recipeImage)), Response::HTTP_OK);
    }

    public function saveNewRecipe(Request $request, EntityManagerInterface $entityManager): Response
    {
        $uniqId = uniqid();
        $dataNewRecipe = $request->toArray();
        $data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $dataNewRecipe['image']));
        $image = base64_decode($dataNewRecipe['image']);
        //TODO: Check data
        /** @var \App\Entity\User $currentUser */
        $currentUser = $this->getUser();
        $currentUserID = $currentUser->getId();
        $recipeImageFolderPath = '../public/images/user_'.$currentUserID;
        $recipeImageFilePath = '../public/images/user_'.$currentUserID."/".$uniqId;
        $newRecipe = new Recipe();
        $newRecipe->setName($dataNewRecipe["name"]);
        $newRecipe->setIngredients($dataNewRecipe["ingredients"]);
        $newRecipe->setInstructions($dataNewRecipe["instructions"]);
        if(!file_exists($recipeImageFolderPath)){
            mkdir($recipeImageFolderPath, 0777, false);
        }
        file_put_contents($recipeImageFilePath.'.png', $data);
        $newRecipe->setImagePath($uniqId);
        $newRecipe->setUser($currentUser);
        $entityManager->persist($newRecipe);
        $entityManager->flush();
        
        return new Response("New recipe saved", 200);
    }
}