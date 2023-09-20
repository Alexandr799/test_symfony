<?php

namespace App\Controller;

use App\Service\ImageScraperService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ImageController extends AbstractController
{
    #[Route('/', name: 'app_image', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $session = $request->getSession();
        $errors = $session->get('errors');
        $session->remove('errors');
        return $this->render('image/index.html.twig', [
            'errors' => $errors,
        ]);
    }

    #[Route('/image', name: 'app_image_result', methods: ['POST'])]
    public function create(Request $request, ImageScraperService $scrapper, ValidatorInterface $validator)
    {
        $url = $request->request->get('url');

        $violations = $validator->validate($url, [
            new Assert\NotBlank([
                'message' => 'Поле URL не может быть пустым.',
            ]),
            new Assert\Url([
                'message' => 'Поле URL должно быть действительным URL-адресом.',
            ]),
        ]);

        if (count($violations) > 0) {
            foreach ($violations as $error) {
                $errorArray[] = $error->getMessage();
            }
            $request->getSession()->set('errors', $errorArray);
            return $this->redirectToRoute('app_image');
        }

        $imgList = $scrapper->scrapeImagesFromUrl($request->get('url'));

        if (!is_array($imgList)) {
            $request->getSession()->set('errors', ['Не удалось получить страницу!']);
            return $this->redirectToRoute('app_image');
        }
        return $this->render('image/result.html.twig', [
            'images' => $imgList,
            'imageCount' => count($imgList),
            'totalSize' => array_sum(array_column($imgList, 'size'))
        ]);
    }
}
