<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use Carbon\Carbon;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ProductSales extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Product Sales Performance';

    protected static ?int $sort = 5;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getProductSalesQuery())
            ->columns([

                // Serial Number
                Tables\Columns\TextColumn::make('index')
                    ->label('#')
                    ->rowIndex(),

                // Machine Name
                Tables\Columns\TextColumn::make('machineModel.name')
                    ->label('Machine')
                    ->searchable(),

                // Quantity Sold
                Tables\Columns\TextColumn::make('total_quantity')
                    ->label('Qty Sold')
                    ->numeric()
                    ->sortable(),

                // Orders
                Tables\Columns\TextColumn::make('total_orders')
                    ->label('Orders')
                    ->numeric()
                    ->sortable(),

                // Revenue
                Tables\Columns\TextColumn::make('total_revenue')
                    ->label('Revenue')
                    ->money('INR')
                    ->sortable(),
            ])
            ->defaultSort('total_quantity', 'desc')
            ->paginated([10, 25, 50]);
    }

    protected function getProductSalesQuery(): Builder
    {
        /*
        |--------------------------------------------------------------------------
        | SAME FILTERS AS RevenueTrend
        |--------------------------------------------------------------------------
        */

        $period = $this->pageFilters['period'] ?? 'this_month';

        $from = $this->pageFilters['from'] ?? null;

        $to = $this->pageFilters['to'] ?? null;

        $query = Customer::query()->when(
            $this->pageFilters['agent'] ?? null,
            fn ($query, $agentId) => $query->where('agent_id', $agentId)
        );

        /*
        |--------------------------------------------------------------------------
        | CUSTOM DATE RANGE
        |--------------------------------------------------------------------------
        */

        if ($from || $to) {

            $query
                ->when(
                    $from,
                    fn ($q) =>
                        $q->whereDate(
                            'booking_date',
                            '>=',
                            $from
                        )
                )
                ->when(
                    $to,
                    fn ($q) =>
                        $q->whereDate(
                            'booking_date',
                            '<=',
                            $to
                        )
                );

        } else {

            /*
            |--------------------------------------------------------------------------
            | SAME PERIOD LOGIC AS RevenueTrend
            |--------------------------------------------------------------------------
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

                    $query->whereBetween(
                        'booking_date',
                        [
                            now()->startOfWeek(),
                            now()->endOfWeek(),
                        ]
                    );

                    break;


                case 'last_7_days':

                    $query->whereBetween(
                        'booking_date',
                        [
                            today()->subDays(6),
                            today(),
                        ]
                    );

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

                    $query->whereBetween(
                        'booking_date',
                        [
                            now()->startOfQuarter(),
                            now()->endOfQuarter(),
                        ]
                    );

                    break;


                case 'this_year':

                    $query->whereYear(
                        'booking_date',
                        now()->year
                    );

                    break;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | PRODUCT SALES AGGREGATION
        |--------------------------------------------------------------------------
        */

        return $query
            ->select([
                /*
                |--------------------------------------------------------------------------
                | Filament needs a unique record key.
                | machine_model_id is unique per aggregated row.
                |--------------------------------------------------------------------------
                */
                DB::raw('machine_model_id as id'),

                'machine_model_id',

                DB::raw(
                    'SUM(quantity) as total_quantity'
                ),

                DB::raw(
                    'COUNT(*) as total_orders'
                ),

                /*
                |--------------------------------------------------------------------------
                | Same revenue concept as RevenueTrend:
                | SUM(price)
                |--------------------------------------------------------------------------
                */
                DB::raw(
                    'SUM(price) as total_revenue'
                ),
            ])

            ->with('machineModel')

            ->whereNotNull('machine_model_id')

            ->groupBy('machine_model_id');
    }
}