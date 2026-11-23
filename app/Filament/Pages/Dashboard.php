<?php

namespace App\Filament\Pages;

use App\Models\User;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\ViewField;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Actions\FilterAction;
use Filament\Pages\Dashboard\Concerns\HasFiltersAction;

class Dashboard extends BaseDashboard
{
    use HasFiltersAction;

    public function mount(): void
    {
        if (empty($this->filters)) {
            $this->filters = [
                'agent' => null,
                'period' => 'this_month',
                'from' => null,
                'to' => null,
            ];
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            FilterAction::make()
                ->label('Filter')
                ->icon('heroicon-o-funnel')
                ->schema([
                    Select::make('agent')
                        ->label('Agent')
                        ->placeholder('All agents')
                        ->options(
                            fn (): array => User::role('Agent')
                                ->where('is_active', true)
                                ->where('name', '!=', 'Amazon')
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->all()
                        )
                        ->searchable()
                        ->preload(),

                    ViewField::make('period')
                        ->label('Period')
                        ->view('filament.components.date-range-picker')
                        ->columnSpanFull(),

                    Hidden::make('from'),
                    Hidden::make('to'),
                ])
                ->modalHeading('Filter Dashboard')
                ->modalSubmitActionLabel('Apply Filters')
                ->modalCancelActionLabel('Cancel'),
        ];
    }
}