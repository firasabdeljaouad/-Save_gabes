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
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Benevole;




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

            // Récupérer les bénévoles cochés
            foreach ($activite->getBenevoles() as $benevole) {
                // Synchroniser l’autre côté (pas obligatoire si mappedBy bien configuré)
                $benevole->addActivite($activite);
            }

            $em->persist($activite);
            $em->flush();

            return $this->redirectToRoute('app_activite_index');
        }

        return $this->render('activite/new.html.twig', [
            'activite' => $activite,
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

    #[Route('/{id}/edit', name: 'app_activite_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Activite $activite, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ActiviteType::class, $activite);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // MAJ MANY TO MANY
            foreach ($activite->getBenevoles() as $benevole) {
                if (!$benevole->getActivites()->contains($activite)) {
                    $benevole->addActivite($activite);
                }
            }

            $em->flush();

            return $this->redirectToRoute('app_activite_index');
        }

        return $this->render('activite/edit.html.twig', [
            'activite' => $activite,
            'form' => $form,
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


    #[Route('/details/{id}', name: 'activite_details', methods: ['GET'])]
    public function details(int $id, ActiviteRepository $activiteRepository): JsonResponse
    {
        $activite = $activiteRepository->find($id);

        if (!$activite) {
            return $this->json(['error' => 'Activité non trouvée'], 404);
        }

        return $this->json([
            'titre' => $activite->getTitre(),
            'date' => $activite->getDate() ? $activite->getDate()->format('d/m/Y H:i') : 'Date non définie',
            'lieu' => $activite->getLieu(),
            'description' => $activite->getDescription(),
        ]);
    }

#[Route('/activites/toutes', name: 'app_activite_toutes')]
public function toutes(ActiviteRepository $activiteRepository): Response
{
    $activites = $activiteRepository->findBy([], ['date' => 'DESC']);
    $recentes = $activiteRepository->findRecentActivities(5);

    return $this->render('activite/toutes.html.twig', [
        'activites' => $activites,
        'recentes' => $recentes,
    ]);
}


#[Route('/recent', name: 'app_activite_recent', methods: ['GET'])]
    public function recent(ActiviteRepository $activiteRepository): Response
    {
        $activites = $activiteRepository->findRecentActivities(5);

        return $this->render('activite/recent.html.twig', [
            'activites' => $activites,
        ]);
    }

#[Route('/activite/{id}/participer', name: 'participer_activite')]
public function participer(
    Activite $activite,
    ManagerRegistry $doctrine
): Response {
    // TODO: remplacer ceci par le bénévole connecté
    $benevole = $doctrine->getRepository(Benevole::class)->find(1);

    if (!$benevole) {
        $this->addFlash('error', "Bénévole non trouvé.");
        return $this->redirectToRoute('app_activite_list');
    }

    // Ajouter si pas déjà inscrit
    if (!$activite->getBenevoles()->contains($benevole)) {
        $activite->addBenevole($benevole);

        // Pour la relation inverse
        $benevole->addActivite($activite);

        $em = $doctrine->getManager();
        $em->persist($activite);
        $em->persist($benevole);
        $em->flush();

        $this->addFlash('success', "Vous participez maintenant à cette activité !");
    } else {
        $this->addFlash('warning', "Vous participez déjà à cette activité.");
    }

    return $this->redirectToRoute('app_activite_toutes');
}



}
