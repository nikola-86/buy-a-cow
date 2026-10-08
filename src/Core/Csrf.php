<?php

declare(strict_types=1);

namespace App\Core;

final class Csrf
{
    private const SESSION_KEY = 'csrf_token';

    public function token(): string
    {
        if (!isset($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }

        return (string) $_SESSION[self::SESSION_KEY];
    }

    public function isValid(?string $token): bool
    {
        $storedToken = $_SESSION[self::SESSION_KEY] ?? null;

        return is_string($storedToken)
            && is_string($token)
            && hash_equals($storedToken, $token);
    }
}
