<?php

namespace App\Filament\Pages;

use App\Models\Customer;
use App\Models\User;
use Filament\Pages\Page;

class OrdersByDate extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.pages.orders-by-date';

    public ?string $date = null;

    public ?int $agent = null;

    public function mount(): void
    {
        $this->date = request('date');

        $agentId = request()->query('agent');
        $validatedAgentId = is_scalar($agentId)
            ? filter_var($agentId, FILTER_VALIDATE_INT)
            : false;

        $this->agent = $validatedAgentId !== false && $validatedAgentId > 0
            ? $validatedAgentId
            : null;
    }

    public function getOrders()
    {
        $agentQuery = User::query()
            ->role('Agent')
            ->whereKey($this->agent);

        if (! auth()->user()?->hasRole('Admin')) {
            $agentQuery->where('is_active', true);
        }

        if (! $this->agent || ! $agentQuery->exists()) {
            return collect();
        }

        return Customer::with([
            'category',
            'machineModel',
            'user',
        ])
            ->whereDate('booking_date', $this->date)
            ->where('agent_id', $this->agent)
            ->orderBy('created_at')
            ->get();
    }

    public function getTitle(): string
    {
        return 'Orders - '.$this->date;
    }
}
