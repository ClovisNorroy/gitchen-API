<?php

namespace App\Controller;

//require(__DIR__.'/../../Serices/WebScraping.php');

use App\Entity\Recipe;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class RecipeController extends AbstractController
{
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
/*         $curl = curl_init($imageLink);
        $uniqId = uniqid();
        $newFile = fopen('../public/images/'.$uniqId.'.jpg', 'wb');
        curl_setopt($curl, CURLOPT_FILE, $newFile);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_exec($curl);
        curl_close($curl);
        fclose($newFile); */
        $uniqId = uniqid();
        $img = '../public/images/'.$uniqId.'.jpg';
        file_put_contents($img, file_get_contents($imageLink));

        
        $title = $crawler->filter("h1")->first()->text();
        $ingredientsContent = $crawler->filter('div.card-ingredient-content')->each(function(Crawler $node, $i): string{
            return $node->text();
        });

        $stepList = $crawler->filter('div.recipe-step-list__container')->each(function(Crawler $node, $i): string{
            $text = $node->text();
            return  preg_replace('/Étape\s[1-9]*/', '', $text);
        });

        return new JsonResponse(array("title" => $title, "ingredients" => $ingredientsContent, "instructions" => $stepList), Response::HTTP_OK);
    }

    public function saveNewRecipe(Request $request, EntityManagerInterface $entityManager): Response
    {
        $dataNewRecipe = $request->toArray();
        //TODO: Check data
        $currentUser = $this->getUser();
        $newRecipe = new Recipe();
        $newRecipe->setName($dataNewRecipe["title"]);
        $newRecipe->setIngredients($dataNewRecipe["ingredients"]);
        $newRecipe->setInstructions($dataNewRecipe["instructions"]);
        $newRecipe->setUser($currentUser);
        $entityManager->persist($newRecipe);
        $entityManager->flush();
        
        return new Response("New recipe saved", 200);
    }
}