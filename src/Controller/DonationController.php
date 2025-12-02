<?php

namespace App\Controller;

use App\Entity\Donation;
use App\Entity\Project;
use App\Form\DonationFormType;
use App\Repository\DonationRepository;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DonationController extends AbstractController
{
    #[Route('/donation', name: 'app_donation')]
    public function index(): Response
    {
        return $this->render('donation/index.html.twig');
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

    #[Route('/list_donations/{id}', name: 'app_donations_details')]
<<<<<<< HEAD
    public function show(ProjectRepository $projectRepository, DonationRepository $donationRepository, int $id): Response
=======
    public function show(
        DonationRepository $donationRepository,
        ProjectRepository $projectRepository,
        int $id
    ): Response
>>>>>>> 1e1f1f665f72710991d480fe35a9d7651614baf9
    {
        $project = $projectRepository->find($id);

        if (!$project) {
            throw $this->createNotFoundException("Le projet avec l'ID $id n'existe pas.");
        }

<<<<<<< HEAD
        $donations = $donationRepository->findBy([
            'project' => $project,
        ]);
=======
        // Fetch donations for this project
        $donations = $donationRepository->findByProjectOrderByAmountDesc($id);
>>>>>>> 1e1f1f665f72710991d480fe35a9d7651614baf9

        return $this->render('cause_details/donation_list_by_id.html.twig', [
            'donations' => $donations,
            'project'   => $project,
        ]);
    }
}
