<?php

declare(strict_types=1);

namespace App\Core;

use App\Enums\Role;

final class Auth
{
    public function login(int $userId, string $username, Role $role): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $userId;
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $role->value;
    }

    public function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $cookie = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $cookie['path'],
                'domain' => $cookie['domain'],
                'secure' => $cookie['secure'],
                'httponly' => $cookie['httponly'],
                'samesite' => $cookie['samesite'],
            ]);
        }

        session_destroy();
    }

    public function userId(): ?int
    {
        $userId = $_SESSION['user_id'] ?? null;

        return is_int($userId) ? $userId : null;
    }

    public function username(): ?string
    {
        $username = $_SESSION['username'] ?? null;

        return is_string($username) ? $username : null;
    }

    public function role(): ?Role
    {
        $role = $_SESSION['role'] ?? null;

        return is_string($role) ? Role::tryFrom($role) : null;
    }

    public function isAdmin(): bool
    {
        return $this->role() === Role::Admin;
    }
}
