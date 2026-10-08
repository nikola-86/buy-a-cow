<?php

declare(strict_types=1);

namespace App\Services;

use App\Dto\EventFilter;
use App\Enums\EventAction;
use App\Repositories\EventRepositoryInterface;
use DateTimeImmutable;

final class StatisticsService
{
    public const PAGE_SIZE = 50;

    public function __construct(
        private readonly EventRepositoryInterface $eventRepository
    ) {
    }

    public function buildFilter(?string $date, ?int $userId, ?string $action, ?int $page): EventFilter
    {
        return new EventFilter(
            $this->parseDate($date),
            $userId,
            $action === null ? null : EventAction::tryFrom($action),
            // Larger page numbers would overflow the OFFSET calculation.
            $page !== null && $page >= 1 && $page <= intdiv(PHP_INT_MAX, self::PAGE_SIZE) ? $page : 1
        );
    }

    /**
     * @return array{
     *     events: list<array{id: int, created_at: string, username: string, action: string, target: string|null}>,
     *     hasNextPage: bool
     * }
     */
    public function findEvents(EventFilter $filter): array
    {
        // One extra row reveals whether a next page exists without a COUNT(*) query.
        $events = $this->eventRepository->findFiltered(
            $filter,
            self::PAGE_SIZE + 1,
            ($filter->page - 1) * self::PAGE_SIZE
        );

        return [
            'events' => array_slice($events, 0, self::PAGE_SIZE),
            'hasNextPage' => count($events) > self::PAGE_SIZE,
        ];
    }

    private function parseDate(?string $date): ?DateTimeImmutable
    {
        if ($date === null) {
            return null;
        }

        $parsedDate = DateTimeImmutable::createFromFormat('!Y-m-d', $date);

        // The round trip rejects overflowing dates such as 2026-02-31.
        return $parsedDate !== false && $parsedDate->format('Y-m-d') === $date ? $parsedDate : null;
    }
}
