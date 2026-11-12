<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class CustomerModel extends Model
{
    protected $table          = 'customers';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps  = true;
    protected $allowedFields  = ['user_id', 'name', 'email', 'phone', 'address', 'tax_id'];

    protected $validationRules = [
        'name'    => 'required|max_length[150]',
        'email'   => 'permit_empty|valid_email|max_length[190]',
        'phone'   => 'permit_empty|max_length[30]',
        'address' => 'permit_empty|max_length[1000]',
        'tax_id'  => 'permit_empty|max_length[50]',
    ];

    /**
     * Customers of one user, optionally filtered by name/email, 10 per page.
     *
     * @return list<array<string, mixed>>
     */
    public function paginateForUser(int $userId, string $search = '', int $perPage = 10): array
    {
        $this->where('user_id', $userId);

        if ($search !== '') {
            $this->groupStart()->like('name', $search)->orLike('email', $search)->groupEnd();
        }

        return $this->orderBy('name', 'ASC')->paginate($perPage);
    }
}
