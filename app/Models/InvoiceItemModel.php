<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class InvoiceItemModel extends Model
{
    protected $table         = 'invoice_items';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'invoice_id', 'product_id', 'description', 'qty', 'unit_price', 'tax_percent', 'line_total',
    ];

    /**
     * @return list<array<string, mixed>>
     */
    public function forInvoice(int $invoiceId): array
    {
        return $this->where('invoice_id', $invoiceId)->orderBy('id', 'ASC')->findAll();
    }
}
