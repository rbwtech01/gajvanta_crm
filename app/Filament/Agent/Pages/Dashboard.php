<?php

namespace App\Filament\Agent\Pages;

use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Schema;
use App\Filament\Agent\Widgets\AgentPerformanceAlert;
use Illuminate\Contracts\Support\Htmlable;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    protected static ?string $navigationLabel = 'Dashboard';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-home';

    protected bool $persistsFiltersInSession = true;

    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('period')
                    ->label('Period')
                    ->default('this_month')
                    ->live()
                    ->options([
                        'all' => 'All Time',
                        'today' => 'Today',
                        'yesterday' => 'Yesterday',
                        'this_week' => 'This Week',
                        'last_7_days' => 'Last 7 Days',
                        'this_month' => 'This Month',
                        'last_month' => 'Last Month',
                        'this_quarter' => 'This Quarter',
                        'this_year' => 'This Year',
                    ]),
            ]);
    }

    // protected function getHeaderWidgets(): array
    // {
    //     return [
    //         AgentPerformanceAlert::class,
    //     ];
    // }

    public function getColumns(): int|array
    {
        return [
            'md' => 2,
            'xl' => 4,
        ];
    }

    public function getTitle(): string | Htmlable
    {
        return (auth()->user()?->name ?? 'Agent') . ' Dashboard';
    }
}