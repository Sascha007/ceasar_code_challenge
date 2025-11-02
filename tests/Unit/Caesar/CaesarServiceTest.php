<?php

namespace Tests\Unit\Caesar;

use App\Domain\Caesar\CaesarService;
use PHPUnit\Framework\TestCase;

class CaesarServiceTest extends TestCase
{
    private CaesarService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CaesarService;
    }

    /**
     * @dataProvider encodeDataProvider
     */
    public function test_encode_correctly_shifts_text(
        string $input,
        int $shift,
        string $expected
    ): void {
        $result = $this->service->encode($input, $shift);
        $this->assertEquals($expected, $result);
    }

    public static function encodeDataProvider(): array
    {
        return [
            'simple shift by 1' => [
                'ABC',
                1,
                'BCD',
            ],
            'wrap around Z' => [
                'XYZ',
                1,
                'YZA',
            ],
            'preserve case' => [
                'Hello World',
                1,
                'Ifmmp Xpsme',
            ],
            'preserve special chars' => [
                'Hello, World!',
                1,
                'Ifmmp, Xpsme!',
            ],
            'negative shift' => [
                'ABC',
                -1,
                'ZAB',
            ],
            'shift more than 26' => [
                'ABC',
                27,
                'BCD',
            ],
            'empty string' => [
                '',
                1,
                '',
            ],
        ];
    }

    /**
     * @dataProvider validateDataProvider
     */
    public function test_validate_correctly_checks_solution(
        string $submission,
        string $expected,
        bool $shouldBeValid
    ): void {
        $result = $this->service->validate($submission, $expected);
        $this->assertEquals($shouldBeValid, $result);
    }

    public static function validateDataProvider(): array
    {
        return [
            'exact match' => [
                'Hello World',
                'Hello World',
                true,
            ],
            'case insensitive' => [
                'HELLO WORLD',
                'hello world',
                true,
            ],
            'trim whitespace' => [
                ' Hello World ',
                'Hello World',
                true,
            ],
            'wrong text' => [
                'Wrong Answer',
                'Hello World',
                false,
            ],
        ];
    }

    public function test_encode_decode_roundtrip(): void
    {
        $original = 'The Quick Brown Fox Jumps Over The Lazy Dog!';
        $shift = 13;

        $encoded = $this->service->encode($original, $shift);
        $decoded = $this->service->decode($encoded, $shift);

        $this->assertEquals($original, $decoded);
    }
}
