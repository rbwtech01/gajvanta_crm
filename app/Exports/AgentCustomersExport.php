<?php

namespace App\Exports;

use App\Models\CategoryAttachment;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AgentCustomersExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithStyles
{
    protected int $rowNumber = 0;

    public function __construct(
        protected ?int $agentId = null,
        protected ?string $from = null,
        protected ?string $until = null,
    ) {}

    public function query(): Builder
    {
        return Customer::query()
            ->with([
                'agent.callCenter',
                'category',
                'machineModel',
            ])

            // Exclude Amazon orders
            ->whereDoesntHave('agent', function (Builder $query) {
                $query->where('name', 'Amazon');
            })
            ->whereDoesntHave('agent.callCenter', function (Builder $query) {
                $query->where('name', 'Amazon Store');
            })

            // Agent filter
            ->when(
                $this->agentId,
                fn (Builder $query) =>
                    $query->where('user_id', $this->agentId)
            )

            // From date
            ->when(
                $this->from,
                fn (Builder $query) =>
                    $query->whereDate('booking_date', '>=', $this->from)
            )

            // Until date
            ->when(
                $this->until,
                fn (Builder $query) =>
                    $query->whereDate('booking_date', '<=', $this->until)
            )

            ->orderByDesc('booking_date');
    }

    public function headings(): array
    {
        return [
            '#',
            'Booking Date',
            'Call Center',
            'Agent',
            'Customer Name',
            'Mobile',
            'Status',
            'Category',
            'Machine Model',
            'Attachments',
            'Price',
            'Advance Payment',
            'State',
            'District',
        ];
    }

    public function map($customer): array
    {
        $this->rowNumber++;

        $attachments = '';

        if (filled($customer->attachments)) {
            $attachments = CategoryAttachment::whereIn(
                'id',
                $customer->attachments
            )
                ->pluck('title')
                ->implode(', ');
        }

        return [
            $this->rowNumber,

            $customer->booking_date
                ? Carbon::parse($customer->booking_date)->format('d M Y')
                : null,

            $customer->agent?->callCenter?->name,
            $customer->agent?->name,
            $customer->customer_name,
            $customer->mobile,
            $customer->status,
            $customer->category?->name,
            $customer->machineModel?->name,
            $attachments,
            $customer->price,
            $customer->advance_payment,
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