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

    public function buildFilter(?string $date, ?string $userId, ?string $action, ?string $page): EventFilter
    {
        return new EventFilter(
            $this->parseDate($date),
            $this->parsePositiveInt($userId),
            $action === null ? null : EventAction::tryFrom($action),
            $this->parsePositiveInt($page, intdiv(PHP_INT_MAX, self::PAGE_SIZE)) ?? 1
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

    private function parsePositiveInt(?string $value, int $max = PHP_INT_MAX): ?int
    {
        $number = filter_var($value, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => $max],
        ]);

        return is_int($number) ? $number : null;
    }
}
