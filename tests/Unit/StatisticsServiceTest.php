<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Dto\EventFilter;
use App\Enums\EventAction;
use App\Repositories\EventRepositoryInterface;
use App\Services\StatisticsService;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class StatisticsServiceTest extends TestCase
{
    public function testBuildFilterParsesValidInput(): void
    {
        $service = new StatisticsService($this->createStub(EventRepositoryInterface::class));

        $filter = $service->buildFilter('2026-10-01', 3, 'button-click', 2);

        $this->assertEquals(
            new EventFilter(new DateTimeImmutable('2026-10-01 00:00:00'), 3, EventAction::ButtonClick, 2),
            $filter
        );
    }

    /**
     * @return array<string, array{?string, ?int, ?string, ?int}>
     */
    public static function invalidFilterInput(): array
    {
        return [
            'missing parameters' => [null, null, null, null],
            'overflowing date' => ['2026-02-31', null, null, null],
            'wrong date format' => ['01.10.2026', null, null, null],
            'unknown action' => [null, null, 'purchase', null],
            'zero page' => [null, null, null, 0],
            'page that overflows the offset' => [null, null, null, PHP_INT_MAX],
        ];
    }

    #[DataProvider('invalidFilterInput')]
    public function testBuildFilterFallsBackToDefaultsForInvalidInput(
        ?string $date,
        ?int $userId,
        ?string $action,
        ?int $page
    ): void {
        $service = new StatisticsService($this->createStub(EventRepositoryInterface::class));

        $this->assertEquals(new EventFilter(), $service->buildFilter($date, $userId, $action, $page));
    }

    public function testFindEventsRequestsOneExtraRowAndDetectsNextPage(): void
    {
        $filter = new EventFilter(page: 2);
        $repository = $this->createMock(EventRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findFiltered')
            ->with($filter, 51, 50)
            ->willReturn($this->eventRows(51));

        $result = (new StatisticsService($repository))->findEvents($filter);

        $this->assertCount(StatisticsService::PAGE_SIZE, $result['events']);
        $this->assertTrue($result['hasNextPage']);
    }

    public function testFindEventsHasNoNextPageOnTheLastPage(): void
    {
        $repository = $this->createStub(EventRepositoryInterface::class);
        $repository->method('findFiltered')->willReturn($this->eventRows(50));

        $result = (new StatisticsService($repository))->findEvents(new EventFilter());

        $this->assertCount(50, $result['events']);
        $this->assertFalse($result['hasNextPage']);
    }

    /**
     * @return list<array{id: int, created_at: string, username: string, action: string, target: string|null}>
     */
    private function eventRows(int $count): array
    {
        return array_map(
            static fn (int $id): array => [
                'id' => $id,
                'created_at' => '2026-10-01 12:00:00',
                'username' => 'alice',
                'action' => EventAction::Login->value,
                'target' => null,
            ],
            range(1, $count)
        );
    }
}
