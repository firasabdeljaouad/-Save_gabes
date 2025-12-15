<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ForgotPasswordRequestFormType;
use App\Form\ResetPasswordFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class ResetPasswordController extends AbstractController
{
    #[Route('/reset-password/request', name: 'app_forgot_password_request')]
    public function request(Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ForgotPasswordRequestFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $user = $userRepository->findOneBy(['email' => $email]);

            // Always show success message for security (don't reveal if email exists)
            if ($user) {
                // Generate secure random token
                $resetToken = bin2hex(random_bytes(32));
                
                // Set token expiration to 1 hour from now
                $expiresAt = new \DateTime('+1 hour');
                
                $user->setResetToken($resetToken);
                $user->setResetTokenExpiresAt($expiresAt);
                
                $entityManager->flush();

                // Store token in session to display the link
                $request->getSession()->set('reset_token', $resetToken);
                $request->getSession()->set('reset_email', $email);
            }

            return $this->redirectToRoute('app_forgot_password_check_email');
        }

        return $this->render('reset_password/request.html.twig', [
            'requestForm' => $form->createView(),
        ]);
    }

    #[Route('/reset-password/check-email', name: 'app_forgot_password_check_email')]
    public function checkEmail(Request $request): Response
    {
        $resetToken = $request->getSession()->get('reset_token');
        $email = $request->getSession()->get('reset_email');

        // Clear session data
        $request->getSession()->remove('reset_token');
        $request->getSession()->remove('reset_email');

        return $this->render('reset_password/check_email.html.twig', [
            'resetToken' => $resetToken,
            'email' => $email,
        ]);
    }

    #[Route('/reset-password/reset/{token}', name: 'app_reset_password')]
    public function reset(
        Request $request,
        string $token,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $userRepository->findOneBy(['resetToken' => $token]);

        // Check if token exists
        if (!$user) {
            $this->addFlash('error', 'Ce lien de réinitialisation est invalide.');
            return $this->redirectToRoute('app_login');
        }

        // Check if token has expired
        $now = new \DateTime();
        if ($user->getResetTokenExpiresAt() < $now) {
            $this->addFlash('error', 'Ce lien de réinitialisation a expiré. Veuillez faire une nouvelle demande.');
            return $this->redirectToRoute('app_forgot_password_request');
        }

        $form = $this->createForm(ResetPasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hash and set the new password
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $form->get('plainPassword')->getData()
            );
            $user->setPassword($hashedPassword);

            // Clear the reset token
            $user->setResetToken(null);
            $user->setResetTokenExpiresAt(null);

            $entityManager->flush();

            $this->addFlash('success', 'Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('reset_password/reset.html.twig', [
            'resetForm' => $form->createView(),
        ]);
    }
}
