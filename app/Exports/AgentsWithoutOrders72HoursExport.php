<?php

namespace App\Exports;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AgentsWithoutOrders72HoursExport implements
    FromQuery,
    ShouldAutoSize,
    WithHeadings,
    WithMapping,
    WithStyles
{
    protected ?int $callCenterId;

    protected int $rowNumber = 0;

    public function __construct(?int $callCenterId = null)
    {
        $this->callCenterId = $callCenterId;
    }

    public function query(): Builder
    {
        $cutoff = now()->subHours(72);

        return User::query()
            ->role('Agent')
            ->where('is_active', true)

            ->when(
                $this->callCenterId,
                fn (Builder $query) =>
                    $query->where(
                        'call_center_id',
                        $this->callCenterId
                    )
            )

            ->whereNotExists(function ($query) use ($cutoff) {
                $query
                    ->selectRaw('1')
                    ->from('customers')
                    ->whereColumn(
                        'customers.user_id',
                        'users.id'
                    )
                    ->where(
                        'customers.created_at',
                        '>=',
                        $cutoff
                    );
            })

            ->withMax('customers', 'created_at')
            ->with('callCenter');
    }

    public function headings(): array
    {
        return [
            '#',
            'Agent',
            'Email',
            'Call Center',
            'Last Order',
            'Status',
        ];
    }

    public function map($agent): array
    {
        return [
            ++$this->rowNumber,

            $agent->name,

            $agent->email,

            $agent->callCenter?->name ?? 'N/A',

            $agent->customers_max_created_at
                ? Carbon::parse(
                    $agent->customers_max_created_at
                )->format('d M Y, h:i A')
                : 'No orders found',

            'No order for 72h+',
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