<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class SettingModel extends Model
{
    protected $table         = 'settings';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'user_id', 'company_name', 'address', 'phone', 'email', 'tax_id',
        'logo_path', 'bank_details', 'invoice_prefix', 'footer_note',
    ];

    protected $validationRules = [
        'company_name'   => 'required|max_length[150]',
        'address'        => 'permit_empty|max_length[1000]',
        'phone'          => 'permit_empty|max_length[30]',
        'email'          => 'permit_empty|valid_email|max_length[190]',
        'tax_id'         => 'permit_empty|max_length[50]',
        'bank_details'   => 'permit_empty|max_length[1000]',
        'invoice_prefix' => 'required|alpha_numeric|max_length[10]',
        'footer_note'    => 'permit_empty|max_length[1000]',
    ];

    /**
     * The user's settings row; created with defaults on first access.
     *
     * @return array<string, mixed>
     */
    public function forUser(int $userId): array
    {
        $row = $this->where('user_id', $userId)->first();

        if ($row === null) {
            $this->skipValidation(true)->insert([
                'user_id'        => $userId,
                'company_name'   => 'My Company',
                'invoice_prefix' => 'INV',
            ]);
            $this->skipValidation(false);
            $row = $this->where('user_id', $userId)->first();
        }

        return $row;
    }
}
