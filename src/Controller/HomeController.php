<?php

namespace App\Controller;

use App\Repository\ProjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ActiviteRepository;
use App\Repository\BenevoleRepository;


final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
        public function index(ActiviteRepository $activiteRepo, BenevoleRepository $benevoleRepo): Response
        {
            $latestActivites = $activiteRepo->findBy([], ['date' => 'DESC'], 3);
            $latestBenevoles = $benevoleRepo->findBy([], ['id' => 'DESC'], 3);

            return $this->render('home/index.html.twig', [
                'latestActivites' => $latestActivites,
                'latestBenevoles' => $latestBenevoles,
                'controller_name' => 'HomeController',
            ]);
        }
    #[Route('/contact', name: 'app_contact')]
    public function contact(): Response
    {
        return $this->render('contact/contact.html.twig');
    }
    #[Route('/about', name: 'app_about')]
    public function about(): Response
    {
        return $this->render('about/index.html.twig');
    }
    #[Route('/blog', name: 'app_blog')]
    public function blog(): Response
    {
        return $this->render('blog/index.html.twig');
    }


}
