<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\InvoiceModel;

class Dashboard extends AppController
{
    public function index(): string
    {
        $invoices = new InvoiceModel();

        return view('dashboard/index', [
            'title'  => 'Dashboard',
            'stats'  => $invoices->statsForUser($this->uid()),
            'latest' => $invoices->latestForUser($this->uid(), 10),
        ]);
    }
}
