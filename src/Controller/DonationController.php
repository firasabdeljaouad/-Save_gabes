<?php

namespace App\Controller;

use App\Entity\Donation;
use App\Entity\Project;
use App\Form\DonationFormType;
use App\Repository\DonationRepository;
use App\Repository\ProjectRepository;
use App\Service\StripePyment;
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
    public function donate(
        Project $project, 
        Request $request, 
        EntityManagerInterface $em,
        \App\Service\StripePaymentService $stripePaymentService
    ): Response
    {
        $donation = new Donation();
        $donation->setProject($project);
        // Default status for new donation
        $donation->setStatus('pending');
        $donation->setPaymentMethod('Stripe'); 

        $form = $this->createForm(DonationFormType::class, $donation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Store donation data in session instead of persisting immediately
            $donationData = [
                'amount' => $donation->getAmount(),
                'paymentMethod' => $donation->getPaymentMethod(),
                'isAnonymous' => $donation->isAnonymous(),
                'projectId' => $project->getId(),
                'name' => $donation->getName(), // Assuming getName exists on Donation
                // Add other necessary fields here
            ];
            
            $request->getSession()->set('pending_donation', $donationData);

            $checkoutUrl = $stripePaymentService->createCheckoutSession($donation);

            if ($checkoutUrl) {
                return $this->redirect($checkoutUrl);
            }
            
            $this->addFlash('error', 'Could not create payment session.');
        }

        return $this->render('donation/index.html.twig', [
            'form' => $form->createView(),
            'project' => $project,
        ]);
    }

    #[Route('/payment/success', name: 'app_payment_success')]
    public function stripeSuccess(
        Request $request, 
        EntityManagerInterface $em, 
        ProjectRepository $projectRepository
    ): Response
    {
        $session = $request->getSession();
        $donationData = $session->get('pending_donation');

        if (!$donationData) {
            $this->addFlash('error', 'No pending donation found.');
            return $this->redirectToRoute('app_donation'); // Or wherever appropriate
        }

        $project = $projectRepository->find($donationData['projectId']);

        if (!$project) {
             $this->addFlash('error', 'Project not found.');
             return $this->redirectToRoute('app_donation');
        }

        $donation = new Donation();
        $donation->setAmount($donationData['amount']);
        $donation->setPaymentMethod($donationData['paymentMethod']);
        $donation->setIsAnonymous($donationData['isAnonymous']);
        $donation->setProject($project);
        $donation->setName($donationData['name']);
        
        $donation->setStatus('completed');
        $donation->setTransactionId($request->query->get('session_id'));
        
        $em->persist($donation);
        $em->flush();
        
        // Clear session
        $session->remove('pending_donation');

        $this->addFlash('success', 'Thank you for your donation!');
        return $this->redirectToRoute('app_project_donate', ['id' => $project->getId()]);
    }

    #[Route('/payment/cancel', name: 'app_payment_cancel')]
    public function stripeCancel(Request $request): Response
    {
        $session = $request->getSession();
        $donationData = $session->get('pending_donation');
        
        $projectId = $donationData['projectId'] ?? null;
        
        $session->remove('pending_donation');

        $this->addFlash('warning', 'Payment cancelled.');
        
        if ($projectId) {
            return $this->redirectToRoute('app_project_donate', ['id' => $projectId]);
        }
        
        return $this->redirectToRoute('app_donation');
    }

    #[Route('/list_donations/{id}', name: 'app_donations_details')]
    public function show(
        DonationRepository $donationRepository,
        ProjectRepository $projectRepository,
        int $id
    ): Response
    {
        $project = $projectRepository->find($id);

        if (!$project) {
            throw $this->createNotFoundException("Le projet avec l'ID $id n'existe pas.");
        }

        // Fetch donations for this project
        $donations = $donationRepository->findByProjectOrderByAmountDesc($id);

        return $this->render('cause_details/donation_list_by_id.html.twig', [
            'donations' => $donations,
            'project'   => $project,
        ]);
    }
}
