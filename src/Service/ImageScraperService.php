<?php

namespace App\Service;

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\CssSelector\CssSelectorConverter;

class ImageScraperService
{
    private $client;
    private $cssConverter;

    public function __construct(Client $client, CssSelectorConverter $cssConverter)
    {
        $this->client =  $client;
        $this->cssConverter =  $cssConverter;
    }

    public function scrapeImagesFromUrl(string $url): array | false
    {
        try {
            $response = $this->client->get($url);
        } catch (\Exception $e) {
            return false;
        }

        $htmlContent = $response->getBody()->getContents();

        $crawler = new Crawler($htmlContent);

        $imageUrls = [];
        $images = $crawler->filterXPath($this->cssConverter->toXPath('img'))->getIterator();

        $imageUrls = [];
        foreach ($images as $i => $image) {
            $src = $image->getAttribute('src');
            if (empty($src) || $src === '') continue;

            $imageData = @file_get_contents($src);
            if (!$imageData) continue;
            $size = empty($imageData) ? null : strlen($imageData);

            $imageUrls[] = [
                'src' => $src,
                'size' => $size
            ];
        }

        return $imageUrls;
    }
}
