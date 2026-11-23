<?php

namespace App\Filament\Pages;

use App\Exports\DashboardDetailsExport;
use App\Models\CallCenter;
use App\Models\CategoryAttachment;
use App\Models\Customer;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class DashboardDetails extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.pages.dashboard-details';

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedChartBar;

    protected static ?string $navigationLabel = 'Dashboard Details';

    protected static bool $shouldRegisterNavigation = false;

    public string $type = 'customers';

    public string $period = 'this_month';

    public ?string $from = null;

    public ?string $to = null;

    public ?int $agentId = null;

    public function mount(): void
    {
        $this->type = request('type', 'customers');

        $this->period = request('period', 'this_month');

        $this->from = request('from');

        $this->to = request('to');

        $this->agentId = filled(request('agent_id'))
            ? (int) request('agent_id')
            : null;
    }

    public function getTitle(): string
    {
        return match ($this->type) {
            'agents' => 'Active Agents',
            'customers' => 'Total Customers',
            'unconfirmed' => 'Unconfirmed Orders',
            'hold' => 'Orders on Hold',
            'confirmed' => 'Confirmed Orders',
            'processing' => 'Processing Orders',
            'packed' => 'Packed Orders',
            'dispatched' => 'Dispatched Orders',
            'delivered' => 'Delivered Orders',
            'call_centers' => 'Active Call Centers',
            'missing_bill' => 'Missing Bill Number',
            'states' => 'Sales by State',
            'districts' => 'Sales by District',
            default => 'Dashboard Details',

        };
    }

    protected function getExportFileName(): string
    {
        return match ($this->type) {
            'agents' => 'active-agents.xlsx',
            'customers' => 'total-customers.xlsx',
            'unconfirmed' => 'unconfirmed-orders.xlsx',
            'hold' => 'hold-orders.xlsx',
            'confirmed' => 'confirmed-orders.xlsx',
            'processing' => 'processing-orders.xlsx',
            'packed' => 'packed-orders.xlsx',
            'dispatched' => 'dispatched-orders.xlsx',
            'delivered' => 'delivered-orders.xlsx',
            'call_centers' => 'active-call-centers.xlsx',
            'missing_bill' => 'missing-bill-orders.xlsx',
            default => 'dashboard-details.xlsx',
        };
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => $this->getDetailsQuery())
            ->columns($this->getTableColumns())
            ->headerActions([
                Action::make('export')
                    ->label('Export Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function () {
                        return Excel::download(
                            new DashboardDetailsExport(
                                type: $this->type,
                                period: $this->period,
                                from: $this->from,
                                to: $this->to,
                                agentId: $this->agentId,
                            ),
                            $this->getExportFileName()
                        );
                    }),
            ])
            ->recordUrl(null)
            ->paginated([10, 25, 50, 100]);
    }

    protected function getDetailsQuery(): Builder
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
         * Keep Amazon exclusions exactly as your dashboard widget:
         *
         * Total Customers
         * Unconfirmed
         * Missing Bill Number
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

        /*
         * Keep the exact status conditions from your dashboard.
         */
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
    | Table Columns
    |--------------------------------------------------------------------------
    */

    protected function getTableColumns(): array
    {
        /*
         * Active Agents
         */
        if ($this->type === 'agents') {
            return [
                TextColumn::make('id')
                    ->label('#')
                    ->rowIndex(),

                TextColumn::make('name')
                    ->label('Agent')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('callCenter.name')
                    ->label('Call Center')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Not Assigned'),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('customers_count')
                    ->label('Bookings')
                    ->sortable(),

                TextColumn::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(
                        fn ($state): string => $state ? 'Active' : 'Inactive'
                    )
                    ->color(
                        fn ($state): string => $state ? 'success' : 'gray'
                    ),
            ];
        }

        /*
         * Active Call Centers
         */
        if ($this->type === 'call_centers') {
            return [
                TextColumn::make('id')
                    ->label('#')
                    ->rowIndex(),

                TextColumn::make('name')
                    ->label('Call Center')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->date('d M Y')
                    ->sortable(),
            ];
        }

        /*
|--------------------------------------------------------------------------
| Sales by State
|--------------------------------------------------------------------------
*/
        if ($this->type === 'states') {
            return [
                TextColumn::make('sr_no')
                    ->label('#')
                    ->rowIndex(),

                TextColumn::make('state')
                    ->label('State')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('sales_value')
                    ->label('Sales Value')
                    ->money('INR')
                    ->sortable(),

                TextColumn::make('customers_count')
                    ->label('Customers')
                    ->sortable(),

                TextColumn::make('agents_count')
                    ->label('Agents')
                    ->sortable(),
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Sales by District
        |--------------------------------------------------------------------------
        */
        if ($this->type === 'districts') {
            return [
                TextColumn::make('sr_no')
                    ->label('#')
                    ->rowIndex(),

                TextColumn::make('district')
                    ->label('District')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('sales_value')
                    ->label('Sales Value')
                    ->money('INR')
                    ->sortable(),

                TextColumn::make('customers_count')
                    ->label('Customers')
                    ->sortable(),

                TextColumn::make('agents_count')
                    ->label('Agents')
                    ->sortable(),
            ];
        }

        /*
         * Customer / Order based cards
         */
        return [
            TextColumn::make('sr_no')
                ->label('#')
                ->rowIndex(),

            TextColumn::make('booking_date')
                ->label('Booking Date')
                ->date('d M Y')
                ->sortable(),

            TextColumn::make('agent.callCenter.name')
                ->label('Call Center')
                ->placeholder('Not Assigned')
                ->sortable(),

            TextColumn::make('agent.name')
                ->label('Agent')
                ->placeholder('Not Assigned')
                ->searchable()
                ->sortable(),

            TextColumn::make('customer_name')
                ->label('Customer Name')
                ->searchable()
                ->sortable(),

            TextColumn::make('mobile')
                ->label('Mobile')
                ->searchable(),

            TextColumn::make('status')
                ->label('Status')
                ->badge(),

            TextColumn::make('category.name')
                ->label('Category')
                ->sortable(),

            TextColumn::make('machineModel.name')
                ->label('Machine Model')
                ->sortable(),

            TextColumn::make('attachments')
                ->label('Attachments')
                ->badge()
                ->state(function ($record) {
                    if (blank($record->attachments)) {
                        return [];
                    }

                    return CategoryAttachment::whereIn(
                        'id',
                        $record->attachments
                    )
                        ->pluck('title')
                        ->toArray();
                }),

            TextColumn::make('price')
                ->label('Price')
                ->money('INR')
                ->sortable(),

            TextColumn::make('advance_payment')
                ->label('Advance Payment')
                ->money('INR')
                ->sortable(),

            TextColumn::make('state')
                ->label('State')
                ->searchable()
                ->sortable(),

            TextColumn::make('district')
                ->label('District')
                ->searchable()
                ->sortable(),

            TextColumn::make('bill_number')
                ->label('Bill Number')
                ->placeholder('Missing')
                ->searchable()
                ->copyable(),

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
        /*
         * Custom date range
         */
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

        /*
         * Preset periods
         */
        switch ($period) {
            case 'today':
                $query->whereDate(
                    'booking_date',
                    today()
                );
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
