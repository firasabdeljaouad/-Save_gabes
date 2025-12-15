<?php

namespace App\Controller;

use App\Entity\Activite;
use App\Entity\Benevole;
use App\Form\BenevoleType;
use App\Repository\ActiviteRepository;
use App\Repository\BenevoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BenevoleController extends AbstractController
{

    #[Route('/benevole', name: 'benevole')]
    public function index(): Response
    {
        return $this->render('benevole/index.html.twig', [
            'controller_name' => 'BenevoleController',
        ]);
    }


    #[Route('/list_benevole/{id}',name: 'app_benevole_list_benevole')]
    public function show(
        int $id,
        ActiviteRepository $activiteRepository,
        BenevoleRepository $benevoleRepository,
        Request $request,
        PaginatorInterface $paginator
    ): Response {
        $activite = $activiteRepository->find($id);
        if(!$activite){
            throw $this->createNotFoundException('there is no benevole');

        }

        $search = $request->query->get('q');
        
        $query = $benevoleRepository->findByActiviteQuery($id, $search);

        $benevoles = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );

        return $this->render('benevole/benevole_list.html.twig', [
            'benevoles' => $benevoles,
            'activite' => $activite,
            'search' => $search,
        ]);
    }

    #[Route('/list_benevole/{id}/pdf', name: 'app_benevole_list_pdf')]
    public function downloadPdf(
        int $id,
        ActiviteRepository $activiteRepository
    ): Response {
        $activite = $activiteRepository->find($id);
        if (!$activite) {
            throw $this->createNotFoundException('Activity not found');
        }

        $benevoles = $activite->getBenevoles();

        $html = $this->renderView('benevole/pdf.html.twig', [
            'benevoles' => $benevoles,
            'activite' => $activite,
        ]);

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return new Response(
            $dompdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="benevoles_' . $activite->getId() . '.pdf"',
            ]
        );
    }
    #[Route('/benevole/{id}/edit', name: 'app_benevole_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Benevole $benevole,
        EntityManagerInterface $em
    ): Response {
        $form = $this->createForm(BenevoleType::class, $benevole);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Le bénévole a été modifié avec succès.');

            return $this->redirectToRoute('app_benevole_show', ['id' => $benevole->getId()]);
        }

        return $this->render('benevole/edit.html.twig', [
            'form' => $form,
            'benevole' => $benevole,
        ]);
    }

    #[Route('/benevole/{id}/delete', name: 'app_benevole_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Benevole $benevole,
        EntityManagerInterface $em
    ): Response {
        if ($this->isCsrfTokenValid('delete'.$benevole->getId(), $request->request->get('_token'))) {
            $em->remove($benevole);
            $em->flush();

            $this->addFlash('success', 'Le bénévole a été supprimé avec succès.');
        }

        return $this->redirectToRoute('app_benevole');
    }
}
