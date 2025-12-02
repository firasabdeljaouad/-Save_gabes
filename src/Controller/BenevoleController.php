<?php

namespace App\Controller;


namespace App\Controller;

use App\Entity\Benevole;
use App\Form\BenevoleType;
use App\Repository\BenevoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/benevole')]
class BenevoleController extends AbstractController
{
    #[Route('/', name: 'app_benevoles_index', methods: ['GET'])]
    public function index(BenevoleRepository $repo): Response
    {
        return $this->render('benevole/index.html.twig', [
            'benevoles' => $repo->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_benevoles_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $benevole = new Benevole();
        $form = $this->createForm(BenevoleType::class, $benevole);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($benevole);
            $em->flush();

            return $this->redirectToRoute('app_benevoles_index');
        }

        return $this->render('benevole/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_benevoles_show', methods: ['GET'])]
    public function show(Benevole $benevole): Response
    {
        return $this->render('benevole/show.html.twig', [
            'benevole' => $benevole,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_benevoles_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Benevole $benevole, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(BenevoleType::class, $benevole);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('app_benevoles_index');
        }

        return $this->render('benevole/edit.html.twig', [
            'form' => $form,
            'benevole' => $benevole,
        ]);
    }

    #[Route('/{id}', name: 'app_benevoles_delete', methods: ['POST'])]
    public function delete(Request $request, Benevole $benevole, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $benevole->getId(), $request->get('_token'))) {
            $em->remove($benevole);
            $em->flush();
        }

        return $this->redirectToRoute('app_benevoles_index');
    }
}

