<?php

namespace App\Filament\Widgets;

use App\Models\SettlementComponent;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ComponentBalanceWidget extends TableWidget
{
    protected static ?string $heading = 'კომპონენტების ნაშთი';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 1;

    protected function getTableQuery(): Builder
    {
        return SettlementComponent::query()
            ->select([
                'settlement_components.id',
                'settlement_components.name',
                DB::raw('MAX(dimensions.name) as dimension_name'),
                DB::raw('COALESCE(SUM(mci.quantity), 0) as total_quantity'),
            ])
            ->leftJoin('dimensions', 'dimensions.id', '=', 'settlement_components.dimension_id')
            ->leftJoin('movement_component_items as mci', 'mci.settlement_component_id', '=', 'settlement_components.id')
            ->groupBy('settlement_components.id', 'settlement_components.name');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->heading(static::$heading)
            ->columns([
                TextColumn::make('name')
                    ->label('კომპონენტი')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('dimension_name')
                    ->label('განზომილება')
                    ->sortable(),

                TextColumn::make('total_quantity')
                    ->label('ნაშთი')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->color(fn ($state) => $state > 0 ? 'success' : 'danger'),
            ])
            ->paginated(false);
    }
}
