<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\View;
use App\Repositories\UserRepositoryInterface;
use App\Services\ReportService;

final class ReportController
{
    public function __construct(
        private readonly ReportService $reportService,
        private readonly UserRepositoryInterface $userRepository,
        private readonly Auth $auth,
        private readonly View $view
    ) {
    }

    public function index(Request $request): void
    {
        $isAdmin = $this->auth->isAdmin();
        $requestedUserId = filter_var($request->query('user_id'), FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]);
        $selectedUserId = $isAdmin && is_int($requestedUserId) ? $requestedUserId : null;

        $this->view->render('reports/index', [
            'title' => 'Reports',
            'report' => $this->reportService->dailyActivity($selectedUserId),
            'isAdmin' => $isAdmin,
            'users' => $isAdmin ? $this->userRepository->findAll() : [],
            'selectedUserId' => $selectedUserId,
        ]);
    }
}
