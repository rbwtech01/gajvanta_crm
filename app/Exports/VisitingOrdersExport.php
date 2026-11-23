<?php

namespace App\Exports;

use App\Models\VisitingOrder;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class VisitingOrdersExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    WithStyles,
    ShouldAutoSize
{
    protected int $rowNumber = 0;

    public function query(): Builder
    {
        return VisitingOrder::query()
            ->with([
                'agent.callCenter',
            ])
            ->latest('visit_date');
    }

    public function headings(): array
    {
        return [
            '#',
            'Agent',
            'Call Center',
            'Visit Date',
            'Visitor Name',
            'Created On',
            'Primary Mobile',
            'Secondary Mobile',
            'City',
            'State',
            'Status',
            'Can Create From',
            'Remark',
        ];
    }

    public function map($record): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,

            $record->agent?->name ?? 'Not Assigned',

            $record->agent?->callCenter?->name ?? 'Not Assigned',

            $record->visit_date
                ? Carbon::parse($record->visit_date)->format('d M Y')
                : '',

            $record->visitor_name,

            $record->created_at
                ? Carbon::parse($record->created_at)->format('d M Y, h:i A')
                : '',

            $record->primary_mobile,

            $record->secondary_mobile ?? 'Not Provided',

            $record->city,

            $record->state,

            $record->cooling_until?->isFuture()
                ? 'Active'
                : 'Expired',

            $record->cooling_until
                ? Carbon::parse($record->cooling_until)->format('d M Y, h:i A')
                : 'Available',

            $record->remark ?? 'No Remark',
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