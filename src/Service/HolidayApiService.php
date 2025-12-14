<?php
// src/Service/HolidayApiService.php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class HolidayApiService
{
    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function getTunisianHolidays(int $year): array
    {
        try {
            $response = $this->client->request(
                'GET',
                "https://date.nager.at/api/v3/PublicHolidays/{$year}/TN",
                [
                    'timeout' => 5,
                    'max_duration' => 10
                ]
            );

            return $response->toArray();
        } catch (\Exception $e) {
            // Return empty array instead of throwing
            error_log("Holiday API error for year {$year}: " . $e->getMessage());
            return [];
        }
    }
}
