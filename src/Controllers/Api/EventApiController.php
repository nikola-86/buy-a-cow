<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Enums\EventAction;
use App\Enums\EventTarget;
use App\Services\ActivityTracker;
use JsonException;
use UnexpectedValueException;

final class EventApiController
{
    public function __construct(
        private readonly ActivityTracker $activityTracker,
        private readonly Auth $auth
    ) {
    }

    public function store(Request $request): void
    {
        try {
            $payload = $request->json();
        } catch (JsonException | UnexpectedValueException) {
            Response::json(['error' => 'Invalid JSON body'], 400);
        }

        $target = is_string($payload['target'] ?? null) ? EventTarget::tryFrom($payload['target']) : null;

        if ($target !== EventTarget::BuyCow) {
            Response::json(['error' => 'Unsupported event target'], 422);
        }

        $this->activityTracker->track($this->auth->requireUserId(), EventAction::ButtonClick, $target);

        Response::json([
            'action' => EventAction::ButtonClick->value,
            'target' => $target->value,
        ], 201);
    }
}
