<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Enums\EventAction;
use App\Enums\EventTarget;
use PDO;

final class EventRepository
{
    private readonly PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::getConnection();
    }

    public function create(
        int $userId,
        EventAction $action,
        ?EventTarget $target = null
    ): void {
        $statement = $this->pdo->prepare(
            'INSERT INTO events (user_id, action, target) VALUES (:user_id, :action, :target)'
        );
        $statement->execute([
            'user_id' => $userId,
            'action' => $action->value,
            'target' => $target?->value,
        ]);
    }
}
