<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Exceptions\ValidationException;
use App\Repositories\UserRepositoryInterface;
use App\Services\AuthService;
use PHPUnit\Framework\TestCase;

final class AuthServiceTest extends TestCase
{
    public function testRegisterRejectsATakenUsernameWithoutCreatingAUser(): void
    {
        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository->method('findByUsername')
            ->with('alice')
            ->willReturn(['id' => 1, 'username' => 'alice', 'password_hash' => 'hash', 'role' => 'user']);
        $userRepository->expects($this->never())->method('create');

        try {
            (new AuthService($userRepository))->register('  alice  ', 'long-enough-password');
            $this->fail('Expected a ValidationException for a taken username.');
        } catch (ValidationException $exception) {
            $this->assertSame(['username' => 'Username is already taken.'], $exception->errors());
        }
    }
}
