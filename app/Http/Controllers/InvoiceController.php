<?php

namespace App\Http\Controllers;

use App\Models\CategoryAttachment;
use App\Models\Customer;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class InvoiceController extends Controller
{
    public function download(Customer $customer)
    {
        $invoiceNo = 'RBW/' . random_int(1000, 999999);

        $pdf = Pdf::loadView('pdf.invoice', [
            'customer' => $customer,
            'attachments' => CategoryAttachment::whereIn(
                'id',
                $customer->attachments ?? []
            )->pluck('title')->toArray(),

            'balance' => $customer->price - $customer->advance_payment,
            'invoiceNo' => $invoiceNo,
        ]);

        return $pdf->download('Invoice-' . Str::slug($customer->customer_name) . '.pdf');
    }
}