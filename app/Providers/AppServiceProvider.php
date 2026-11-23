<?php

namespace App\Providers;

use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        FilamentAsset::register([
            Js::make(
                'admin-date-range-presets',
                resource_path('js/admin-date-range-presets.js'),
            ),
        ]);

        FilamentView::registerRenderHook(
            PanelsRenderHook::AUTH_LOGIN_FORM_AFTER,
            fn (): string => Blade::render(<<<'BLADE'
                <div class="mt-6 flex justify-center">
                    <x-filament::button
                        tag="a"
                        href="{{ config('app.url') }}"
                        color="gray"
                        icon="heroicon-m-arrow-left"
                    >
                        Back
                    </x-filament::button>
                </div>
            BLADE),
        );
    }
}
