<?php

namespace App\Filament\Agent\Pages;

use App\Models\Customer;
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
        $this->agent = auth()->id();
    }

    public function getOrders()
    {
        if (! $this->agent || ! $this->date) {
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
