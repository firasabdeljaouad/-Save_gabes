<?php

namespace App\Controller\Api;

use App\Entity\Activite;
use App\Repository\ActiviteRepository;
use App\Repository\BenevoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;

#[Route('/api')]
#[OA\Tag(name: 'Activities')]
class ActiviteApiController extends AbstractController
{
    private $activiteRepository;
    private $benevoleRepository;
    private $entityManager;

    public function __construct(
        ActiviteRepository $activiteRepository,
        BenevoleRepository $benevoleRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->activiteRepository = $activiteRepository;
        $this->benevoleRepository = $benevoleRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * Retrieves the list of all activities.
     */
    #[Route('/activities', name: 'api_activities_list', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns the list of all activities.',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Activite::class))
        )
    )]
    public function list(): JsonResponse
    {
        $activities = $this->activiteRepository->findAll();
        return $this->json($activities);
    }

    /**
     * Assigns a volunteer to a specific activity.
     */
    #[Route('/activities/{id}/assign-volunteer', name: 'api_activities_assign_volunteer', methods: ['POST'])]
    #[OA\RequestBody(
        description: 'The ID of the volunteer to assign',
        required: true,
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'benevole_id', type: 'integer', example: 1)
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Volunteer assigned successfully.'
    )]
    #[OA\Response(
        response: 400,
        description: 'Invalid input, activity is in the past, or volunteer already assigned.'
    )]
    #[OA\Response(
        response: 404,
        description: 'Activity or Volunteer not found.'
    )]
    public function assignVolunteer(Request $request, Activite $activite): JsonResponse
    {
        // Business Rule: Check if the activity is in the past.
        if ($activite->isPast()) {
            return $this->json(['error' => 'Cannot assign volunteer to an activity that is in the past.'], Response::HTTP_BAD_REQUEST);
        }

        $data = json_decode($request->getContent(), true);
        $benevoleId = $data['benevole_id'] ?? null;

        if (!$benevoleId) {
            return $this->json(['error' => 'benevole_id is required.'], Response::HTTP_BAD_REQUEST);
        }

        $benevole = $this->benevoleRepository->find($benevoleId);
        if (!$benevole) {
            return $this->json(['error' => 'Volunteer not found.'], Response::HTTP_NOT_FOUND);
        }

        // Business Rule: Use the métier in the entity.
        $wasAssigned = $activite->assignBenevole($benevole);

        if (!$wasAssigned) {
            return $this->json(['message' => 'This volunteer is already assigned to this activity.'], Response::HTTP_BAD_REQUEST);
        }

        // If assigned, save the change.
        $this->entityManager->flush();

        return $this->json(['message' => 'Volunteer assigned successfully.']);
    }
}