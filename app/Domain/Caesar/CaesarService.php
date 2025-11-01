<?php

namespace App\Domain\Caesar;

class CaesarService
{
    public function encode(string $text, int $shift): string
    {
        // Normalize shift to 0-25 range
        $shift = $shift % 26;
        if ($shift < 0) {
            $shift += 26;
        }

        return preg_replace_callback('/[A-Za-z]/', function($matches) use ($shift) {
            $char = $matches[0];
            $ascii = ord($char);
            $isUpperCase = ctype_upper($char);

            // Convert to 0-25 range, shift, then back to ASCII
            $base = $isUpperCase ? ord('A') : ord('a');
            $shifted = (($ascii - $base + $shift) % 26) + $base;

            return chr($shifted);
        }, $text);
    }

    public function decode(string $text, int $shift): string
    {
        return $this->encode($text, -$shift);
    }

    public function validate(string $submission, string $expectedPlaintext): bool
    {
        return strcasecmp(
            trim($submission),
            trim($expectedPlaintext)
        ) === 0;
    }
}
