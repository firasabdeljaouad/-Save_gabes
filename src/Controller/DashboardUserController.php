<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\AdminUserFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class DashboardUserController extends AbstractController
{
    #[Route('/dashboard/users', name: 'dashboard_users', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        UserRepository $userRepository
    ): Response {
        $user = new User();
        $form = $this->createForm(AdminUserFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            $role = $form->get('role')->getData() ?? 'ROLE_USER';

            $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
            $user->setRoles([$role]);
            $user->setCreatedAt(new \DateTime());
            $user->setUpdatedAt(new \DateTime());

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'User created successfully.');

            return $this->redirectToRoute('dashboard_users');
        }

        return $this->render('dashboard/users/index.html.twig', [
            'users' => $userRepository->findAll(),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/dashboard/users/{id}/edit', name: 'dashboard_users_edit', methods: ['GET', 'POST'])]
    public function edit(
        User $user,
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $form = $this->createForm(AdminUserFormType::class, $user, ['is_edit' => true]);
        $currentRole = in_array('ROLE_ADMIN', $user->getRoles(), true) ? 'ROLE_ADMIN' : 'ROLE_USER';
        $form->get('role')->setData($currentRole);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            if (!empty($plainPassword)) {
                $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
            }

            $role = $form->get('role')->getData() ?? 'ROLE_USER';
            $user->setRoles([$role]);
            $user->setUpdatedAt(new \DateTime());

            $entityManager->flush();

            $this->addFlash('success', 'User updated successfully.');

            return $this->redirectToRoute('dashboard_users');
        }

        return $this->render('dashboard/users/edit.html.twig', [
            'userForm' => $form->createView(),
            'userEntity' => $user,
        ]);
    }

    #[Route('/dashboard/users/{id}/delete', name: 'dashboard_users_delete', methods: ['POST'])]
    public function delete(User $user, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete_user_'.$user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
            $this->addFlash('success', 'User deleted successfully.');
        }

        return $this->redirectToRoute('dashboard_users');
    }
}


