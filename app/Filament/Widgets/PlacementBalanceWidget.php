<?php

namespace App\Filament\Widgets;

use App\Models\Movement;
use App\Models\Organization;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class PlacementBalanceWidget extends TableWidget
{
    protected static ?string $heading = 'ობიექტზე განთავსებული პროდუქტები';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 3;

    protected function getTableQuery(): Builder
    {
        return Organization::query()
            ->select([
                'organizations.id',
                'organizations.name as organization_name',
                'product_settlements.id as product_settlement_id',
                'product_settlements.name as product_name',
                DB::raw('MAX(dimensions.name) as dimension_name'),
                DB::raw('SUM(mppi.quantity) as total_quantity'),
            ])
            ->join('movements', 'movements.organization_id', '=', 'organizations.id')
            ->join('movement_product_placement_items as mppi', 'mppi.movement_id', '=', 'movements.id')
            ->join('product_settlements', 'product_settlements.id', '=', 'mppi.product_settlement_id')
            ->leftJoin('dimensions', 'dimensions.id', '=', 'product_settlements.dimension_id')
            ->where('movements.operation_type', Movement::OPERATION_PRODUCT_PLACEMENT)
            ->groupBy(
                'organizations.id',
                'organizations.name',
                'product_settlements.id',
                'product_settlements.name',
            );
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->heading(static::$heading)
            ->columns([
                TextColumn::make('organization_name')
                    ->label('ორგანიზაცია')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('product_name')
                    ->label('პროდუქტი')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('dimension_name')
                    ->label('განზომილება'),

                TextColumn::make('total_quantity')
                    ->label('რაოდენობა')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
            ])
            ->defaultSort('organization_name')
            ->paginated(false);
    }
}
