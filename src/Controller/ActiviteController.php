<?php

namespace App\Controller;

use App\Entity\Activite;
use App\Entity\Benevole;
use App\Form\ActiviteType;
use App\Form\BenevoleType;
use App\Repository\ActiviteRepository;
use App\Repository\BenevoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/activite')]
final class ActiviteController extends AbstractController
{
    /*
        #[Route('', name: 'app_activite_index', methods: ['GET'])]
        public function index(ActiviteRepository $activiteRepository): Response
        {
            $activites = $activiteRepository->findAll();

            return $this->render('activite/index.html.twig', [
                'activites' => $activites,
            ]);
        }
            */

    #[Route('/list_activite', name:'app_list_Activite')]
    public function list(ActiviteRepository $activiteRepository){
        $Activite = $activiteRepository->findAll();
        return $this->render('activite/list_activite.html.twig', [
            'activites_list' => $Activite,
        ]);
    }
    #[Route('/Details/{id}', name: 'app_activite_details')]
    public function show(
        ActiviteRepository $activiteRepository,
        BenevoleRepository $donationRepository,
        int $id): Response
    {
        $activite = $activiteRepository->find($id);

        //  if (!$activitet) {
        //     throw $this->createNotFoundException('L\'article avec l\'ID ' . $id . ' n\'existe pas.');
        // }
        $benevole = $donationRepository->findBy(['benevole' => $activite]);

        return $this->render('ActiviteDetails/index.html.twig', [
            'activite' => $activite,
            'donations' => $benevole,
        ]);
    }
    #[Route('/new', name: 'app_activite_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $activites = new Activite();
        $form = $this->createForm(ActiviteType::class, $activites);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Ensure date is set (safety check)
            if (!$activites->getDate()) {
                $activites->setDate(new \DateTimeImmutable());
            }

            // Handle image upload before persisting
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('activites_images_directory'),
                        $newFilename
                    );
                    $activites->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors du téléchargement de l\'image.');
                }
            }

            $em->persist($activites); // FIXED: Added missing persist
            $em->flush();

            $this->addFlash('success', 'L\'activité a été créée avec succès.');

            return $this->redirectToRoute('app_activite_index');
        }

        return $this->render('activite/add_Activite.html.twig', [
            'form' => $form,
            'activites' => $activites,
        ]);
    }
    #[Route('/{id}/benevole', name: 'add_app_benevole', methods: ['GET', 'POST'])]
    public function addAppBenevole(
        Request $request,
        EntityManagerInterface $em,
        Activite $activite
    ): Response {
        $benevole = new Benevole();
        $benevole->addActivite($activite);

        $form = $this->createForm(BenevoleType::class, $benevole);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($benevole);
            $em->flush();

            $this->addFlash('success', 'Le bénévole a été ajouté avec succès.');

            return $this->redirectToRoute('add_app_benevole', ['id' => $activite->getId()]);
        }

        return $this->render('benevole/index.html.twig', [
            'form' => $form->createView(),
            'activite' => $activite,
        ]);
    }

    #[Route('/{id}', name: 'app_activite_show', methods: ['GET'])]
    public function showActivite(Activite $activite): Response
    {
        return $this->render('activite/index.html.twig', [
            'activite' => $activite,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_activite_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Activite $activite,
        EntityManagerInterface $em,
        SluggerInterface $slugger
    ): Response
    {
        $oldImage = $activite->getImage();

        $form = $this->createForm(ActiviteType::class, $activite);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Ensure date is set (safety check)
            if (!$activite->getDate()) {
                $activite->setDate(new \DateTimeImmutable());
            }

            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('activites_images_directory'),
                        $newFilename
                    );

                    // Delete old image if it exists
                    if ($oldImage) {
                        $oldPath = $this->getParameter('activites_images_directory') . '/' . $oldImage;
                        if (file_exists($oldPath)) {
                            unlink($oldPath);
                        }
                    }

                    $activite->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors du téléchargement de l\'image.');
                }
            }

            $em->flush(); // No need to persist on edit

            $this->addFlash('success', 'L\'activité a été modifiée avec succès.');

            return $this->redirectToRoute('app_activite_index');
        }

        return $this->render('activite/update_activite.html.twig', [
            'form' => $form,
            'activite' => $activite,
        ]);
    }
    #[Route('/{id}/delete', name: 'app_activite_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Activite $activite,
        EntityManagerInterface $em
    ): Response
    {
        // CSRF protection
        if ($this->isCsrfTokenValid('delete'.$activite->getId(), $request->request->get('_token'))) {
            // Delete associated image if exists
            if ($activite->getImage()) {
                $imagePath = $this->getParameter('activites_images_directory') . '/' . $activite->getImage();
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }

            // Remove benevoles associations (if not handled by cascade)
            foreach ($activite->getBenevoles() as $benevole) {
                $benevole->removeActivite($activite);
            }

            $em->remove($activite);
            $em->flush();

            $this->addFlash('success', 'L\'activité a été supprimée avec succès.');
        }

        return $this->redirectToRoute('app_activites');
    }
    #[Route('/', name: 'app_activite_index')]

    public function listactivite(
        Request            $request,
        activiteRepository  $activiteRepository,
        PaginatorInterface $paginator,

    ): Response
    {
        $search = $request->query->get('search');
        $criteria = array_filter([
            'search' => $search,
        ], function ($value) {
            return $value !== null && $value !== '';
        });

        if (!empty($criteria)) {
            $query  = $activiteRepository->findactiviteByCriteriaQuery($criteria);
        } else {
            $query  = $activiteRepository->findAll();
        }
        $activites = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            6
        );
        return $this->render("activite/index.html.twig", [
            "activites" => $activites,
            "currentFilters" => $criteria
        ]);
    }

}
