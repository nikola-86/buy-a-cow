<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\EventAction;
use App\Enums\EventTarget;
use App\Repositories\EventRepository;

final class ActivityTracker
{
    public function __construct(
        private readonly EventRepository $eventRepository = new EventRepository()
    ) {
    }

    public function track(
        int $userId,
        EventAction $action,
        ?EventTarget $target = null
    ): void {
        $this->eventRepository->create($userId, $action, $target);
    }
}
