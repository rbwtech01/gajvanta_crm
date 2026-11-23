<?php

namespace App\Exports;

use App\Models\CategoryAttachment;
use App\Models\Customer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CustomersExport implements FromCollection, WithHeadings, WithStyles
{
    public function __construct(
        protected ?int $userId = null,
    ) {}

    public function collection()
    {
        return Customer::with([
                'category',
                'machineModel',
            ])
            ->when($this->userId, function ($query) {
                $query->where('user_id', $this->userId);
            })
            ->latest()
            ->get()
            ->map(function ($customer, $index) {

                $attachmentTitles = '';

                if (! empty($customer->attachments)) {

                    $attachmentIds = is_array($customer->attachments)
                        ? $customer->attachments
                        : json_decode($customer->attachments, true);

                    $attachmentTitles = CategoryAttachment::whereIn(
                        'id',
                        $attachmentIds ?? []
                    )
                        ->pluck('title')
                        ->implode(', ');
                }

                return [
                    'sr_no'             => $index + 1,
                    'customer_name'     => $customer->customer_name,
                    'booking_date'      => $customer->booking_date,
                    'mobile'            => $customer->mobile,
                    'category'          => $customer->category?->name ?? '',
                    'product'           => $customer->machineModel?->name ?? '',
                    'attachments'       => $attachmentTitles,
                    'price'             => $customer->price,
                    'advance_payment'   => $customer->advance_payment,
                    'status'            => $customer->status,
                    'district'          => $customer->district,
                    'state'             => $customer->state,
                    'payment_proof'     => $customer->getFirstMediaUrl('screenshots'),
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Sr. No.',
            'Customer Name',
            'Booking Date',
            'Mobile',
            'Category',
            'Product',
            'Attachments',
            'Price',
            'Advance Payment',
            'Status',
            'District',
            'State',
            'Payment Proof',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                ],
            ],
        ];
    }
}