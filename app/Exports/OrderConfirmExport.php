<?php

namespace App\Exports;

use App\Models\CategoryAttachment;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OrderConfirmExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    WithStyles
{
    use Exportable;

    protected Builder $query;

    public function __construct(Builder $query)
    {
        $this->query = $query;
    }

    public function query(): Builder
    {
        return $this->query;
    }

    public function headings(): array
    {
        return [
            'Customer',
            'Mobile',
            'Category',
            'Machine',
            'Attachments',
            'Price',
            'Advance Payment',
            'Sales Executive',
            'Booking Date',
            'Delivery Scheduled',
            'Status',
            'Bill Number',
            'Remark',
            'District',
            'State',
            'Pincode',
            'Address',
        ];
    }

    public function map($customer): array
    {
        $attachments = [];

        if (filled($customer->attachments)) {
            $attachments = CategoryAttachment::whereIn(
                'id',
                $customer->attachments
            )
                ->pluck('title')
                ->implode(', ');
        }

        return [
            $customer->customer_name,
            $customer->mobile,
            $customer->category?->name,
            $customer->machineModel?->name,
            $attachments ?: 'N/A',
            $customer->price,
            $customer->advance_payment,
            $customer->agent?->name,
            $customer->booking_date
                ? \Carbon\Carbon::parse(
                    $customer->booking_date
                )->format('d/m/Y')
                : 'N/A',

            $customer->delivery_date
            ? Carbon\Carbon::parse(
                $customer->delivery_date
            )->format('d/m/Y')
            : 'Not Scheduled',
            match ($customer->status) {
                'created' => 'Created',
                'hold' => 'Hold',
                'processing' => 'Processing',
                default => ucfirst($customer->status ?? 'N/A'),
            },
            filled($customer->bill_number)
                ? $customer->bill_number
                : 'N/A',
            filled($customer->hold_remark)
                ? $customer->hold_remark
                : (
                    $customer->status === 'processing'
                        ? 'N/A'
                        : 'No remark'
                ),
            $customer->district,
            $customer->state,
            $customer->pincode,
            $customer->address,
        ];
    }

    public function styles(Worksheet $sheet): array
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