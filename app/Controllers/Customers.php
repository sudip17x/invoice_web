<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\CustomerModel;
use CodeIgniter\HTTP\RedirectResponse;

class Customers extends AppController
{
    private const FIELDS = ['name', 'email', 'phone', 'address', 'tax_id'];

    public function index(): string
    {
        $model  = new CustomerModel();
        $search = trim((string) $this->request->getGet('q'));

        return view('customers/index', [
            'title' => 'Customers',
            'rows'  => $model->paginateForUser($this->uid(), $search),
            'pager' => $model->pager,
            'q'     => $search,
        ]);
    }

    public function create(): string
    {
        return view('customers/form', [
            'title'  => 'Add customer',
            'row'    => null,
            'action' => site_url('customers/store'),
        ]);
    }

    public function store(): RedirectResponse
    {
        $model = new CustomerModel();
        $data  = $this->request->getPost(self::FIELDS) + ['user_id' => $this->uid()];

        if (! $model->insert($data)) {
            return redirect()->back()->withInput()->with('errors', $model->errors());
        }

        return redirect()->to('/customers')->with('success', 'Customer added.');
    }

    public function edit(int $id): string
    {
        return view('customers/form', [
            'title'  => 'Edit customer',
            'row'    => $this->ownedOr404(new CustomerModel(), $id),
            'action' => site_url('customers/update/' . $id),
        ]);
    }

    public function update(int $id): RedirectResponse
    {
        $model = new CustomerModel();
        $this->ownedOr404($model, $id);

        if (! $model->update($id, $this->request->getPost(self::FIELDS))) {
            return redirect()->back()->withInput()->with('errors', $model->errors());
        }

        return redirect()->to('/customers')->with('success', 'Customer updated.');
    }

    public function delete(int $id): RedirectResponse
    {
        $model = new CustomerModel();
        $this->ownedOr404($model, $id);
        $model->delete($id); // soft delete: existing invoices keep their customer

        return redirect()->to('/customers')->with('success', 'Customer deleted.');
    }
}
