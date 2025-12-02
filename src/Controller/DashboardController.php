<?php
// src/Controller/DashboardController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

// You might need to fetch data from the database here, so import your repositories
use App\Repository\BenevoleRepository;
use App\Repository\ActiviteRepository;
use App\Repository\DonationRepository;
use App\Repository\ProjectRepository;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(
        BenevoleRepository $benevoleRepo,
        ActiviteRepository $activiteRepo,
        DonationRepository $donationRepo,
        ProjectRepository $projetRepo
    ): Response
    {
        // Count total items
        $benevolesCount = $benevoleRepo->count([]);
        $activitesCount = $activiteRepo->count([]);
        $donationsCount = $donationRepo->count([]);
        $projectsCount = $projetRepo->count([]);

        // Get latest 5 benevoles and activites
        $lastBenevoles = $benevoleRepo->findBy([], ['id' => 'DESC'], 5);
        $lastActivites = $activiteRepo->findBy([], ['id' => 'DESC'], 5);

        return $this->render('dashboard/index.html.twig', [
            'benevoles_count' => $benevolesCount,
            'activites_count' => $activitesCount,
            'donations_count' => $donationsCount,
            'projects_count' => $projectsCount,
            'last_benevoles' => $lastBenevoles,
            'last_activites' => $lastActivites,
        ]);
    }
}
