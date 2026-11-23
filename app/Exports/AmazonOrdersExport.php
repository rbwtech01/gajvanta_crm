<?php

namespace App\Exports;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class AmazonOrdersExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithStyles
{
    protected ?int $month;

    public function __construct(?int $month = null)
    {
        $this->month = $month;
    }

    public function query()
    {
        return Customer::query()
            ->with([
                'agent.callCenter',
                'category',
                'machineModel',
            ])
            ->where(function (Builder $query) {
                $query
                    ->whereHas('agent', function (Builder $query) {
                        $query->where('name', 'Amazon');
                    })
                    ->orWhereHas('agent.callCenter', function (Builder $query) {
                        $query->where('name', 'Amazon Store');
                    });
            })
            ->when(
                $this->month,
                fn (Builder $query) =>
                    $query->whereMonth('booking_date', $this->month)
            )
            ->orderByDesc('booking_date');
    }

    public function headings(): array
    {
        return [
            'Booking Date',
            'Call Center',
            'Customer Name',
            'Mobile',
            'Status',
            'Category',
            'Machine Model',
            'Price',
            'State',
            'District',
        ];
    }

    public function map($customer): array
    {
        return [
            $customer->booking_date
                ? Carbon::parse($customer->booking_date)->format('d M Y')
                : null,
            $customer->agent?->callCenter?->name,
            $customer->customer_name,
            $customer->mobile,
            $customer->status,
            $customer->category?->name,
            $customer->machineModel?->name,
            $customer->price,
            $customer->state,
            $customer->district,
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