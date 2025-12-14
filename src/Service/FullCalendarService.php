<?php
// src/Service/FullCalendarService.php
namespace App\Service;

use App\Repository\EvenementRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FullCalendarService
{
    public function __construct(
        private EvenementRepository $evenementRepository,
        private UrlGeneratorInterface $router,
        private HolidayApiService $holidayService
    ) {}

    public function getEventsForDateRange(\DateTimeInterface $start, \DateTimeInterface $end): array
    {
        $events = [];

        error_log("=== CALENDAR SERVICE DEBUG ===");
        error_log("Date range requested: " . $start->format('Y-m-d') . " to " . $end->format('Y-m-d'));

        // 1. Get database events
        error_log("Fetching database events...");
        try {
            $evenements = $this->evenementRepository->findEventsBetweenDates($start, $end);
            error_log("Found " . count($evenements) . " events in database");

            foreach ($evenements as $evenement) {
                error_log("Database event: " . $evenement->getTitre() .
                         " from " . $evenement->getDateDebut()->format('Y-m-d') .
                         " to " . $evenement->getDateFin()->format('Y-m-d'));

                $formattedEvent = $this->formatEventForCalendar($evenement);
                $events[] = $formattedEvent;
            }
        } catch (\Exception $e) {
            error_log("Error fetching database events: " . $e->getMessage());
        }

        // 2. Get holidays
        error_log("Fetching holidays...");
        $startYear = (int)$start->format('Y');
        $endYear = (int)$end->format('Y');

        error_log("Checking holidays for years: {$startYear} to {$endYear}");

        for ($year = $startYear; $year <= $endYear; $year++) {
            try {
                $holidays = $this->holidayService->getTunisianHolidays($year);
                error_log("Found " . count($holidays) . " holidays for year {$year}");

                foreach ($holidays as $holiday) {
                    $holidayDate = new \DateTime($holiday['date']);

                    // Check if holiday is within range
                    if ($holidayDate >= $start && $holidayDate <= $end) {
                        error_log("Adding holiday: " . ($holiday['localName'] ?? $holiday['name']) .
                                 " on " . $holidayDate->format('Y-m-d'));

                        $events[] = $this->formatHolidayForCalendar($holiday);
                    }
                }
            } catch (\Exception $e) {
                error_log("Error fetching holidays for year {$year}: " . $e->getMessage());
            }
        }

        error_log("Total events to return: " . count($events));
        error_log("=== END DEBUG ===");

        return $events;
    }

    private function formatEventForCalendar($evenement): array
    {
        $color = $this->getEventColor($evenement->getStatus());

        $startDate = $evenement->getDateDebut();
        $endDate = $evenement->getDateFin();

        // Your event is from 2025-12-14 00:00:00 to 2025-12-17 00:00:00
        // This is a multi-day all-day event
        $isAllDay = $this->isAllDayEvent($evenement);

        return [
            'id' => 'event-' . $evenement->getId(),
            'title' => $evenement->getTitre(),
            'start' => $startDate->format('Y-m-d'), // Just date for all-day events
            'end' => $endDate->format('Y-m-d'),     // Just date for all-day events
            'description' => $evenement->getDescription(),
            'location' => $evenement->getLieu(),
            'type' => $evenement->getTypeEvenement()?->getNom(),
            'status' => $evenement->getStatus(),
            'color' => $color,
            'textColor' => '#FFFFFF',
            'borderColor' => $color,
            'url' => $this->router->generate('lists_evenement_front') . '#event-' . $evenement->getId(),
            'allDay' => $isAllDay,
            'extendedProps' => [
                'organizer' => $evenement->getTypeEvenement()?->getOrganisateur(),
                'participants' => $evenement->getNombreParticipants(),
                'image' => $evenement->getImageName() ? '/images/evenements/' . $evenement->getImageName() : null,
                'isHoliday' => false
            ]
        ];
    }

    private function isAllDayEvent($evenement): bool
    {
        $start = $evenement->getDateDebut();
        $end = $evenement->getDateFin();

        // Your event starts and ends at midnight, spans multiple days
        // This should be treated as all-day
        $startTime = $start->format('H:i:s');
        $endTime = $end->format('H:i:s');

        // If it's exactly at midnight or spans multiple days, it's all-day
        if ($startTime === '00:00:00' || $endTime === '00:00:00') {
            return true;
        }

        // Check if spans multiple days
        $startDay = $start->format('Y-m-d');
        $endDay = $end->format('Y-m-d');

        return $startDay !== $endDay;
    }

    private function formatHolidayForCalendar(array $holiday): array
    {
        $holidayDate = new \DateTime($holiday['date']);

        return [
            'id' => 'holiday-' . $holiday['date'],
            'title' => '🎉 ' . ($holiday['localName'] ?? $holiday['name']),
            'start' => $holidayDate->format('Y-m-d'),
            'end' => $holidayDate->format('Y-m-d'),
            'color' => '#FF6B6B',
            'textColor' => '#FFFFFF',
            'borderColor' => '#FF6B6B',
            'allDay' => true,
            'className' => 'fc-holiday-event',
            'extendedProps' => [
                'isHoliday' => true,
                'country' => 'Tunisia',
                'type' => 'public_holiday',
                'description' => 'Jour férié en Tunisie'
            ]
        ];
    }

    private function getEventColor(?string $status): string
    {
        return match(strtolower($status ?? '')) {
            'confirmed', 'confirmé' => '#4CAF50', // Green
            'pending', 'en attente' => '#FF9800', // Orange
            'cancelled', 'annulé' => '#F44336',   // Red
            'completed', 'terminé' => '#2196F3',  // Blue
            default => '#9C27B0',                 // Purple
        };
    }
}
