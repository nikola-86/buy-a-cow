<?php

declare(strict_types=1);

namespace App\Dto;

use App\Enums\EventAction;
use DateTimeImmutable;

final class EventFilter
{
    public function __construct(
        public readonly ?DateTimeImmutable $date = null,
        public readonly ?int $userId = null,
        public readonly ?EventAction $action = null,
        public readonly int $page = 1
    ) {
    }
}
