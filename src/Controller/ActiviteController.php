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
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

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
    public function list(ActiviteRepository $activiteRepository, PaginatorInterface $paginator, Request $request){
        $query = $activiteRepository->findAll();
        $activites = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );
        return $this->render('activite/list_activite.html.twig', [
            'activites_list' => $activites,
        ]);
    }
    #[Route('/Details/{id}', name: 'app_activite_details')]
    public function show(
        ActiviteRepository $activiteRepository,
        int $id): Response
    {
        $activite = $activiteRepository->find($id);

        if (!$activite) {
             throw $this->createNotFoundException('L\'activité avec l\'ID ' . $id . ' n\'existe pas.');
        }

        return $this->render('ActiviteDetails/index.html.twig', [
            'activite' => $activite,
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

            return $this->redirectToRoute('app_list_Activite');
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
        Activite $activite,
        MailerInterface $mailer,
        #[Autowire(param: 'app.mailer.sender_email')] string $senderEmail,
        #[Autowire(param: 'app.mailer.sender_name')] string $senderName
    ): Response {
        $benevole = new Benevole();
        $benevole->addActivite($activite);

        $form = $this->createForm(BenevoleType::class, $benevole);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($benevole);
            $em->flush();

            // Send confirmation email
            $email = (new Email())
                ->from(new Address($senderEmail, $senderName))
                ->to($benevole->getEmail())
                ->subject('Confirmation de votre inscription bénévole')
                ->html(sprintf(
                    '<p>Bonjour %s,</p>
                    <p>Merci de vous être inscrit comme bénévole pour l\'activité <strong>%s</strong>.</p>
                    <p>Voici vos détails :</p>
                    <ul>
                        <li>Nom : %s</li>
                        <li>Email : %s</li>
                        <li>Téléphone : %s</li>
                    </ul>
                    <p>À bientôt !</p>',
                    htmlspecialchars($benevole->getNom()),
                    htmlspecialchars($activite->getTitle() ?? 'Activité'),
                    htmlspecialchars($benevole->getNom()),
                    htmlspecialchars($benevole->getEmail()),
                    htmlspecialchars($benevole->getTelephone())
                ));

            $mailer->send($email);

            $this->addFlash('success', 'Le bénévole a été ajouté avec succès. The email ete envoye.');

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

            return $this->redirectToRoute('app_list_Activite');
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

        return $this->redirectToRoute('app_list_Activite');
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
