<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Repositories\EventRepositoryInterface;
use App\Services\ReportService;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class ReportServiceTest extends TestCase
{
    private ReportService $reportService;

    protected function setUp(): void
    {
        $this->reportService = new ReportService($this->createStub(EventRepositoryInterface::class));
    }

    public function testFillMissingDaysAddsZeroRowsFromTheFirstDayUntilToday(): void
    {
        $dailyCounts = [
            $this->dailyCount('2026-10-01', '2', '1', '1', '0'),
            $this->dailyCount('2026-10-03', '0', '3', null, '2'),
        ];

        $report = $this->reportService->fillMissingDays($dailyCounts, new DateTimeImmutable('2026-10-04 15:30:00'));

        $this->assertSame([
            $this->dailyCount('2026-10-01', 2, 1, 1, 0),
            $this->dailyCount('2026-10-02', 0, 0, 0, 0),
            $this->dailyCount('2026-10-03', 0, 3, 0, 2),
            $this->dailyCount('2026-10-04', 0, 0, 0, 0),
        ], $report);
    }

    public function testFillMissingDaysReturnsAnEmptyReportWithoutEvents(): void
    {
        $this->assertSame([], $this->reportService->fillMissingDays([], new DateTimeImmutable('2026-10-04')));
    }

    /**
     * @return array<string, string|int|null>
     */
    private function dailyCount(
        string $day,
        string|int|null $pageAViews,
        string|int|null $pageBViews,
        string|int|null $buyCowClicks,
        string|int|null $downloadClicks
    ): array {
        return [
            'day' => $day,
            'page_a_views' => $pageAViews,
            'page_b_views' => $pageBViews,
            'buy_cow_clicks' => $buyCowClicks,
            'download_clicks' => $downloadClicks,
        ];
    }
}
