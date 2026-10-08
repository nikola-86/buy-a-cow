<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\EventRepositoryInterface;
use DateInterval;
use DatePeriod;
use DateTimeImmutable;

final class ReportService
{
    public function __construct(
        private readonly EventRepositoryInterface $eventRepository
    ) {
    }

    /**
     * @return list<array{
     *     day: string,
     *     page_a_views: int,
     *     page_b_views: int,
     *     buy_cow_clicks: int,
     *     download_clicks: int
     * }>
     */
    public function dailyActivity(?int $userId = null): array
    {
        return $this->fillMissingDays(
            $this->eventRepository->dailyCounts($userId),
            new DateTimeImmutable('today')
        );
    }

    /**
     * Returns one row per day from the first day in $dailyCounts to $today, with zeros for days without events.
     *
     * @param list<array{
     *     day: string,
     *     page_a_views: string|int|null,
     *     page_b_views: string|int|null,
     *     buy_cow_clicks: string|int|null,
     *     download_clicks: string|int|null
     * }> $dailyCounts Rows sorted by day
     *
     * @return list<array{
     *     day: string,
     *     page_a_views: int,
     *     page_b_views: int,
     *     buy_cow_clicks: int,
     *     download_clicks: int
     * }>
     */
    public function fillMissingDays(array $dailyCounts, DateTimeImmutable $today): array
    {
        if ($dailyCounts === []) {
            return [];
        }

        $countsByDay = array_column($dailyCounts, null, 'day');
        $days = new DatePeriod(
            new DateTimeImmutable($dailyCounts[0]['day']),
            new DateInterval('P1D'),
            $today->setTime(0, 0)->modify('+1 day')
        );
        $report = [];

        foreach ($days as $day) {
            $dayKey = $day->format('Y-m-d');
            $counts = $countsByDay[$dayKey] ?? [];
            $report[] = [
                'day' => $dayKey,
                'page_a_views' => (int) ($counts['page_a_views'] ?? 0),
                'page_b_views' => (int) ($counts['page_b_views'] ?? 0),
                'buy_cow_clicks' => (int) ($counts['buy_cow_clicks'] ?? 0),
                'download_clicks' => (int) ($counts['download_clicks'] ?? 0),
            ];
        }

        return $report;
    }
}
