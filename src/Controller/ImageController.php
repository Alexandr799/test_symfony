<?php

namespace App\Controller;

use App\Service\ImageScraperService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ImageController extends AbstractController
{
    #[Route('/', name: 'app_image', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('image/index.html.twig');
    }

    #[Route('/image', name: 'app_image_result', methods: ['POST'])]
    public function create(Request $request, ImageScraperService $scrapper)
    {
        $imgList = $scrapper->scrapeImagesFromUrl($request->get('url'));
        return $this->render('image/result.html.twig', [
            'images' => $imgList,
            'imageCount' => count($imgList),
            'totalSize' => array_sum(array_column($imgList, 'size'))
        ]);
    }
}
