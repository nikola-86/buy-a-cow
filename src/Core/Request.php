<?php

declare(strict_types=1);

namespace App\Core;

use JsonException;
use UnexpectedValueException;

final class Request
{
    /**
     * @var array<string, mixed>|null
     */
    private ?array $json = null;

    public function method(): string
    {
        return strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
    }

    public function path(): string
    {
        $path = parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);

        return is_string($path) && $path !== '' ? $path : '/';
    }

    public function query(string $key, ?string $default = null): ?string
    {
        $value = $_GET[$key] ?? $default;

        return is_string($value) ? $value : $default;
    }

    public function input(string $key, ?string $default = null): ?string
    {
        $value = $_POST[$key] ?? $default;

        return is_string($value) ? $value : $default;
    }

    /**
     * @return array<string, mixed>
     *
     * @throws JsonException
     */
    public function json(): array
    {
        if ($this->json !== null) {
            return $this->json;
        }

        $body = file_get_contents('php://input');

        if ($body === false || $body === '') {
            return $this->json = [];
        }

        $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($data)) {
            throw new UnexpectedValueException('The JSON request body must be an object.');
        }

        return $this->json = $data;
    }

    public function header(string $name): ?string
    {
        $normalizedName = strtoupper(str_replace('-', '_', $name));
        $serverKey = in_array($normalizedName, ['CONTENT_TYPE', 'CONTENT_LENGTH'], true)
            ? $normalizedName
            : 'HTTP_' . $normalizedName;
        $value = $_SERVER[$serverKey] ?? null;

        return is_string($value) ? $value : null;
    }
}
