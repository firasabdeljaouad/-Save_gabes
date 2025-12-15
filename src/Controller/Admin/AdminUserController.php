<?php

namespace App\Controller\Admin;

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
class AdminUserController extends AbstractController
{
    #[Route('/admin/user', name: 'admin_user_index', priority: 255)]
    public function index(UserRepository $userRepository): Response
    {
        // Get only active users (not deleted)
        $users = $userRepository->createQueryBuilder('u')
            ->where('u.deletedAt IS NULL')
            ->orderBy('u.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->render('admin/user/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/admin/users/new', name: 'admin_user_create', methods: ['GET', 'POST'], priority: 255)]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
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

            return $this->redirectToRoute('admin_user_index');
        }

        // Return form for modal (GET request or form with errors)
        return $this->render('admin/user/_create_form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/users/{id}/edit', name: 'admin_user_edit', methods: ['GET', 'POST'], priority: 255, requirements: ['id' => '\d+'])]
    public function edit(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        UserRepository $userRepository
    ): Response {
        $user = $userRepository->find($id);
        if (!$user) {
            $this->addFlash('error', 'User with ID ' . $id . ' not found.');
            return $this->redirectToRoute('admin_user_index');
        }
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

            return $this->redirectToRoute('admin_user_index');
        }

        return $this->render('admin/user/edit.html.twig', [
            'userForm' => $form->createView(),
            'userEntity' => $user,
        ]);
    }

    #[Route('/admin/users/{id}/delete', name: 'admin_user_delete', methods: ['POST'], priority: 255, requirements: ['id' => '\d+'])]
    public function delete(int $id, Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {
        $user = $userRepository->find($id);
        if (!$user) {
            $this->addFlash('error', 'User with ID ' . $id . ' not found.');
            return $this->redirectToRoute('admin_user_index');
        }

        if ($this->isCsrfTokenValid('delete_user_'.$user->getId(), $request->request->get('_token'))) {
            // Soft delete: set deletedAt instead of removing
            $user->setDeletedAt(new \DateTime());
            $entityManager->flush();
            $this->addFlash('success', 'User moved to trash successfully.');
        }

        return $this->redirectToRoute('admin_user_index');
    }

    #[Route('/admin/users/{id}/restore', name: 'admin_user_restore', methods: ['POST'], priority: 255, requirements: ['id' => '\d+'])]
    public function restore(int $id, Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {
        $user = $userRepository->find($id);
        if (!$user) {
            $this->addFlash('error', 'User with ID ' . $id . ' not found.');
            return $this->redirectToRoute('admin_user_trash');
        }

        if ($this->isCsrfTokenValid('restore_user_'.$user->getId(), $request->request->get('_token'))) {
            // Restore: remove deletedAt
            $user->setDeletedAt(null);
            $entityManager->flush();
            $this->addFlash('success', 'User restored successfully.');
        }

        return $this->redirectToRoute('admin_user_trash');
    }

    #[Route('/admin/users/trash', name: 'admin_user_trash', priority: 255)]
    public function trash(UserRepository $userRepository): Response
    {
        // Get only deleted users
        $users = $userRepository->createQueryBuilder('u')
            ->where('u.deletedAt IS NOT NULL')
            ->orderBy('u.deletedAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->render('admin/user/trash.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/admin/users/{id}/permanent-delete', name: 'admin_user_permanent_delete', methods: ['POST'], priority: 255, requirements: ['id' => '\d+'])]
    public function permanentDelete(int $id, Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {
        $user = $userRepository->find($id);
        if (!$user) {
            $this->addFlash('error', 'User with ID ' . $id . ' not found.');
            return $this->redirectToRoute('admin_user_trash');
        }

        if ($this->isCsrfTokenValid('permanent_delete_user_'.$user->getId(), $request->request->get('_token'))) {
            // Permanent delete: actually remove from database
            $entityManager->remove($user);
            $entityManager->flush();
            $this->addFlash('success', 'User permanently deleted.');
        }

        return $this->redirectToRoute('admin_user_trash');
    }
}

