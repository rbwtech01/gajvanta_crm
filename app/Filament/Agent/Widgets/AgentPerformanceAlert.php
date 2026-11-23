<?php

namespace App\Filament\Agent\Widgets;

use App\Models\Customer;
// use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\Widget;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;

class AgentPerformanceAlert extends Widget
{
    // use InteractsWithPageFilters;
    private const ALERT_AFTER_HOURS = 72;

    private const GRACE_PERIOD_HOURS = 1;
    // private const GRACE_PERIOD_SECONDS = 1;

    protected string $view = 'filament.agent.widgets.agent-performance-alert';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = -100;

    public int $sales = 0;

    public int $target = 300000;

    public int $remaining = 0;

    public float $percentage = 0;

    public string $status = 'red';

    public function render(): View
    {
        $this->calculate();

        return view($this->view);
    }

    protected function calculate(): void
    {
        $this->target = 300000;

        // Current month sales only
        $this->sales = (int) Customer::query()
            ->where('user_id', auth()->id())
            ->whereMonth('booking_date', now()->month)
            ->whereYear('booking_date', now()->year)
            ->sum('price');

        // Current month information
        $daysInMonth = now()->daysInMonth;
        $currentDay = now()->day;

        // Daily target
        $dailyTarget = $this->target / $daysInMonth;

        // Expected sales till today
        $expectedTarget = $dailyTarget * $currentDay;

        $this->remaining = max(0, $this->target - $this->sales);

        // Progress against expected target
        $this->percentage = round(
            ($this->sales / max($expectedTarget, 1)) * 100,
            2
        );

        /*
        RED
        Below expected target

        YELLOW
        Expected target achieved
        But monthly target not yet achieved

        GREEN
        Monthly target achieved
        */

        if ($this->sales >= $this->target) {

            $this->status = 'green';

        } elseif ($this->sales >= $expectedTarget) {

            $this->status = 'yellow';

        } else {

            $this->status = 'red';

        }
    }


    protected function getLatestOrder(): ?Customer
    {
        return Customer::query()
            ->where('user_id', auth()->id())
            ->latest('created_at')
            ->first();
    }

    public function shouldShowOrderAlert(): bool
    {
        $latestOrder = $this->getLatestOrder();

        // No order has ever been created.
        if (! $latestOrder) {
            return true;
        }

        // A new order was created within the last 72 hours.
        if ($latestOrder->created_at->gt(
            now()->subHours(self::ALERT_AFTER_HOURS)
        )) {
            return false;
        }

        // Check whether the agent recently acknowledged the alert.
        $acknowledgedAt = session('agent_order_alert_acknowledged_at');

        if ($acknowledgedAt) {
            $expiresAt = Carbon::parse($acknowledgedAt)
                ->addHours(self::GRACE_PERIOD_HOURS);
                
            // $expiresAt = Carbon::parse($acknowledgedAt)
            //     ->addSeconds(self::GRACE_PERIOD_SECONDS);

            // Agent is still inside the 1-hour grace period.
            if (now()->lt($expiresAt)) {
                return false;
            }

            // Grace period has expired.
            session()->forget('agent_order_alert_acknowledged_at');
        }

        return true;
    }

    public function acknowledgeOrderAlert(): void
    {
        session()->put(
            'agent_order_alert_acknowledged_at',
            now()->toDateTimeString()
        );
    }

    public function getLastOrderDate(): ?string
    {
        $latestOrder = $this->getLatestOrder();

        if (! $latestOrder) {
            return null;
        }

        return $latestOrder->created_at->format('d M Y, h:i A');
    }

    public function getTimeSinceLastOrder(): ?string
    {
        $latestOrder = $this->getLatestOrder();

        if (! $latestOrder) {
            return null;
        }

        return $latestOrder->created_at->diffForHumans();
    }

    protected function getPollingInterval(): ?string
    {
        return '60s';
    }
}
