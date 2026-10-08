<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\View;
use App\Dto\EventFilter;
use App\Repositories\UserRepositoryInterface;
use App\Services\StatisticsService;

final class StatisticsController
{
    public function __construct(
        private readonly StatisticsService $statisticsService,
        private readonly UserRepositoryInterface $userRepository,
        private readonly View $view
    ) {
    }

    public function index(Request $request): void
    {
        $filter = $this->statisticsService->buildFilter(
            $request->query('date'),
            $request->queryPositiveInt('user_id'),
            $request->query('action'),
            $request->queryPositiveInt('page')
        );
        $result = $this->statisticsService->findEvents($filter);

        $this->view->render('statistics/index', [
            'title' => 'Statistics',
            'filter' => $filter,
            'events' => $result['events'],
            'users' => $this->userRepository->findAll(),
            'previousPageUrl' => $filter->page > 1 ? $this->pageUrl($filter, $filter->page - 1) : null,
            'nextPageUrl' => $result['hasNextPage'] ? $this->pageUrl($filter, $filter->page + 1) : null,
        ]);
    }

    private function pageUrl(EventFilter $filter, int $page): string
    {
        return '/statistics?' . http_build_query([
            'date' => $filter->date?->format('Y-m-d'),
            'user_id' => $filter->userId,
            'action' => $filter->action?->value,
            'page' => $page,
        ]);
    }
}
