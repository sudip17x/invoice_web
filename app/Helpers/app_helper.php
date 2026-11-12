<?php

declare(strict_types=1);

/**
 * Presentation helpers only. No business rules here.
 */

if (! function_exists('currency_symbol')) {
    function currency_symbol(): string
    {
        return (string) env('app.currencySymbol', '₹');
    }
}

if (! function_exists('money')) {
    function money(float|int|string|null $value): string
    {
        return number_format((float) $value, 2, '.', ',');
    }
}

if (! function_exists('is_overdue')) {
    /**
     * Due date passed and the invoice is still open (not paid / cancelled).
     *
     * @param array<string, mixed> $invoice
     */
    function is_overdue(array $invoice): bool
    {
        return in_array($invoice['status'], ['draft', 'sent'], true)
            && $invoice['due_date'] < date('Y-m-d');
    }
}

if (! function_exists('status_badge')) {
    /**
     * @param array<string, mixed> $invoice
     */
    function status_badge(array $invoice): string
    {
        $map = [
            'draft'     => 'secondary',
            'sent'      => 'primary',
            'paid'      => 'success',
            'cancelled' => 'dark',
        ];
        $status = (string) $invoice['status'];
        $html   = '<span class="badge text-bg-' . $map[$status] . '">' . esc(ucfirst($status)) . '</span>';

        if (is_overdue($invoice)) {
            $html .= ' <span class="badge text-bg-danger">Overdue</span>';
        }

        return $html;
    }
}

if (! function_exists('sort_link')) {
    /**
     * Link for a sortable column header that keeps the current filters.
     *
     * @param array<string, mixed> $filters
     */
    function sort_link(string $column, string $label, array $filters, string $baseUrl): string
    {
        $isCurrent = ($filters['sort'] ?? '') === $column;
        $dir       = $isCurrent && ($filters['dir'] ?? 'desc') === 'asc' ? 'desc' : 'asc';
        $query     = array_filter(
            array_merge($filters, ['sort' => $column, 'dir' => $dir]),
            static fn ($v) => $v !== null && $v !== '',
        );
        $arrow = $isCurrent ? (($filters['dir'] ?? 'desc') === 'asc' ? ' ▲' : ' ▼') : '';

        return '<a class="text-decoration-none text-reset" href="' . esc($baseUrl . '?' . http_build_query($query), 'attr') . '">'
            . esc($label) . $arrow . '</a>';
    }
}
