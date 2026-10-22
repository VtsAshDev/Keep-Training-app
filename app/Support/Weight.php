<?php

namespace App\Support;

/**
 * Brazilian number format for weights and volumes: "62,5", "60", "8.420".
 */
final class Weight
{
    public static function format(float|int|string|null $value, int $decimals = 2): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        $formatted = number_format((float) $value, $decimals, ',', '.');

        return str_contains($formatted, ',') ? rtrim(rtrim($formatted, '0'), ',') : $formatted;
    }

    /**
     * Accepts "62,5", "62.5" or "1.062,5"; empty input means no value.
     */
    public static function parse(?string $input): ?float
    {
        $input = trim((string) $input);

        if ($input === '') {
            return null;
        }

        if (str_contains($input, ',')) {
            $input = str_replace(['.', ','], ['', '.'], $input);
        }

        return is_numeric($input) ? max(0.0, (float) $input) : null;
    }
}
