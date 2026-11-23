<?php

namespace App\Filament\Pages;

use App\Models\User;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Collection;

class OrderCalendar extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.pages.order-calendar';

    protected Width|string|null $maxContentWidth = Width::Full;

    public function getHeading(): string
    {
        $agent = $this->getSelectedAgent();

        return $agent
            ? $agent->name.' Order Calendar'
            : 'Order Calendar';
    }

    public function getViewData(): array
    {
        $agents = $this->getAvailableAgents();

        return [
            'agents' => $agents,
            'selectedAgentId' => $this->getSelectedAgent($agents)?->getKey(),
        ];
    }

    /**
     * @return Collection<int, User>
     */
    private function getAvailableAgents(): Collection
    {
        $query = User::query()
            ->role('Agent')
            ->orderBy('name');

        if (! auth()->user()?->hasRole('Admin')) {
            $query
                ->whereKey(auth()->id())
                ->where('is_active', true);
        }

        return $query->get(['id', 'name']);
    }

    private function getSelectedAgent(?Collection $agents = null): ?User
    {
        $agents ??= $this->getAvailableAgents();

        $requestedAgentId = request()->query('agent');

        if (is_scalar($requestedAgentId) && filled($requestedAgentId)) {
            $requestedAgent = $agents->first(
                fn (User $agent): bool => (string) $agent->getKey() === (string) $requestedAgentId
            );

            if ($requestedAgent) {
                return $requestedAgent;
            }
        }

        $authenticatedUser = auth()->user();

        if ($authenticatedUser) {
            $authenticatedAgent = $agents->first(
                fn (User $agent): bool => $agent->is($authenticatedUser)
            );

            if ($authenticatedAgent) {
                return $authenticatedAgent;
            }
        }

        return $agents->first();
    }
}
