<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Dto\EventFilter;
use App\Enums\EventAction;
use App\Enums\EventTarget;

interface EventRepositoryInterface
{
    public function create(int $userId, EventAction $action, ?EventTarget $target = null): void;

    /**
     * Newest events first.
     *
     * @return list<array{id: int, created_at: string, username: string, action: string, target: string|null}>
     */
    public function findFiltered(EventFilter $filter, int $limit, int $offset): array;

    /**
     * One row per day that has events, sorted by day.
     *
     * @return list<array{
     *     day: string,
     *     page_a_views: string|int|null,
     *     page_b_views: string|int|null,
     *     buy_cow_clicks: string|int|null,
     *     download_clicks: string|int|null
     * }>
     */
    public function dailyCounts(?int $userId = null): array;
}
