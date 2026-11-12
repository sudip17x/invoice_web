<?php

declare(strict_types=1);

namespace App\Models;

use App\Libraries\InvoiceNumber;
use CodeIgniter\Model;

class InvoiceModel extends Model
{
    public const STATUSES = ['draft', 'sent', 'paid', 'cancelled'];

    /** Columns the list may be sorted by (whitelist - never pass user input to orderBy directly). */
    private const SORTABLE = ['invoice_no', 'issue_date', 'due_date', 'grand_total', 'status', 'customer_name'];

    protected $table         = 'invoices';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'user_id', 'customer_id', 'invoice_no', 'issue_date', 'due_date', 'status',
        'subtotal', 'discount', 'tax_total', 'grand_total', 'notes',
    ];

    /**
     * One invoice, only if it belongs to the user (with the customer's name/contact joined).
     * Customers are soft-deleted, so the join deliberately ignores customers.deleted_at.
     *
     * @return array<string, mixed>|null
     */
    public function findForUser(int $id, int $userId): ?array
    {
        return $this->baseSelect()
            ->where('invoices.user_id', $userId)
            ->where('invoices.id', $id)
            ->first();
    }

    /**
     * Filtered, sorted, paginated list.
     *
     * @param array{status?: string, customer_id?: string, from?: string, to?: string, sort?: string, dir?: string} $filters
     *
     * @return list<array<string, mixed>>
     */
    public function listForUser(int $userId, array $filters, int $perPage = 10): array
    {
        $this->baseSelect()->where('invoices.user_id', $userId);

        if (in_array($filters['status'] ?? '', self::STATUSES, true)) {
            $this->where('invoices.status', $filters['status']);
        }

        if (ctype_digit((string) ($filters['customer_id'] ?? ''))) {
            $this->where('invoices.customer_id', (int) $filters['customer_id']);
        }

        if ($this->isDate($filters['from'] ?? '')) {
            $this->where('invoices.issue_date >=', $filters['from']);
        }

        if ($this->isDate($filters['to'] ?? '')) {
            $this->where('invoices.issue_date <=', $filters['to']);
        }

        $sort = in_array($filters['sort'] ?? '', self::SORTABLE, true) ? $filters['sort'] : 'issue_date';
        $dir  = strtolower((string) ($filters['dir'] ?? 'desc')) === 'asc' ? 'ASC' : 'DESC';
        $col  = $sort === 'customer_name' ? 'customers.name' : 'invoices.' . $sort;

        return $this->orderBy($col, $dir)->orderBy('invoices.id', 'DESC')->paginate($perPage);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function latestForUser(int $userId, int $limit = 10): array
    {
        return $this->baseSelect()
            ->where('invoices.user_id', $userId)
            ->orderBy('invoices.id', 'DESC')
            ->findAll($limit);
    }

    /**
     * Dashboard numbers. "Unpaid" and "overdue" both mean: draft or sent (not paid, not cancelled).
     *
     * @return array{total: int, this_month: int, paid_amount: float, unpaid_amount: float, overdue_count: int}
     */
    public function statsForUser(int $userId): array
    {
        $today = date('Y-m-d');

        $total = $this->where('user_id', $userId)->countAllResults();

        $thisMonth = $this->where('user_id', $userId)
            ->where('issue_date >=', date('Y-m-01'))
            ->countAllResults();

        $paid = $this->selectSum('grand_total', 'amount')
            ->where('user_id', $userId)->where('status', 'paid')->first();

        $unpaid = $this->selectSum('grand_total', 'amount')
            ->where('user_id', $userId)->whereIn('status', ['draft', 'sent'])->first();

        $overdue = $this->where('user_id', $userId)
            ->whereIn('status', ['draft', 'sent'])
            ->where('due_date <', $today)
            ->countAllResults();

        return [
            'total'         => $total,
            'this_month'    => $thisMonth,
            'paid_amount'   => (float) ($paid['amount'] ?? 0),
            'unpaid_amount' => (float) ($unpaid['amount'] ?? 0),
            'overdue_count' => $overdue,
        ];
    }

    /**
     * Last invoice number for this prefix/year, locking the row so two concurrent
     * requests cannot read the same value. Call ONLY inside a transaction.
     * Values are bound parameters - no string concatenation into SQL.
     */
    public function lastNumberForUpdate(int $userId, string $prefix, int $year): ?string
    {
        $row = $this->db->query(
            'SELECT invoice_no FROM invoices WHERE user_id = ? AND invoice_no LIKE ? ORDER BY id DESC LIMIT 1 FOR UPDATE',
            [$userId, InvoiceNumber::likePattern($prefix, $year)],
        )->getRowArray();

        return $row['invoice_no'] ?? null;
    }

    private function baseSelect(): static
    {
        return $this->select('invoices.*, customers.name AS customer_name, customers.email AS customer_email, '
                . 'customers.phone AS customer_phone, customers.address AS customer_address, customers.tax_id AS customer_tax_id')
            ->join('customers', 'customers.id = invoices.customer_id', 'left');
    }

    private function isDate(mixed $value): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', (string) $value);

        return $d !== false && $d->format('Y-m-d') === $value;
    }
}
