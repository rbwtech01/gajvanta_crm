<?php

namespace App\Filament\Resources\Customers;

use App\Filament\Resources\Customers\Pages\CreateCustomer;
use App\Filament\Resources\Customers\Pages\EditCustomer;
use App\Filament\Resources\Customers\Pages\ListCustomers;
use App\Filament\Resources\Customers\Schemas\CustomerForm;
use App\Filament\Resources\Customers\Tables\CustomersTable;
use App\Models\Customer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationLabel = 'All Customers';

    protected static ?string $pluralModelLabel = 'All Customers';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Users;

    protected static ?string $recordTitleAttribute = 'name';

    protected static bool $shouldRegisterNavigation = true;

    public static function form(Schema $schema): Schema
    {
        return CustomerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomersTable::configure($table)
            ->modifyQueryUsing(function (Builder $query): void {

                /*
                 * ---------------------------------------------------------
                 * URL PARAMETERS
                 * ---------------------------------------------------------
                 */

                $period = request()->query('period');
                $from = request()->query('from');
                $to = request()->query('to');
                $status = request()->query('status');
                $agentId = request()->query('agent_id');

                /*
                 * ---------------------------------------------------------
                 * EAGER LOAD
                 * ---------------------------------------------------------
                 */

                $query->with('shipment');

                /*
                 * ---------------------------------------------------------
                 * AGENT FILTER
                 * ---------------------------------------------------------
                 */

                $query->when(
                    filled($agentId),
                    fn (Builder $q) => $q->where('agent_id', $agentId)
                );

                /*
                 * ---------------------------------------------------------
                 * STATUS FILTER
                 * ---------------------------------------------------------
                 */

                $query->when(
                    filled($status),
                    fn (Builder $q) => $q->where('status', $status)
                );

                /*
                 * ---------------------------------------------------------
                 * AMAZON EXCLUSION
                 * ---------------------------------------------------------
                 */

                $query
                    ->whereDoesntHave('agent', function (Builder $q): void {
                        $q->where('name', 'Amazon');
                    })
                    ->whereDoesntHave('agent.callCenter', function (Builder $q): void {
                        $q->where('name', 'Amazon Store');
                    });

                /*
                 * ---------------------------------------------------------
                 * MISSING BILL NUMBER
                 * ---------------------------------------------------------
                 */

                if (request()->boolean('bill_missing')) {
                    $query->whereNull('bill_number');
                }

                /*
                 * ---------------------------------------------------------
                 * MISSING TRACKING ID
                 *
                 * Requirement:
                 * Shipment MUST exist
                 * AND tracking_id MUST be NULL
                 * ---------------------------------------------------------
                 */

                if (request()->boolean('tracking_missing')) {
                    $query->whereHas('shipment', function (Builder $q): void {
                        $q->whereNull('tracking_id');
                    });
                }

                /*
                 * ---------------------------------------------------------
                 * DATE FILTER
                 * ---------------------------------------------------------
                 */

                self::applyDateFilter(
                    $query,
                    $period,
                    $from,
                    $to
                );

                /*
                 * ---------------------------------------------------------
                 * FINAL ORDER
                 * ---------------------------------------------------------
                 */

                $query->latest('booking_date');
            });
    }

    protected static function applyDateFilter(
        Builder $query,
        ?string $period,
        ?string $from,
        ?string $to
    ): void {
        /*
         * Custom date range has priority.
         */
        if (filled($from) || filled($to)) {
            $query
                ->when(
                    filled($from),
                    fn (Builder $q) => $q->whereDate(
                        'booking_date',
                        '>=',
                        $from
                    )
                )
                ->when(
                    filled($to),
                    fn (Builder $q) => $q->whereDate(
                        'booking_date',
                        '<=',
                        $to
                    )
                );

            return;
        }

        if (blank($period) || $period === 'all') {
            return;
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
                    today()->subDays(6)->startOfDay(),
                    today()->endOfDay(),
                ]);
                break;

            case 'this_month':
                $query
                    ->whereDate(
                        'booking_date',
                        '>=',
                        now()->startOfMonth()
                    )
                    ->whereDate(
                        'booking_date',
                        '<=',
                        now()->endOfMonth()
                    );
                break;

            case 'last_month':
                $date = now()->subMonth();

                $query
                    ->whereDate(
                        'booking_date',
                        '>=',
                        $date->copy()->startOfMonth()
                    )
                    ->whereDate(
                        'booking_date',
                        '<=',
                        $date->copy()->endOfMonth()
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

            default:
                /*
                 * Keep the resource consistent with the dashboard.
                 */
                $query
                    ->whereDate(
                        'booking_date',
                        '>=',
                        now()->startOfMonth()
                    )
                    ->whereDate(
                        'booking_date',
                        '<=',
                        now()->endOfMonth()
                    );

                break;
        }
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCustomers::route('/'),
            'create' => CreateCustomer::route('/create'),
            'edit' => EditCustomer::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}