<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

final class View
{
    public function __construct(
        private readonly Auth $auth,
        private readonly Csrf $csrf
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function render(string $view, array $data = [], int $status = 200): void
    {
        $projectRoot = dirname(__DIR__, 2);
        $viewPath = $projectRoot . '/views/' . $view . '.php';

        if (!is_file($viewPath)) {
            throw new RuntimeException(sprintf('View not found: %s', $view));
        }

        $layoutPath = $projectRoot . '/views/layout.php';
        $currentUsername = $this->auth->username();
        $currentRole = $this->auth->role();
        $csrfToken = $this->csrf->token();
        extract($data, EXTR_SKIP);

        http_response_code($status);
        require $layoutPath;
    }
}
