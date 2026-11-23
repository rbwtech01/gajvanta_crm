<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\Filament\AdminPanelProvider::class,
    App\Providers\Filament\AgentPanelProvider::class,
    App\Providers\Filament\ManagerPanelProvider::class,
    App\Providers\Filament\OperationsPanelProvider::class,
    App\Providers\Filament\PackingPanelProvider::class,
    App\Providers\Filament\ShipmentPanelProvider::class,
    App\Providers\Filament\SupervisorPanelProvider::class,
    CodeWithKyrian\FilamentDateRange\DateRangeServiceProvider::class,
];
