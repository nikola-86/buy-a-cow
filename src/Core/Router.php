<?php

declare(strict_types=1);

namespace App\Core;

use App\Enums\RouteAccess;
use Closure;
use RuntimeException;

final class Router
{
    /**
     * @var array<string, array{class-string, string, RouteAccess}>
     */
    private array $routes = [];

    /**
     * @param array<class-string, Closure(): object> $controllerFactories Called only for the matched route
     */
    public function __construct(
        private readonly Auth $auth,
        private readonly Csrf $csrf,
        private readonly View $view,
        private readonly array $controllerFactories
    ) {
    }

    /**
     * @param class-string $controller
     */
    public function add(
        string $method,
        string $path,
        string $controller,
        string $action,
        RouteAccess $access
    ): void {
        $this->routes[strtoupper($method) . ' ' . $path] = [$controller, $action, $access];
    }

    public function dispatch(Request $request): void
    {
        $route = $this->routes[$request->method() . ' ' . $request->path()] ?? null;

        if ($route === null) {
            $this->error($request, 404, 'Page not found');
            return;
        }

        [$controller, $action, $access] = $route;

        if (!$this->hasAccess($request, $access)) {
            return;
        }

        if ($request->method() === 'POST') {
            $token = $request->input('csrf_token') ?? $request->header('X-CSRF-Token');

            if (!$this->csrf->isValid($token)) {
                $this->error($request, 403, 'Invalid CSRF token');
                return;
            }
        }

        $createController = $this->controllerFactories[$controller]
            ?? throw new RuntimeException(sprintf('No factory registered for %s.', $controller));
        $instance = $createController();

        if (!method_exists($instance, $action)) {
            throw new RuntimeException('Configured route action does not exist.');
        }

        $instance->{$action}($request);
    }

    private function hasAccess(Request $request, RouteAccess $access): bool
    {
        $userId = $this->auth->userId();

        if ($access === RouteAccess::Guest && $userId !== null) {
            Response::redirect('/page-a');
        }

        if ($access !== RouteAccess::Guest && $userId === null) {
            if ($request->isApi()) {
                Response::json(['error' => 'Authentication required'], 401);
            }

            Response::redirect('/login');
        }

        if ($access === RouteAccess::Admin && !$this->auth->isAdmin()) {
            $this->error($request, 403, 'Forbidden');

            return false;
        }

        return true;
    }

    private function error(Request $request, int $status, string $message): void
    {
        if ($request->isApi()) {
            Response::json(['error' => $message], $status);
        }

        $this->view->render('error', [
            'title' => $status . ' Error',
            'status' => $status,
            'message' => $message,
        ], $status);
    }
}
