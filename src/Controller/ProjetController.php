<?php

namespace App\Controller;

use App\Entity\Project;
use App\Form\ProjectFromType;
use App\Repository\DonationRepository;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

final class ProjetController extends AbstractController
{
   /* #[Route('/projet', name: 'app_projets')]
    public function index(ProjectRepository $projectRepository): Response
    {
        $projets = $projectRepository->findAll();

        return $this->render('projet/index.html.twig', [
            'projets' => $projets,
        ]);
    }*/

    #[Route('/list_projet', name: 'app_list_projets')]
    public function list(ProjectRepository $projectRepository): Response
    {
        $projets = $projectRepository->findAll();

        return $this->render('dashboard/list_project/list_project.html.twig', [
            'projets_list' => $projets,
        ]);
    }
    #[Route('/cause/{id}', name: 'app_cause_details')]
    public function show(
        ProjectRepository $projectRepository,
        DonationRepository $donationRepository,
        int $id): Response
    {
        $projet = $projectRepository->find($id);

      //  if (!$projet) {
       //     throw $this->createNotFoundException('L\'article avec l\'ID ' . $id . ' n\'existe pas.');
       // }
        $donations = $donationRepository->findBy(['project' => $projet]);

        return $this->render('cause_details/index.html.twig', [
            'projet' => $projet,
            'donations' => $donations,
        ]);
    }

    #[Route('/projet/new', name: 'app_projet')]
    public function addProject(EntityManagerInterface $em,Request $request, SluggerInterface $slugger): Response
    {
        $project = new Project();

        $form = $this->createForm(ProjectFromType::class,$project);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $em->persist($project);

            $imageFile = $form->get('image')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $imageFile->move(
                        $this->getParameter('projects_images_directory'),
                        $newFilename)
                    ;
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $project->setImage($newFilename);
            }

            $em->flush();
            return $this->redirectToRoute('app_projets');
        }
        return $this->render('dashboard/list_project/add_project_dash.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/projet/{id}/update', name: 'update_app_projet')]
    public function updateProject(
        Project $project,
        EntityManagerInterface $em,
        Request $request,
        SluggerInterface $slugger
    ): Response {
        $oldImage = $project->getImage(); // garder l'ancienne image

        $form = $this->createForm(ProjectFromType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $imageFile = $form->get('image')->getData();

            // Si utilisateur a uploadé une nouvelle image
            if ($imageFile) {

                // 1) Générer un nouveau nom
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                // 2) Déplacer l'image uploadée
                try {
                    $imageFile->move(
                        $this->getParameter('projects_images_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // gérer l'erreur
                }

                // 3) Supprimer l’ancienne image si elle existe
                if ($oldImage) {
                    $oldPath = $this->getParameter('projects_images_directory') . '/' . $oldImage;
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                    }
                }

                // 4) Mettre à jour l'entité
                $project->setImage($newFilename);
            } else {
                // garder l'ancienne image
                $project->setImage($oldImage);
            }

            $em->persist($project);
            $em->flush();

            return $this->redirectToRoute('app_projets');
        }

        return $this->render('dashboard/list_project/update.html.twig', [
            'form' => $form->createView(),
            'project' => $project
        ]);
    }
    #[Route('/projet/{id}/delete', name: 'delete_app_projet')]
public function deleteProject(Project $project,EntityManagerInterface $em,Request $request): Response
    {
        foreach ($project->getDonations() as $donation) {
            $em->remove($donation);
        }
        $em->remove($project);
        $em->flush();
        return $this->redirectToRoute('app_projets');
    }

#[Route('/projet', name: 'app_projets')]

    public function listProject(
        Request            $request,
        ProjectRepository  $projectRepository,
        PaginatorInterface $paginator,

    ): Response
    {
        $search = $request->query->get('search');
        $status = $request->query->get('status');
        $dateFrom = $request->query->get('date_from');
        $dateTo = $request->query->get('date_to');

        $criteria = array_filter([
            'search' => $search,
            'status' => $status,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ], function ($value) {
            return $value !== null && $value !== '';
        });

        if (!empty($criteria)) {
            $query  = $projectRepository->findProjectByCriteriaQuery($criteria);
        } else {
            $query  = $projectRepository->findAllQuery();
        }
        $projets = $paginator->paginate(
            $query,                                    // Query object
            $request->query->getInt('page', 1),       // Current page number, default 1
            6                                          // Items per page
        );
        return $this->render("projet/index.html.twig", [
            "projets" => $projets,
            "currentFilters" => $criteria
        ]);
    }

}
