<?php

//namespace WebScraper;

use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;

/* class WebScraper{
    public function scrape(){
        $browser = new HttpBrowser(HttpClient::create());

        $browser->request('GET', 'https://www.marmiton.org/recettes/recette_tarte-aux-peches_59368.aspx');
    }

} */
function scrapingTest(){
    $browser = new HttpBrowser(HttpClient::create());

    $crawler = $browser->request('GET', 'https://www.marmiton.org/recettes/recette_tarte-aux-peches_59368.aspx');
    $pageTitle = $crawler->filter('title')->text();
    return $pageTitle;
}
