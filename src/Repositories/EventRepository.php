<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Dto\EventFilter;
use App\Enums\EventAction;
use App\Enums\EventTarget;
use PDO;

final class EventRepository implements EventRepositoryInterface
{
    public function __construct(
        private readonly PDO $pdo
    ) {
    }

    public function create(int $userId, EventAction $action, ?EventTarget $target = null): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO events (user_id, action, target) VALUES (:user_id, :action, :target)'
        );
        $statement->execute([
            'user_id' => $userId,
            'action' => $action->value,
            'target' => $target?->value,
        ]);
    }

    public function findFiltered(EventFilter $filter, int $limit, int $offset): array
    {
        $conditions = [];
        $parameters = [];

        if ($filter->date !== null) {
            // Range on the raw column so the created_at index can be used.
            $conditions[] = 'e.created_at >= :day_start AND e.created_at < :next_day_start';
            $parameters['day_start'] = $filter->date->format('Y-m-d 00:00:00');
            $parameters['next_day_start'] = $filter->date->modify('+1 day')->format('Y-m-d 00:00:00');
        }

        if ($filter->userId !== null) {
            $conditions[] = 'e.user_id = :user_id';
            $parameters['user_id'] = $filter->userId;
        }

        if ($filter->action !== null) {
            $conditions[] = 'e.action = :action';
            $parameters['action'] = $filter->action->value;
        }

        $whereClause = $conditions === [] ? '' : 'WHERE ' . implode(' AND ', $conditions);
        $statement = $this->pdo->prepare(
            "SELECT e.id, e.created_at, u.username, e.action, e.target
             FROM events e
             JOIN users u ON u.id = e.user_id
             {$whereClause}
             ORDER BY e.created_at DESC, e.id DESC
             LIMIT :limit OFFSET :offset"
        );

        foreach ($parameters as $name => $value) {
            $statement->bindValue($name, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }

        $statement->bindValue('limit', $limit, PDO::PARAM_INT);
        $statement->bindValue('offset', $offset, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    public function dailyCounts(?int $userId = null): array
    {
        $whereClause = $userId === null ? '' : 'WHERE user_id = ?';
        $statement = $this->pdo->prepare(
            "SELECT DATE(created_at) AS day,
                    SUM(action = ? AND target = ?) AS page_a_views,
                    SUM(action = ? AND target = ?) AS page_b_views,
                    SUM(action = ? AND target = ?) AS buy_cow_clicks,
                    SUM(action = ? AND target = ?) AS download_clicks
             FROM events
             {$whereClause}
             GROUP BY day
             ORDER BY day"
        );

        $parameters = [
            EventAction::ViewPage->value,
            EventTarget::PageA->value,
            EventAction::ViewPage->value,
            EventTarget::PageB->value,
            EventAction::ButtonClick->value,
            EventTarget::BuyCow->value,
            EventAction::ButtonClick->value,
            EventTarget::Download->value,
        ];

        if ($userId !== null) {
            $parameters[] = $userId;
        }

        $statement->execute($parameters);

        return $statement->fetchAll();
    }
}
