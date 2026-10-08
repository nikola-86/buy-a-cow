<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\Role;
use App\Exceptions\ValidationException;
use App\Repositories\UserRepositoryInterface;

final class AuthService
{
    private const USERNAME_MIN_LENGTH = 3;
    private const USERNAME_MAX_LENGTH = 50;
    private const PASSWORD_MIN_LENGTH = 8;

    public function __construct(
        private readonly UserRepositoryInterface $userRepository
    ) {
    }

    /**
     * @throws ValidationException
     */
    public function register(string $username, string $password): int
    {
        $username = trim($username);
        $usernameLength = mb_strlen($username);
        $errors = [];

        if ($usernameLength < self::USERNAME_MIN_LENGTH || $usernameLength > self::USERNAME_MAX_LENGTH) {
            $errors['username'] = sprintf(
                'Username must be between %d and %d characters.',
                self::USERNAME_MIN_LENGTH,
                self::USERNAME_MAX_LENGTH
            );
        } elseif ($this->userRepository->findByUsername($username) !== null) {
            $errors['username'] = 'Username is already taken.';
        }

        if (mb_strlen($password) < self::PASSWORD_MIN_LENGTH) {
            $errors['password'] = sprintf(
                'Password must be at least %d characters.',
                self::PASSWORD_MIN_LENGTH
            );
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        return $this->userRepository->create(
            $username,
            password_hash($password, PASSWORD_DEFAULT),
            Role::User
        );
    }

    /**
     * @return array{id: int, username: string, role: Role}
     *
     * @throws ValidationException
     */
    public function authenticate(string $username, string $password): array
    {
        $user = $this->userRepository->findByUsername(trim($username));

        if ($user === null || !password_verify($password, $user['password_hash'])) {
            throw new ValidationException(['credentials' => 'Invalid username or password.']);
        }

        return [
            'id' => $user['id'],
            'username' => $user['username'],
            'role' => Role::from($user['role']),
        ];
    }
}
