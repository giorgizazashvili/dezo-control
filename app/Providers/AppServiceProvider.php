<?php

namespace App\Providers;

use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\URL;
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
        URL::forceScheme('https');
        $this->app['request']->server->set('HTTPS', 'on');

        FilamentView::registerRenderHook(
            PanelsRenderHook::SIDEBAR_FOOTER,
            fn (): string => view('filament.footer')->render(),
        );

        FilamentView::registerRenderHook(
            PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE,
            fn (): string => view('filament.login-header')->render(),
        );

        FilamentView::registerRenderHook(
            PanelsRenderHook::SCRIPTS_AFTER,
            fn (): string => '<script src="https://cdn.jsdelivr.net/npm/signature_pad@4/dist/signature_pad.umd.min.js"></script>',
        );

        FilamentView::registerRenderHook(
            PanelsRenderHook::STYLES_AFTER,
            fn (): string => '<style>
                .fi-simple-layout {
                    min-height: 100vh;
                    background-color: #f3f4f6;
                    background-image:
                        radial-gradient(ellipse at 0% 0%, rgba(204,0,0,0.06) 0%, transparent 50%),
                        radial-gradient(ellipse at 100% 100%, rgba(204,0,0,0.04) 0%, transparent 50%);
                }
            </style>',
        );
    }
}
