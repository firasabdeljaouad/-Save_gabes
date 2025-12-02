<?php

namespace App\Controller;

use App\Entity\Activite;
use App\Form\ActiviteType;
use App\Repository\ActiviteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/activite')]
class ActiviteController extends AbstractController
{
    #[Route('/', name: 'app_activite_index', methods: ['GET'])]
    public function index(ActiviteRepository $repo): Response
    {
        return $this->render('activite/index.html.twig', [
            'activites' => $repo->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_activite_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $activite = new Activite();
        $form = $this->createForm(ActiviteType::class, $activite);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($activite);
            $em->flush();

            return $this->redirectToRoute('app_activite_index');
        }

        return $this->render('activite/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_activite_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Activite $activite): Response
    {
        return $this->render('activite/show.html.twig', [
            'activite' => $activite,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_activite_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Request $request, Activite $activite, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ActiviteType::class, $activite);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('app_activite_index');
        }

        return $this->render('activite/edit.html.twig', [
            'form' => $form,
            'activite' => $activite,
        ]);
    }

    #[Route('/{id}', name: 'app_activite_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, Activite $activite, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$activite->getId(), $request->get('_token'))) {
            $em->remove($activite);
            $em->flush();
        }

        return $this->redirectToRoute('app_activite_index');
    }
}
