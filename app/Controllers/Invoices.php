<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\InvoiceService;
use App\Libraries\PdfService;
use App\Models\CustomerModel;
use App\Models\InvoiceItemModel;
use App\Models\InvoiceModel;
use App\Models\ProductModel;
use App\Models\SettingModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\DownloadResponse;
use CodeIgniter\HTTP\RedirectResponse;
use DomainException;

class Invoices extends AppController
{
    /** @var array<int|string, string> */
    private array $inputErrors = [];

    public function index(): string
    {
        $model   = new InvoiceModel();
        $filters = [
            'status'      => (string) $this->request->getGet('status'),
            'customer_id' => (string) $this->request->getGet('customer_id'),
            'from'        => (string) $this->request->getGet('from'),
            'to'          => (string) $this->request->getGet('to'),
            'sort'        => (string) $this->request->getGet('sort'),
            'dir'         => (string) $this->request->getGet('dir'),
        ];

        return view('invoices/index', [
            'title'     => 'Invoices',
            'rows'      => $model->listForUser($this->uid(), $filters),
            'pager'     => $model->pager,
            'filters'   => $filters,
            'customers' => (new CustomerModel())->where('user_id', $this->uid())->orderBy('name')->findAll(),
            'statuses'  => InvoiceModel::STATUSES,
        ]);
    }

    public function create(): string
    {
        return $this->form('Create invoice', null, [], site_url('invoices/store'));
    }

