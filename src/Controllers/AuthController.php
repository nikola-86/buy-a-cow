<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Enums\EventAction;
use App\Exceptions\ValidationException;
use App\Services\ActivityTracker;
use App\Services\AuthService;

final class AuthController
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly ActivityTracker $activityTracker,
        private readonly Auth $auth,
        private readonly View $view
    ) {
    }

    public function home(Request $request): void
    {
        Response::redirect('/login');
    }

    public function showLoginForm(Request $request): void
    {
        $this->renderLoginForm(registered: $request->query('registered') === '1');
    }

    public function login(Request $request): void
    {
        $username = $request->input('username', '');

        try {
            $user = $this->authService->authenticate($username, $request->input('password', ''));
        } catch (ValidationException $exception) {
            $this->renderLoginForm($username, $exception->errors(), status: 422);
            return;
        }

        $this->auth->login($user['id'], $user['username'], $user['role']);
        $this->activityTracker->track($user['id'], EventAction::Login);

        Response::redirect('/page-a');
    }

    public function showRegistrationForm(Request $request): void
    {
        $this->renderRegistrationForm();
    }

    public function register(Request $request): void
    {
        $username = $request->input('username', '');

        try {
            $userId = $this->authService->register($username, $request->input('password', ''));
        } catch (ValidationException $exception) {
            $this->renderRegistrationForm($username, $exception->errors(), 422);
            return;
        }

        $this->activityTracker->track($userId, EventAction::Registration);

        Response::redirect('/login?registered=1');
    }

    public function logout(Request $request): void
    {
        $this->activityTracker->track($this->auth->requireUserId(), EventAction::Logout);
        $this->auth->logout();

        Response::redirect('/login');
    }

    /**
     * @param array<string, string> $errors
     */
    private function renderLoginForm(
        string $username = '',
        array $errors = [],
        bool $registered = false,
        int $status = 200
    ): void {
        $this->view->render('auth/login', [
            'title' => 'Login',
            'username' => $username,
            'errors' => $errors,
            'registered' => $registered,
        ], $status);
    }

    /**
     * @param array<string, string> $errors
     */
    private function renderRegistrationForm(string $username = '', array $errors = [], int $status = 200): void
    {
        $this->view->render('auth/register', [
            'title' => 'Register',
            'username' => $username,
            'errors' => $errors,
        ], $status);
    }
}
