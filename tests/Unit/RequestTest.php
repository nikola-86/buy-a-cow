<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Core\Request;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class RequestTest extends TestCase
{
    protected function tearDown(): void
    {
        $_GET = [];
    }

    /**
     * @return array<string, array{mixed, ?int}>
     */
    public static function positiveIntQueryValues(): array
    {
        return [
            'positive number' => ['3', 3],
            'non-numeric' => ['abc', null],
            'negative' => ['-1', null],
            'zero' => ['0', null],
            'decimal' => ['1.5', null],
            'empty' => ['', null],
            'array' => [['1'], null],
        ];
    }

    #[DataProvider('positiveIntQueryValues')]
    public function testQueryPositiveIntAcceptsOnlyPositiveIntegers(mixed $queryValue, ?int $expected): void
    {
        $_GET = ['user_id' => $queryValue];

        $this->assertSame($expected, (new Request())->queryPositiveInt('user_id'));
    }

    public function testQueryPositiveIntReturnsNullForAMissingParameter(): void
    {
        $this->assertNull((new Request())->queryPositiveInt('user_id'));
    }
}