    public function store(): RedirectResponse
    {
        $input = $this->validatedInput();

        if ($input === null) {
            return redirect()->back()->withInput()->with('errors', $this->inputErrors);
        }

        try {
            $id = (new InvoiceService())->create($this->uid(), $input['header'], $input['items']);
        } catch (DomainException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->to('/invoices/view/' . $id)->with('success', 'Invoice created.');
    }

    public function show(int $id): string
    {
        $invoice = $this->invoiceOr404($id);

        return view('invoices/view', [
            'title'   => 'Invoice ' . $invoice['invoice_no'],
            'invoice' => $invoice,
            'items'   => (new InvoiceItemModel())->forInvoice($id),
        ]);
    }

    public function edit(int $id): string|RedirectResponse
    {
        $invoice = $this->invoiceOr404($id);

        if ($invoice['status'] !== 'draft') {
            return redirect()->to('/invoices/view/' . $id)->with('error', 'Only draft invoices can be edited.');
        }

        return $this->form(
            'Edit ' . $invoice['invoice_no'],
            $invoice,
            (new InvoiceItemModel())->forInvoice($id),
            site_url('invoices/update/' . $id),
        );
    }

    public function update(int $id): RedirectResponse
    {
        $this->invoiceOr404($id);
        $input = $this->validatedInput();

        if ($input === null) {
            return redirect()->back()->withInput()->with('errors', $this->inputErrors);
        }

        try {
            (new InvoiceService())->update($this->uid(), $id, $input['header'], $input['items']);
        } catch (DomainException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->to('/invoices/view/' . $id)->with('success', 'Invoice updated.');
    }

    public function status(int $id): RedirectResponse
    {
        try {
            (new InvoiceService())->changeStatus($this->uid(), $id, (string) $this->request->getPost('status'));
        } catch (DomainException $e) {
            return redirect()->to('/invoices/view/' . $id)->with('error', $e->getMessage());
        }

        return redirect()->to('/invoices/view/' . $id)->with('success', 'Status updated.');
    }

    public function delete(int $id): RedirectResponse
    {
        try {
            (new InvoiceService())->delete($this->uid(), $id);
        } catch (DomainException $e) {
            return redirect()->to('/invoices/view/' . $id)->with('error', $e->getMessage());
        }

        return redirect()->to('/invoices')->with('success', 'Invoice deleted.');
    }

    public function pdf(int $id): DownloadResponse
    {
        $pdf  = new PdfService();
        $data = $this->documentData($id, $pdf->logoDataUri($this->settings()['logo_path'] ?? null));

        return $this->response->download($pdf->filename($data['invoice']['invoice_no']), $pdf->render($data))
            ->setContentType('application/pdf');
    }

    public function printView(int $id): string
    {
        $settings = $this->settings();
        $logo     = ! empty($settings['logo_path']) ? site_url('settings/logo') : null;

        return view('invoices/pdf_template', $this->documentData($id, $logo) + ['mode' => 'print']);
    }

    // ---------------------------------------------------------------- helpers

    /**
     * @return array<string, mixed>
     */
    private function invoiceOr404(int $id): array
    {
        $invoice = (new InvoiceModel())->findForUser($id, $this->uid());

        if ($invoice === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $invoice;
    }

    /**
     * @return array<string, mixed>
     */
    private function settings(): array
    {
        return (new SettingModel())->forUser($this->uid());
    }

    /**
     * @return array<string, mixed>
     */
    private function documentData(int $id, ?string $logoSrc): array
    {
        return [
            'invoice'  => $this->invoiceOr404($id),
            'items'    => (new InvoiceItemModel())->forInvoice($id),
            'settings' => $this->settings(),
            'logoSrc'  => $logoSrc,
        ];
    }

    /**
     * @param array<string, mixed>|null       $invoice
     * @param list<array<string, mixed>>      $items
     */
    private function form(string $title, ?array $invoice, array $items, string $action): string
    {
        return view('invoices/form', [
            'title'     => $title,
            'invoice'   => $invoice,
            'items'     => $items,
            'action'    => $action,
            'customers' => (new CustomerModel())->where('user_id', $this->uid())->orderBy('name')->findAll(),
            'products'  => (new ProductModel())->where('user_id', $this->uid())->orderBy('name')->findAll(),
        ]);
    }

    /**
     * Validate the posted invoice. Returns null when invalid; messages are in $this->inputErrors.
     *
     * @return array{header: array<string, mixed>, items: list<array<string, mixed>>}|null
     */
    private function validatedInput(): ?array
    {
        $items = $this->request->getPost('items');

        if (! is_array($items) || $items === []) {
            $this->inputErrors = ['items' => 'Add at least one line item.'];

            return null;
        }

        foreach ($items as $item) {
            if (! is_array($item)) {
                $this->inputErrors = ['items' => 'Each line item must be valid.'];

                return null;
            }

            foreach (['description', 'qty', 'unit_price', 'tax_percent', 'product_id'] as $field) {
                if (
                    ($field !== 'tax_percent' && $field !== 'product_id' && ! array_key_exists($field, $item))
                    || (array_key_exists($field, $item) && ! is_scalar($item[$field]))
                ) {
                    $this->inputErrors = ['items' => 'Each line item must be valid.'];

                    return null;
                }
            }
        }

        $rules = [
            'customer_id'         => 'required|is_natural_no_zero',
            'issue_date'          => 'required|valid_date[Y-m-d]',
            'due_date'            => 'required|valid_date[Y-m-d]',
            'discount'            => 'permit_empty|numeric|greater_than_equal_to[0]',
            'notes'               => 'permit_empty|max_length[2000]',
            'items.*.description' => 'required|max_length[255]',
            'items.*.qty'         => 'required|numeric|greater_than[0]|less_than[100000000]',
            'items.*.unit_price'  => 'required|numeric|greater_than_equal_to[0]|less_than[10000000000]',
            'items.*.tax_percent' => 'permit_empty|numeric|greater_than_equal_to[0]|less_than_equal_to[100]',
        ];

        if (! $this->validate($rules)) {
            $this->inputErrors = array_values($this->validator->getErrors());

            return null;
        }

        if ((string) $this->request->getPost('due_date') < (string) $this->request->getPost('issue_date')) {
            $this->inputErrors = ['due_date' => 'Due date cannot be before the issue date.'];

            return null;
        }

        return [
            'header' => $this->request->getPost(['customer_id', 'issue_date', 'due_date', 'discount', 'notes']),
            'items'  => array_values($items),
        ];
    }
}
