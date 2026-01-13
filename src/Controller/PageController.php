<?php

namespace App\Controller;

use App\Entity\Image;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PageController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('page/index.html.twig', []);
    }
    #[Route('/about', name: 'about')]
    public function about(): Response
    {
        return $this->render('page/about.html.twig', []);
    }
    #[Route('/archieves', name: 'archieves')]
    public function archieves(ManagerRegistry $doctrine): Response
    {
        // Obtenemos todas las imágenes de la base de datos
        $images = $doctrine->getRepository(Image::class)->findAll();

        // Renderizamos Twig pasando la variable 'images'
        return $this->render('page/archieves.html.twig', [
            'images' => $images,
        ]);
    }
    #[Route('/contact', name: 'contact')]
    public function contact(): Response
    {
        return $this->render('page/contact.html.twig', []);
    }
    #[Route('/thankyou', name: 'thankyou')]
    public function thankyou(): Response
    {
        return $this->render('page/thankyou.html.twig', []);
    }
}
