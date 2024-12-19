<?php

namespace App\Controller;

use App\Repository\PublicationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route(path: '/', name: 'index')]
    public function index(PublicationRepository $publicationRepository): Response
    {
        $publications = $publicationRepository->findBy([], ["createdAt" => "ASC"], 3);

        return $this->render("index.html.twig", ["publications" => $publications]);
    }
}
