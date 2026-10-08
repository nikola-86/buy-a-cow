<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\View;
use App\Enums\EventAction;
use App\Enums\EventTarget;
use App\Services\ActivityTracker;

final class PageController
{
    private const DOWNLOAD_FILE_NAME = 'cow-installer.exe';

    public function __construct(
        private readonly ActivityTracker $activityTracker,
        private readonly Auth $auth,
        private readonly View $view
    ) {
    }

    public function pageA(Request $request): void
    {
        $this->activityTracker->track($this->auth->requireUserId(), EventAction::ViewPage, EventTarget::PageA);

        $this->view->render('pages/page-a', [
            'title' => 'Page A',
            'buyCowTarget' => EventTarget::BuyCow->value,
        ]);
    }

    public function pageB(Request $request): void
    {
        $this->activityTracker->track($this->auth->requireUserId(), EventAction::ViewPage, EventTarget::PageB);

        $this->view->render('pages/page-b', ['title' => 'Page B']);
    }

    public function download(Request $request): void
    {
        $filePath = dirname(__DIR__, 2) . '/storage/downloads/' . self::DOWNLOAD_FILE_NAME;

        $this->activityTracker->track($this->auth->requireUserId(), EventAction::ButtonClick, EventTarget::Download);

        // TODO: Move the download headers to a Response::download() method, like Response::json() and redirect().
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . self::DOWNLOAD_FILE_NAME . '"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
    }
}
