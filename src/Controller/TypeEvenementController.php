<?php

namespace App\Controller;

use App\Entity\TypeEvenement;
use App\Form\TypeEvenementType;
use App\Repository\TypeEvenementRepository;
use Doctrine\Persistence\ManagerRegistry as Registry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TypeEvenementController extends AbstractController
{
    #[Route('/typeevenement/list', name: 'typeevenement_list')]
    public function list(TypeEvenementRepository $repository)
    {
        $typeEvenements = $repository->findAll();
        return $this->render("type_evenement/listType.html.twig",
            ["typeEvenements" => $typeEvenements]);
    }

    #[Route('/addType', name: 'app_typeevenement_add')]
    public function addWithForm(Request $request, Registry $doctrine): Response
    {
        $typeEvenement = new TypeEvenement();
        $form = $this->createForm(TypeEvenementType::class, $typeEvenement);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $doctrine->getManager();
            $entityManager->persist($typeEvenement);
            $entityManager->flush();

            $this->addFlash('success', 'Type dévénement ajouté avec succès !');

            return $this->redirectToRoute('typeevenement_list');
        }

        return $this->render('type_evenement/add.html.twig', [
            'formTypeEvenement' => $form
        ]);
    }

    #[Route('/typeevenement/update/{id}', name: 'update_typeevenement')]
    public function update($id, TypeEvenementRepository $repository, Request $request, Registry $doctrine)
    {
        $typeEvenement = $repository->find($id);
        $form = $this->createForm(TypeEvenementType::class, $typeEvenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $doctrine->getManager();
            $em->flush();

            $this->addFlash('success', 'Type d\'événement modifié avec succès !');
            return $this->redirectToRoute("typeevenement_list");
        }

        return $this->render("type_evenement/update.html.twig",
            ["formTypeEvenement" => $form]);
    }

    #[Route('/typeevenement/delete/{id}', name: 'delete_typeevenement')]
    public function deleteTypeEvenement(Registry $doctrine, $id, TypeEvenementRepository $repository): Response
    {
        $typeEvenement = $repository->find($id);

        if (!$typeEvenement) {
            $this->addFlash('error', 'Type d\'événement non trouvé !');
            return $this->redirectToRoute('typeevenement_list');
        }

        $em = $doctrine->getManager();
        $em->remove($typeEvenement);
        $em->flush();

        $this->addFlash('success', 'Type d\'événement supprimé avec succès !');

        return $this->redirectToRoute('typeevenement_list');
    }
}
