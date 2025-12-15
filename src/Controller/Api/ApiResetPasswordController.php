<?php

namespace App\Controller\Api;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/forgot-password')]
class ApiResetPasswordController extends AbstractController
{
    #[Route('/request', name: 'api_forgot_password_request', methods: ['POST'])]
    public function request(Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;

        if (!$email) {
            return new JsonResponse(['error' => 'Email is required'], Response::HTTP_BAD_REQUEST);
        }

        $user = $userRepository->findOneBy(['email' => $email]);

        // Always return success to prevent email enumeration
        if ($user) {
            // Generate secure random token
            $resetToken = bin2hex(random_bytes(32));
            
            // Set token expiration to 1 hour from now
            $expiresAt = new \DateTime('+1 hour');
            
            $user->setResetToken($resetToken);
            $user->setResetTokenExpiresAt($expiresAt);
            
            $entityManager->flush();

            // IN DEVELOPMENT ONLY: Return token in response for testing
            // In production, you would send an email here
            return new JsonResponse([
                'message' => 'Reset link sent (simulation)',
                'dev_token' => $resetToken, 
                'expires_in' => '1 hour'
            ]);
        }

        return new JsonResponse(['message' => 'If this email exists, a reset link has been sent.']);
    }

    #[Route('/reset', name: 'api_forgot_password_reset', methods: ['POST'])]
    public function reset(
        Request $request,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $token = $data['token'] ?? null;
        $newPassword = $data['password'] ?? null;

        if (!$token || !$newPassword) {
            return new JsonResponse(['error' => 'Token and password are required'], Response::HTTP_BAD_REQUEST);
        }

        if (strlen($newPassword) < 6) {
            return new JsonResponse(['error' => 'Password must be at least 6 characters'], Response::HTTP_BAD_REQUEST);
        }

        $user = $userRepository->findOneBy(['resetToken' => $token]);

        if (!$user) {
            return new JsonResponse(['error' => 'Invalid token'], Response::HTTP_BAD_REQUEST);
        }

        // Check expiration
        $now = new \DateTime();
        if ($user->getResetTokenExpiresAt() < $now) {
            return new JsonResponse(['error' => 'Token expired'], Response::HTTP_BAD_REQUEST);
        }

        // Update password
        $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
        $user->setPassword($hashedPassword);

        // Clear token
        $user->setResetToken(null);
        $user->setResetTokenExpiresAt(null);

        $entityManager->flush();

        return new JsonResponse(['message' => 'Password updated successfully']);
    }
}
