<?php
// src/Controller/CalendarController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\FullCalendarService;
use App\Service\HolidayApiService;
use App\Repository\EvenementRepository;

class CalendarController extends AbstractController
{
    #[Route('/calendar', name: 'calendar_view')]
    public function index(): Response
    {
        return $this->render('calendar/index.html.twig');
    }

    // In CalendarController.php
    #[Route('/calendar/test-simple', name: 'calendar_test_simple')]
    public function testSimple(): Response
    {
        return $this->render('calendar/test_simple.html.twig');
    }

    #[Route('/calendar/events.json', name: 'calendar_events_json')]
public function eventsJson(
    Request $request,
    FullCalendarService $calendarService,
    EvenementRepository $evenementRepository,
    HolidayApiService $holidayService
): JsonResponse {
    $start = $request->query->get('start', '2025-01-01');
    $end = $request->query->get('end', '2025-12-31');

    // Log the request
    error_log("=== JSON ENDPOINT CALLED ===");
    error_log("Start: {$start}, End: {$end}");

    $events = [];

    try {
        // First, try to get events from service
        $events = $calendarService->getEventsForDateRange(
            new \DateTime($start),
            new \DateTime($end)
        );

        error_log("Service returned " . count($events) . " events");

        // If no events, try direct database query
        if (empty($events)) {
            error_log("No events from service, trying direct query...");

            // Get ALL events from database (for debugging)
            $allEvents = $evenementRepository->findAll();
            error_log("Direct query found " . count($allEvents) . " total events");

            foreach ($allEvents as $event) {
                error_log("Event in DB: " . $event->getTitre() .
                         " at " . $event->getDateDebut()->format('Y-m-d'));

                // Manually format if within range
                $eventStart = $event->getDateDebut();
                $eventEnd = $event->getDateFin();

                if ($eventStart >= new \DateTime($start) && $eventStart <= new \DateTime($end)) {
                    $events[] = [
                        'id' => 'db-' . $event->getId(),
                        'title' => $event->getTitre(),
                        'start' => $eventStart->format('Y-m-d'),
                        'end' => $eventEnd->format('Y-m-d'),
                        'color' => '#4CAF50',
                        'allDay' => true
                    ];
                }
            }

            // Add holidays manually
            error_log("Adding holidays manually...");
            $year = 2025;
            $holidays = $holidayService->getTunisianHolidays($year);
            error_log("Found " . count($holidays) . " holidays for {$year}");

            foreach ($holidays as $holiday) {
                $events[] = [
                    'id' => 'holiday-' . $holiday['date'],
                    'title' => '🎉 ' . ($holiday['localName'] ?? $holiday['name']),
                    'start' => $holiday['date'],
                    'color' => '#FF6B6B',
                    'allDay' => true,
                    'className' => 'fc-holiday-event'
                ];
            }
        }

    } catch (\Exception $e) {
        error_log("ERROR in eventsJson: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());

        // Ultimate fallback
        $events = [
            [
                'id' => 'camping-manual',
                'title' => 'Camping (Manual Fallback)',
                'start' => '2025-12-14',
                'end' => '2025-12-17',
                'color' => '#2196F3',
                'allDay' => true
            ],
            [
                'id' => 'holiday-manual',
                'title' => '🎉 عيد الثورة',
                'start' => '2025-12-17',
                'color' => '#FF6B6B',
                'allDay' => true,
                'className' => 'fc-holiday-event'
            ]
        ];
    }

    error_log("Returning " . count($events) . " events");
    error_log("=== END JSON REQUEST ===");

    $response = new JsonResponse($events);
    $response->headers->set('Access-Control-Allow-Origin', '*');

    return $response;
}
}
