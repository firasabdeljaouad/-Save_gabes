<?php

namespace App\Controller;

use App\Entity\Evenement;
use App\Form\EvenementType;
use App\Repository\EvenementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface; // Add this import

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
    public function listfront(EvenementRepository $repository): Response
    {
        $evenements = $repository->findAll();
        return $this->render("evenement/EventsFront.html.twig",
            ["tabEvenements" => $evenements]);
    }


    #[Route('/add', name: 'addForm_evenement')]
    public function add(EntityManagerInterface $em, Request $request, SluggerInterface $slugger): Response
    {
        $evenement = new Evenement();

        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $imageFile = $form->get('imageFile')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('evenement_images_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // Log the error or add flash message
                }

                $evenement->setImageName($newFilename);
            }

            $em->persist($evenement);
            $em->flush();

            $this->addFlash('success', 'Événement ajouté avec succès !');
            return $this->redirectToRoute('addForm_evenement');
        }

        return $this->render('evenement/add.html.twig', [
            'formEvenement' => $form->createView(),
        ]);
    }

    #[Route('/update/{id}', name: 'update_evenement')]
    public function update(
        Evenement $evenement,
        EntityManagerInterface $em,
        Request $request,
        SluggerInterface $slugger
    ): Response {
        $oldImage = $evenement->getImageName();

        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $imageFile = $form->get('imageFile')->getData(); // Changed from 'imageName' to 'imageFile'

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('evenement_images_directory'),
                        $newFilename
                    );

                    // Delete old image after successful upload
                    if ($oldImage) {
                        $oldPath = $this->getParameter('evenement_images_directory') . '/' . $oldImage; // Fixed parameter name
                        if (file_exists($oldPath)) {
                            unlink($oldPath);
                        }
                    }

                    $evenement->setImageName($newFilename);

                } catch (FileException $e) {
                    // Keep old image if upload fails
                    $this->addFlash('error', 'Erreur lors de l\'upload de l\'image');
                }
            }
            // If no new image uploaded, Doctrine will keep the existing imageName automatically

            $em->flush(); // No need to persist() for existing entities

            $this->addFlash('success', 'Événement modifié avec succès !');
            return $this->redirectToRoute('lists_evenement_front'); // Or your events list route
        }

        return $this->render('evenement/update.html.twig', [
            'formEvenement' => $form->createView(),
            'evenement' => $evenement
        ]);
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
}
