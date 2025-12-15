<?php

namespace App\Controller;

use App\Entity\Evenement;
use App\Form\EvenementType;
use App\Repository\EvenementRepository;
use App\Repository\TypeEvenementRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;
use Knp\Component\Pager\PaginatorInterface;

class EvenementController extends AbstractController
{
    #[Route('/list', name: 'lists_evenement')]
    public function list(EvenementRepository $repository): Response
    {
        $evenements = $repository->findAll();
        return $this->render("evenement/listEvenements.html.twig",
            ["tabEvenements" => $evenements]);
    }

    #[Route('/evenement', name: 'lists_evenement_front')]
    public function listfront(
        Request $request,
        EvenementRepository $repository,
        TypeEvenementRepository $typeEvenementRepository,
        PaginatorInterface $paginator
    ): Response
    {
        $search = $request->query->get('search');
        $type = $request->query->get('type');
        $status = $request->query->get('status');
        $dateFrom = $request->query->get('date_from');
        $dateTo = $request->query->get('date_to');

        $criteria = array_filter([
            'search' => $search,
            'type' => $type,
            'status' => $status,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ], function($value) {
            return $value !== null && $value !== '';
        });

        // Get type events for filter dropdown
        $types = $typeEvenementRepository->findAll();

        if (!empty($criteria)) {
            $query = $repository->findEventsByCriteriaQuery($criteria);
        } else {
            $query = $repository->findAllQuery(); // Show all events by default
        }

        // Paginate the results
        $evenements = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1), // page number
            8 // limit per page
        );

        return $this->render("evenement/EventsFront.html.twig", [
            "tabEvenements" => $evenements,
            "types" => $types,
            "currentFilters" => $criteria
        ]);
    }

    #[Route('/add', name: 'addForm_evenement')]
    public function addWithForm(Request $request, ManagerRegistry $doctrine, SluggerInterface $slugger): Response
    {
        $evenement = new Evenement();

        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle file upload
            $imageFile = $form->get('imageFile')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('kernel.project_dir').'/public/images/evenements/',
                        $newFilename
                    );
                    $evenement->setImageName($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de l\'image.');
                }
            }

            $em = $doctrine->getManager();
            $em->persist($evenement);
            $em->flush();

            $this->addFlash('success', 'Événement ajouté avec succès!');
            return $this->redirectToRoute("lists_evenement");
        }

        return $this->render("evenement/add.html.twig",
            [
                "formEvenement" => $form->createView()
            ]);
    }

    #[Route('/update/{id}', name: 'update_evenement')]
    public function update($id, EvenementRepository $repository, Request $request, ManagerRegistry $doctrine, SluggerInterface $slugger): Response
    {
        $evenement = $repository->find($id);

        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle file upload
            $imageFile = $form->get('imageFile')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('kernel.project_dir').'/public/images/evenements/',
                        $newFilename
                    );

                    // Delete old image if exists
                    $oldImageName = $evenement->getImageName();
                    if ($oldImageName) {
                        $oldImagePath = $this->getParameter('kernel.project_dir').'/public/images/evenements/'.$oldImageName;
                        if (file_exists($oldImagePath)) {
                            unlink($oldImagePath);
                        }
                    }

                    $evenement->setImageName($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de l\'image.');
                }
            }

            $em = $doctrine->getManager();
            $em->flush();

            $this->addFlash('success', 'Événement modifié avec succès!');
            return $this->redirectToRoute("lists_evenement");
        }

        return $this->render("evenement/update.html.twig",
            array("formEvenement" => $form->createView()));
    }

    #[Route('/remove/{id}', name: 'remove_evenement')]
    public function deleteEvenement(ManagerRegistry $doctrine, $id, EvenementRepository $repository): Response
    {
        $evenement = $repository->find($id);

        // Delete associated image file
        $imageName = $evenement->getImageName();
        if ($imageName) {
            $imagePath = $this->getParameter('kernel.project_dir').'/public/images/evenements/'.$imageName;
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        $em = $doctrine->getManager();
        $em->remove($evenement);
        $em->flush();

        $this->addFlash('success', 'Événement supprimé avec succès!');
        return $this->redirectToRoute("lists_evenement");
    }

    /**
     * Additional routes using the new DQL methods
     */
    #[Route('/evenement/upcoming', name: 'upcoming_events')]
    public function upcomingEvents(EvenementRepository $repository): Response
    {
        $evenements = $repository->findUpcomingEvents();
        return $this->render("evenement/EventsFront.html.twig",
            ["tabEvenements" => $evenements]);
    }

    #[Route('/evenement/status/{status}', name: 'events_by_status')]
    public function eventsByStatus(string $status, EvenementRepository $repository): Response
    {
        $evenements = $repository->findByStatus($status);
        return $this->render("evenement/EventsFront.html.twig",
            ["tabEvenements" => $evenements]);
    }

    #[Route('/evenement/search', name: 'search_events')]
    public function searchEvents(Request $request, EvenementRepository $repository): Response
    {
        $searchTerm = $request->query->get('q', '');
        $evenements = [];

        if (!empty($searchTerm)) {
            $evenements = $repository->searchEvents($searchTerm);
        }

        return $this->render("evenement/EventsFront.html.twig", [
            "tabEvenements" => $evenements,
            "searchTerm" => $searchTerm
        ]);
    }
}
