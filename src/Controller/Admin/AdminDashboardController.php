<?php

namespace App\Controller\Admin;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class AdminDashboardController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'admin_dashboard')]
    public function index(UserRepository $userRepository): Response
    {
        // Get all users for statistics
        $users = $userRepository->findAll();

        // Calculate gender counts
        $genderCounts = ['male' => 0, 'female' => 0, 'other' => 0];
        foreach ($users as $u) {
            $key = $u->getSexe() ? strtolower($u->getSexe()) : 'other';
            $normalized = in_array($key, ['male', 'homme', 'm']) ? 'male' : (in_array($key, ['female', 'femme', 'f']) ? 'female' : 'other');
            $genderCounts[$normalized]++;
        }

        // Get recent activity (last 5 users by creation date)
        $recentUsers = $userRepository->createQueryBuilder('u')
            ->orderBy('u.createdAt', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();

        return $this->render('admin/dashboard/index.html.twig', [
            'genderCounts' => $genderCounts,
            'totalUsers' => count($users),
            'recentUsers' => $recentUsers,
        ]);
    }
}

