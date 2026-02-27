<?php

namespace App\Filament\Widgets;

use App\Models\Movement;
use App\Models\ProductSettlement;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ProductBalanceWidget extends TableWidget
{
    protected static ?string $heading = 'პროდუქტების ნაშთი';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 2;

    protected function getTableQuery(): Builder
    {
        $receipt   = Movement::OPERATION_PRODUCT_RECEIPT;
        $placement = Movement::OPERATION_PRODUCT_PLACEMENT;

        return ProductSettlement::query()
            ->select([
                'product_settlements.id',
                'product_settlements.name',
                DB::raw('MAX(dimensions.name) as dimension_name'),
                DB::raw("
                    COALESCE(SUM(CASE
                        WHEN m.operation_type = '{$receipt}'   THEN mpi.quantity
                        WHEN m.operation_type = '{$placement}' THEN -mppi.quantity
                        ELSE 0
                    END), 0) as total_quantity
                "),
            ])
            ->leftJoin('dimensions', 'dimensions.id', '=', 'product_settlements.dimension_id')
            ->leftJoin('movement_product_items as mpi', 'mpi.product_settlement_id', '=', 'product_settlements.id')
            ->leftJoin('movements as m', 'm.id', '=', 'mpi.movement_id')
            ->leftJoin('movement_product_placement_items as mppi', 'mppi.product_settlement_id', '=', 'product_settlements.id')
            ->groupBy('product_settlements.id', 'product_settlements.name');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->heading(static::$heading)
            ->columns([
                TextColumn::make('name')
                    ->label('პროდუქტი')
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
