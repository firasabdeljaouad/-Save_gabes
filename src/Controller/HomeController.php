<?php

namespace App\Controller;

use App\Repository\ProjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
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
