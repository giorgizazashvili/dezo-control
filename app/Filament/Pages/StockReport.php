<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\ComponentBalanceWidget;
use App\Filament\Widgets\PlacementBalanceWidget;
use App\Filament\Widgets\ProductBalanceWidget;
use Filament\Pages\Page;

class StockReport extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'ნაშთების რეპორტი';

    protected static ?string $title = 'ნაშთების რეპორტი';

    protected static ?int $navigationSort = 5;

    protected string $view = 'filament-panels::pages.page';

    protected function getHeaderWidgets(): array
    {
        return [
            ComponentBalanceWidget::class,
            ProductBalanceWidget::class,
            PlacementBalanceWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 1;
    }
}
