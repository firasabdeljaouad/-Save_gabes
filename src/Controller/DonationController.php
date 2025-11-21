<?php

namespace App\Controller;

use App\Entity\Donation;
use App\Entity\Project;

use App\Form\DonationFormType;
use App\Repository\DonaterRepository;
use App\Repository\DonationRepository;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DonationController extends AbstractController
{
    #[Route('/donation', name: 'app_donation')]
    public function index(): Response
    {
        return $this->render('donation/index.html.twig', [
            'controller_name' => 'DonationController',
        ]);
    }

    #[Route('/project/{id}/donate', name: 'app_project_donate')]
    public function donate(Project $project, Request $request, EntityManagerInterface $em): Response
    {
        $donation = new Donation();
        $donation->setProject($project);

        $form = $this->createForm(DonationFormType::class, $donation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($donation);
            $em->flush();

            $this->addFlash('success', 'Donation successfully added!');
            return $this->redirectToRoute('app_project_donate', ['id' => $project->getId()]);
        }

        return $this->render('donation/index.html.twig', [
            'form' => $form->createView(),
            'project' => $project,
        ]);
    }

    #[Route('/list_donation', name: 'app_list_donation')]
    public function list(DonationRepository  $donationRepository): Response
    {
        $donations = $donationRepository->findAll();
        return $this->render('donation/donation_list.html.twig', [
            'donations' => $donations,
        ]);
    }

    #[Route('/list_donations/{id}', name: 'app_donations_details')]
    public function show(DonationRepository $donationRepository, ProjectRepository $projectRepository, int $id): Response
    {
        // Fetch the project
        $projet = $projectRepository->find($id);

        if (!$projet) {
            throw $this->createNotFoundException('Le projet avec l\'ID ' . $id . ' n\'existe pas.');
        }

        // Fetch donations for this project
        $donations = $donationRepository->findBy(['project' => $id]);

        return $this->render('cause_details/donation_list_by_id.html.twig', [
            'donations' => $donations,
            'projet' => $projet,  // Add this
        ]);
    }

}
