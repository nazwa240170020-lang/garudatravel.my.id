<?php

namespace App\Providers\Filament;

use App\Filament\Resources\Transactions\Widgets\TransactionStatsWidget;
use App\Filament\Widgets\AnalyticsAirlineChart;
use App\Filament\Widgets\AnalyticsDayOfWeekChart;
use App\Filament\Widgets\AnalyticsForecastWidget;
use App\Filament\Widgets\AnalyticsKpiCards;
use App\Filament\Widgets\AnalyticsPaymentStatusChart;
use App\Filament\Widgets\AnalyticsRevenueTrend;
use App\Filament\Widgets\AnalyticsRouteChart;
use App\Filament\Widgets\AnalyticsRouteTable;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandLogo(fn () => view('components.filament-logo'))
            ->brandLogoHeight('3rem')
            ->favicon(asset('images/logo.svg'))
            ->colors([
                'primary' => Color::hex('#2563EB'),
                'accent' => Color::hex('#4A0E30'),
                'secondary' => Color::hex('#1F212B'),
                'tertiary' => Color::hex('#6B7280'),
                'neutral' => Color::hex('#F7F8FB'),
                'surface' => Color::hex('#FFFFFF'),
                'gray' => Color::Zinc,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->widgets([
                TransactionStatsWidget::class,
                AnalyticsKpiCards::class,
                AnalyticsRevenueTrend::class,
                AnalyticsPaymentStatusChart::class,
                AnalyticsAirlineChart::class,
                AnalyticsRouteChart::class,
                AnalyticsDayOfWeekChart::class,
                AnalyticsForecastWidget::class,
                AnalyticsRouteTable::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}