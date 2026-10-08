<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Dto\EventFilter;
use App\Enums\EventAction;
use App\Enums\EventTarget;
use App\Enums\Role;
use App\Repositories\EventRepository;
use App\Repositories\UserRepository;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\TestCase;

/**
 * Runs against the separate TEST_DB_NAME database; every test is rolled back.
 */
final class EventRepositoryTest extends TestCase
{
    private static PDO $pdo;
    private EventRepository $eventRepository;
    private UserRepository $userRepository;

    public static function setUpBeforeClass(): void
    {
        self::$pdo = new PDO(
            sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', getenv('DB_HOST'), getenv('TEST_DB_NAME')),
            getenv('DB_USER'),
            getenv('DB_PASSWORD'),
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );

        $statement = self::$pdo->prepare(
            'SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?'
        );
        $statement->execute(['events']);

        if ($statement->fetch() === false) {
            self::loadSchema();
        }
    }

    protected function setUp(): void
    {
        self::$pdo->beginTransaction();
        $this->eventRepository = new EventRepository(self::$pdo);
        $this->userRepository = new UserRepository(self::$pdo);
    }

    protected function tearDown(): void
    {
        self::$pdo->rollBack();
    }

    public function testDailyCountsAggregatesTrackedEventsPerDayForOneUser(): void
    {
        $aliceId = $this->createUser('alice');
        $bobId = $this->createUser('bob');
        $this->insertEvent($aliceId, EventAction::ViewPage, EventTarget::PageA, '2026-10-01 08:00:00');
        $this->insertEvent($aliceId, EventAction::ViewPage, EventTarget::PageA, '2026-10-01 23:59:59');
        $this->insertEvent($aliceId, EventAction::ViewPage, EventTarget::PageB, '2026-10-01 09:00:00');
        $this->insertEvent($aliceId, EventAction::ButtonClick, EventTarget::BuyCow, '2026-10-02 00:00:00');
        $this->insertEvent($aliceId, EventAction::ButtonClick, EventTarget::Download, '2026-10-02 10:00:00');
        $this->insertEvent($aliceId, EventAction::Login, null, '2026-10-02 07:00:00');
        $this->insertEvent($bobId, EventAction::ViewPage, EventTarget::PageA, '2026-10-01 12:00:00');

        // MySQL returns SUM() as a numeric string, hence assertEquals instead of assertSame.
        $this->assertEquals([
            [
                'day' => '2026-10-01',
                'page_a_views' => 2,
                'page_b_views' => 1,
                'buy_cow_clicks' => 0,
                'download_clicks' => 0,
            ],
            [
                'day' => '2026-10-02',
                'page_a_views' => 0,
                'page_b_views' => 0,
                'buy_cow_clicks' => 1,
                'download_clicks' => 1,
            ],
        ], $this->eventRepository->dailyCounts($aliceId));
    }

    public function testFindFilteredCombinesDateUserAndActionFiltersNewestFirst(): void
    {
        $aliceId = $this->createUser('alice');
        $bobId = $this->createUser('bob');
        $this->insertEvent($aliceId, EventAction::ViewPage, EventTarget::PageA, '2026-10-01 08:00:00');
        $this->insertEvent($aliceId, EventAction::ViewPage, EventTarget::PageB, '2026-10-01 20:00:00');
        $this->insertEvent($aliceId, EventAction::Login, null, '2026-10-01 07:59:00');
        $this->insertEvent($aliceId, EventAction::ViewPage, EventTarget::PageA, '2026-10-02 00:00:00');
        $this->insertEvent($bobId, EventAction::ViewPage, EventTarget::PageA, '2026-10-01 12:00:00');

        $events = $this->eventRepository->findFiltered(
            new EventFilter(new DateTimeImmutable('2026-10-01'), $aliceId, EventAction::ViewPage),
            10,
            0
        );

        $this->assertSame([
            ['2026-10-01 20:00:00', 'alice', 'view-page', 'page_b'],
            ['2026-10-01 08:00:00', 'alice', 'view-page', 'page_a'],
        ], array_map(
            static fn (array $event): array => [
                $event['created_at'],
                $event['username'],
                $event['action'],
                $event['target'],
            ],
            $events
        ));
    }

    private static function loadSchema(): void
    {
        $schema = (string) file_get_contents(__DIR__ . '/../../database/01-schema.sql');

        foreach (array_filter(array_map('trim', explode(';', $schema))) as $createStatement) {
            self::$pdo->exec($createStatement);
        }
    }

    private function createUser(string $username): int
    {
        return $this->userRepository->create($username, 'not-a-real-hash', Role::User);
    }

    private function insertEvent(int $userId, EventAction $action, ?EventTarget $target, string $createdAt): void
    {
        $statement = self::$pdo->prepare(
            'INSERT INTO events (user_id, action, target, created_at) VALUES (?, ?, ?, ?)'
        );
        $statement->execute([$userId, $action->value, $target?->value, $createdAt]);
    }
}
