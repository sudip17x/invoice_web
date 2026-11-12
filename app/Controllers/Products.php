<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\ProductModel;
use CodeIgniter\HTTP\RedirectResponse;

class Products extends AppController
{
    private const FIELDS = ['name', 'description', 'unit_price', 'tax_percent'];

    public function index(): string
    {
        $model  = new ProductModel();
        $search = trim((string) $this->request->getGet('q'));

        return view('products/index', [
            'title' => 'Products & services',
            'rows'  => $model->paginateForUser($this->uid(), $search),
            'pager' => $model->pager,
            'q'     => $search,
        ]);
    }

    public function create(): string
    {
        return view('products/form', [
            'title'  => 'Add product / service',
            'row'    => null,
            'action' => site_url('products/store'),
        ]);
    }

    public function store(): RedirectResponse
    {
        $model = new ProductModel();
        $data  = $this->request->getPost(self::FIELDS) + ['user_id' => $this->uid()];

        if (! $model->insert($data)) {
            return redirect()->back()->withInput()->with('errors', $model->errors());
        }

        return redirect()->to('/products')->with('success', 'Product added.');
    }

    public function edit(int $id): string
    {
        return view('products/form', [
            'title'  => 'Edit product / service',
            'row'    => $this->ownedOr404(new ProductModel(), $id),
            'action' => site_url('products/update/' . $id),
        ]);
    }

    public function update(int $id): RedirectResponse
    {
        $model = new ProductModel();
        $this->ownedOr404($model, $id);

        if (! $model->update($id, $this->request->getPost(self::FIELDS))) {
            return redirect()->back()->withInput()->with('errors', $model->errors());
        }

        return redirect()->to('/products')->with('success', 'Product updated.');
    }

    public function delete(int $id): RedirectResponse
    {
        $model = new ProductModel();
        $this->ownedOr404($model, $id);
        $model->delete($id); // soft delete: old invoice lines keep their text/price

        return redirect()->to('/products')->with('success', 'Product deleted.');
    }
}
