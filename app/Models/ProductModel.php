<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    protected $table          = 'products';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps  = true;
    protected $allowedFields  = ['user_id', 'name', 'description', 'unit_price', 'tax_percent'];

    protected $validationRules = [
        'name'        => 'required|max_length[150]',
        'description' => 'permit_empty|max_length[2000]',
        'unit_price'  => 'required|numeric|greater_than_equal_to[0]|less_than[10000000000]',
        'tax_percent' => 'required|numeric|greater_than_equal_to[0]|less_than_equal_to[100]',
    ];

    /**
     * @return list<array<string, mixed>>
     */
    public function paginateForUser(int $userId, string $search = '', int $perPage = 10): array
    {
        $this->where('user_id', $userId);

        if ($search !== '') {
            $this->groupStart()->like('name', $search)->orLike('description', $search)->groupEnd();
        }

        return $this->orderBy('name', 'ASC')->paginate($perPage);
    }
}
