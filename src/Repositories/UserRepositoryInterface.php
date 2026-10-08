<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\Role;

interface UserRepositoryInterface
{
    /**
     * @return array{id: int, username: string, password_hash: string, role: string}|null
     */
    public function findByUsername(string $username): ?array;

    /**
     * @return list<array{id: int, username: string}>
     */
    public function findAll(): array;

    public function create(string $username, string $passwordHash, Role $role): int;
}
