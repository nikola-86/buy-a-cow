<?php

declare(strict_types=1);

use App\Core\Database;
use App\Enums\EventAction;
use App\Enums\EventTarget;
use App\Enums\Role;
use App\Repositories\UserRepository;

require_once __DIR__ . '/../vendor/autoload.php';

date_default_timezone_set('UTC');

$demoUsernames = ['demo1', 'demo2', 'demo3'];
$demoPassword = 'demo1234';
$eventCount = 500;
$daysBack = 30;
$eventTypes = [
    [EventAction::ViewPage, EventTarget::PageA],
    [EventAction::ViewPage, EventTarget::PageB],
    [EventAction::ButtonClick, EventTarget::BuyCow],
    [EventAction::ButtonClick, EventTarget::Download],
    [EventAction::Login, null],
    [EventAction::Logout, null],
];

$pdo = Database::getConnection();
$userRepository = new UserRepository($pdo);
$userIds = [];

foreach ($demoUsernames as $username) {
    $userIds[] = $userRepository->findByUsername($username)['id']
        ?? $userRepository->create($username, password_hash($demoPassword, PASSWORD_DEFAULT), Role::User);
}

$insertEvent = $pdo->prepare('INSERT INTO events (user_id, action, target, created_at) VALUES (?, ?, ?, ?)');
$latestTimestamp = time();
$earliestTimestamp = strtotime(sprintf('-%d days midnight', $daysBack - 1), $latestTimestamp);

$pdo->beginTransaction();

for ($i = 0; $i < $eventCount; $i++) {
    [$action, $target] = $eventTypes[array_rand($eventTypes)];
    $insertEvent->execute([
        $userIds[array_rand($userIds)],
        $action->value,
        $target?->value,
        date('Y-m-d H:i:s', random_int($earliestTimestamp, $latestTimestamp)),
    ]);
}

$pdo->commit();

printf(
    "Inserted %d events for %s between %s and %s (UTC).\n",
    $eventCount,
    implode(', ', $demoUsernames),
    date('Y-m-d', $earliestTimestamp),
    date('Y-m-d', $latestTimestamp)
);
