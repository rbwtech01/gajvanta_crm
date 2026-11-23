<?php

namespace App\Exports;

use App\Models\CallCenter;
use App\Models\CategoryAttachment;
use App\Models\Customer;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DashboardDetailsExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    protected int $rowNumber = 0;

    public function __construct(
        protected string $type,
        protected string $period = 'this_month',
        protected ?string $from = null,
        protected ?string $to = null,
        protected ?int $agentId = null,
    ) {}

    public function query(): Builder
    {
        return match ($this->type) {
            'agents' => $this->agentsQuery(),

            'call_centers' => $this->callCentersQuery(),

            'states' => $this->statesQuery(),

            'districts' => $this->districtsQuery(),

            default => $this->customersQuery(),
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Agents
    |--------------------------------------------------------------------------
    */

    protected function agentsQuery(): Builder
    {
        return User::query()
            ->with('callCenter')
            ->role('Agent')
            ->where('name', '!=', 'Amazon')
            ->when(
                $this->agentId,
                fn (Builder $query, $agentId) => $query->whereKey($agentId)
            )
            ->whereHas('customers', function (Builder $query) {
                $this->applyDateFilter(
                    $query,
                    $this->period,
                    $this->from,
                    $this->to,
                );
            })
            ->withCount([
                'customers' => function (Builder $query) {
                    $this->applyDateFilter(
                        $query,
                        $this->period,
                        $this->from,
                        $this->to,
                    );
                },
            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Call Centers
    |--------------------------------------------------------------------------
    */

    protected function callCentersQuery(): Builder
    {
        return CallCenter::query()
            ->whereHas('users.customers', function (Builder $query) {
                $query->when(
                    $this->agentId,
                    fn (Builder $query, $agentId) => $query->where('agent_id', $agentId)
                );

                $this->applyDateFilter(
                    $query,
                    $this->period,
                    $this->from,
                    $this->to,
                );
            });
    }

    protected function statesQuery(): Builder
    {
        $query = Customer::query()
            ->select([
                'customers.state',
                DB::raw('MIN(customers.id) as id'),
                DB::raw('SUM(customers.price) as sales_value'),
                DB::raw('COUNT(*) as customers_count'),
                DB::raw('COUNT(DISTINCT customers.agent_id) as agents_count'),
            ])
            ->whereNotNull('customers.state')
            ->where('customers.state', '!=', '');

        $this->applyCommonCustomerFilters($query);

        return $this->applyDateFilter(
            $query,
            $this->period,
            $this->from,
            $this->to,
        )
            ->groupBy('customers.state')
            ->orderByDesc('sales_value');
    }

    protected function districtsQuery(): Builder
    {
        $query = Customer::query()
            ->select([
                'customers.district',
                DB::raw('MIN(customers.id) as id'),
                DB::raw('SUM(customers.price) as sales_value'),
                DB::raw('COUNT(*) as customers_count'),
                DB::raw('COUNT(DISTINCT customers.agent_id) as agents_count'),
            ])
            ->whereNotNull('customers.state')
            ->where('customers.state', '!=', '')
            ->whereNotNull('customers.district')
            ->where('customers.district', '!=', '');

        $this->applyCommonCustomerFilters($query);

        return $this->applyDateFilter(
            $query,
            $this->period,
            $this->from,
            $this->to,
        )
            ->groupBy('customers.district')
            ->orderByDesc('sales_value');
    }

    protected function applyCommonCustomerFilters(Builder $query): Builder
    {
        return $query
            ->when(
                $this->agentId,
                fn (Builder $query, $agentId) => $query->where('customers.agent_id', $agentId)
            )
            ->whereNotExists(function ($query) {
                $query
                    ->selectRaw('1')
                    ->from('users')
                    ->whereColumn('users.id', 'customers.agent_id')
                    ->where('users.name', 'Amazon');
            })
            ->whereNotExists(function ($query) {
                $query
                    ->selectRaw('1')
                    ->from('users')
                    ->join(
                        'call_centers',
                        'call_centers.id',
                        '=',
                        'users.call_center_id'
                    )
                    ->whereColumn('users.id', 'customers.agent_id')
                    ->where('call_centers.name', 'Amazon Store');
            });
    }

    /*
    |--------------------------------------------------------------------------
    | Customers / Orders
    |--------------------------------------------------------------------------
    */

    protected function customersQuery(): Builder
    {
        $query = Customer::query()
            ->with([
                'agent.callCenter',
                'category',
                'machineModel',
            ])
            ->when(
                $this->agentId,
                fn (Builder $query, $agentId) => $query->where('agent_id', $agentId)
            );

        /*
        * Same Amazon exclusions as DashboardDetails.
        */
        if (in_array($this->type, [
            'customers',
            'unconfirmed',
            'missing_bill',
        ], true)) {
            $query
                ->whereDoesntHave('agent', function (Builder $query) {
                    $query->where('name', 'Amazon');
                })
                ->whereDoesntHave('agent.callCenter', function (Builder $query) {
                    $query->where('name', 'Amazon Store');
                });
        }

        match ($this->type) {
            'unconfirmed' => $query->whereIn('status', [
                'created',
                'supervisor_hold',
                'supervisor_confirmed',
            ]),

            'hold' => $query->where('status', 'hold'),

            'confirmed' => $query->where('status', 'confirmed'),

            'processing' => $query->where('status', 'processing'),

            'packed' => $query->where('status', 'packed'),

            'dispatched' => $query->where('status', 'dispatched'),

            'delivered' => $query->where('status', 'delivered'),

            'missing_bill' => $query->whereNull('bill_number'),

            default => null,
        };

        return $this->applyDateFilter(
            $query,
            $this->period,
            $this->from,
            $this->to,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Excel Headers
    |--------------------------------------------------------------------------
    */

    public function headings(): array
    {
        if ($this->type === 'agents') {
            return [
                '#',
                'Agent',
                'Email',
                'Call Center',
                'Bookings',
                'Status',
            ];
        }

        if ($this->type === 'call_centers') {
            return [
                '#',
                'Call Center',
                'Created',
            ];
        }

        if ($this->type === 'states') {
            return [
                '#',
                'State',
                'Sales_Value',
                'Customers',
                'Agents',
            ];
        }

        if ($this->type === 'districts') {
            return [
                '#',
                'District',
                'Sales_Value',
                'Customers',
                'Agents',
            ];
        }

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
            'Bill Number',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Excel Row Mapping
    |--------------------------------------------------------------------------
    */

    public function map($record): array
    {
        $this->rowNumber++;

        /*
        |--------------------------------------------------------------------------
        | Active Agents
        |--------------------------------------------------------------------------
        */
        if ($this->type === 'agents') {
            return [
                $this->rowNumber,
                $record->name,
                $record->email,
                $record->callCenter?->name ?? 'Not Assigned',
                $record->customers_count,
                $record->is_active ? 'Active' : 'Inactive',
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Call Centers
        |--------------------------------------------------------------------------
        */
        if ($this->type === 'call_centers') {
            return [
                $this->rowNumber,
                $record->name ?? 'Not Assigned',
                $record->created_at
                    ? Carbon::parse($record->created_at)->format('d M Y')
                    : '',
            ];
        }

        if ($this->type === 'states') {
            return [
                $this->rowNumber,
                $record->state,
                $record->sales_value,
                $record->customers_count,
                $record->agents_count,
            ];
        }

        if ($this->type === 'districts') {
            return [
                $this->rowNumber,
                $record->district,
                $record->sales_value,
                $record->customers_count,
                $record->agents_count,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Customer / Order Details
        |--------------------------------------------------------------------------
        */

        $attachments = [];

        if (filled($record->attachments)) {
            $attachments = CategoryAttachment::whereIn(
                'id',
                $record->attachments
            )
                ->pluck('title')
                ->toArray();
        }

        return [
            // #
            $this->rowNumber,

            // Booking Date
            $record->booking_date
                ? Carbon::parse($record->booking_date)->format('d M Y')
                : '',

            // Call Center
            $record->agent?->callCenter?->name ?? 'Not Assigned',

            // Agent
            $record->agent?->name ?? 'Not Assigned',

            // Customer Name
            $record->customer_name,

            // Mobile
            $record->mobile,

            // Status
            $record->status,

            // Category
            $record->category?->name ?? 'Not Assigned',

            // Machine Model
            $record->machineModel?->name ?? 'Not Assigned',

            // Attachments
            implode(', ', $attachments),

            // Price
            $record->price,

            // Advance Payment
            $record->advance_payment,

            // State
            $record->state,

            // District
            $record->district,

            // Bill Number
            $record->bill_number ?? 'Missing',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Header Styling
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | Date Filter
    |--------------------------------------------------------------------------
    */

    protected function applyDateFilter(
        Builder $query,
        string $period,
        ?string $from,
        ?string $to,
    ): Builder {
        if ($from || $to) {
            return $query
                ->when(
                    $from,
                    fn (Builder $query) => $query->whereDate(
                        'booking_date',
                        '>=',
                        $from
                    )
                )
                ->when(
                    $to,
                    fn (Builder $query) => $query->whereDate(
                        'booking_date',
                        '<=',
                        $to
                    )
                );
        }

        switch ($period) {
            case 'today':
                $query->whereDate('booking_date', today());
                break;

            case 'yesterday':
                $query->whereDate(
                    'booking_date',
                    today()->subDay()
                );
                break;

            case 'this_week':
                $query->whereBetween('booking_date', [
                    now()->startOfWeek(),
                    now()->endOfWeek(),
                ]);
                break;

            case 'last_7_days':
                $query->whereBetween('booking_date', [
                    today()->subDays(6),
                    today(),
                ]);
                break;

            case 'this_month':
                $query
                    ->whereMonth(
                        'booking_date',
                        now()->month
                    )
                    ->whereYear(
                        'booking_date',
                        now()->year
                    );
                break;

            case 'last_month':
                $date = now()->subMonth();

                $query
                    ->whereMonth(
                        'booking_date',
                        $date->month
                    )
                    ->whereYear(
                        'booking_date',
                        $date->year
                    );
                break;

            case 'this_quarter':
                $query->whereBetween('booking_date', [
                    now()->startOfQuarter(),
                    now()->endOfQuarter(),
                ]);
                break;

            case 'this_year':
                $query->whereYear(
                    'booking_date',
                    now()->year
                );
                break;
        }

        return $query;
    }
}
