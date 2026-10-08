<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Enums\Role;
use PDO;

final class UserRepository
{
    private readonly PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::getConnection();
    }

    /**
     * @return array{id: int, username: string, password_hash: string, role: string}|null
     */
    public function findByUsername(string $username): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT id, username, password_hash, role FROM users WHERE username = :username'
        );
        $statement->execute(['username' => $username]);
        $user = $statement->fetch();

        return $user === false ? null : $user;
    }

    public function create(string $username, string $passwordHash, Role $role): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO users (username, password_hash, role) VALUES (:username, :password_hash, :role)'
        );
        $statement->execute([
            'username' => $username,
            'password_hash' => $passwordHash,
            'role' => $role->value,
        ]);

        return (int) $this->pdo->lastInsertId();
    }
}
