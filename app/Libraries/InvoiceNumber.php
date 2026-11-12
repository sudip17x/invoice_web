<?php

declare(strict_types=1);

namespace App\Libraries;

/**
 * Formats the next sequential number, e.g. INV-2026-0001.
 * Pure function; the DB lookup of the last number (with row locking)
 * lives in InvoiceModel::lastNumberForUpdate().
 */
final class InvoiceNumber
{
    public static function next(?string $lastNumber, string $prefix, int $year): string
    {
        $seq = 1;

        if ($lastNumber !== null && preg_match('/-(\d+)$/', $lastNumber, $m) === 1) {
            $seq = (int) $m[1] + 1;
        }

        return sprintf('%s-%d-%04d', $prefix, $year, $seq);
    }

    public static function likePattern(string $prefix, int $year): string
    {
        return $prefix . '-' . $year . '-%';
    }
}
