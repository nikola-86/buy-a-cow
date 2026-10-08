<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use RuntimeException;

final class Database
{
    private static ?PDO $connection = null;

    private function __construct()
    {
    }

    public static function getConnection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=utf8mb4',
            self::environment('DB_HOST'),
            self::environment('DB_NAME')
        );

        self::$connection = new PDO(
            $dsn,
            self::environment('DB_USER'),
            self::environment('DB_PASSWORD'),
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );

        $statement = self::$connection->prepare('SET time_zone = ?');
        $statement->execute(['+00:00']);

        return self::$connection;
    }

    private static function environment(string $name): string
    {
        $value = getenv($name);

        if ($value === false || $value === '') {
            throw new RuntimeException(sprintf('Missing required environment variable: %s', $name));
        }

        return $value;
    }
}
