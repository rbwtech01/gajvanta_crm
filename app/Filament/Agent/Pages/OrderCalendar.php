<?php

namespace App\Filament\Agent\Pages;

use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Collection;
use App\Models\User;

class OrderCalendar extends Page
{
    protected string $view = 'filament.pages.order-calendar';

    protected static ?string $navigationLabel = 'Order Calendar';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?int $navigationSort = 4;

    protected Width|string|null $maxContentWidth = Width::Full;

    public function getHeading(): string
    {
        return 'Order Calendar';
    }

    public function getViewData(): array
    {
        $agent = auth()->user();

        $agents = $agent instanceof User
            ? User::query()
                ->role('Agent')
                ->whereKey($agent->getKey())
                ->where('is_active', true)
                ->get(['id', 'name'])
            : new Collection();

        return [
            'agents' => $agents,
            'selectedAgentId' => $agents->first()?->getKey(),
        ];
    }
}
