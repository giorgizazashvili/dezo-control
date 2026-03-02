<?php

namespace App\Filament\Widgets;

use App\Models\Dimension;
use App\Models\Movement;
use App\Models\ProductSettlement;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
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
                DB::raw('(SELECT d.name FROM dimensions d WHERE d.id = product_settlements.dimension_id LIMIT 1) as dimension_name'),
                DB::raw("
                    COALESCE((
                        SELECT SUM(mpi.quantity)
                        FROM movement_product_items mpi
                        JOIN movements mr ON mr.id = mpi.movement_id AND mr.operation_type = '{$receipt}'
                        WHERE mpi.product_settlement_id = product_settlements.id
                    ), 0)
                    -
                    COALESCE((
                        SELECT SUM(mppi.quantity)
                        FROM movement_product_placement_items mppi
                        JOIN movements mp ON mp.id = mppi.movement_id AND mp.operation_type = '{$placement}'
                        WHERE mppi.product_settlement_id = product_settlements.id
                    ), 0)
                    as total_quantity
                "),
            ])
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
            ->filters([
                Filter::make('dimension')
                    ->form([
                        Select::make('dimension_id')
                            ->label('განზომილება')
                            ->options(Dimension::pluck('name', 'id'))
                            ->searchable(),
                    ])
                    ->query(fn ($query, array $data) => $query->when(
                        $data['dimension_id'] ?? null,
                        fn ($q) => $q->where('product_settlements.dimension_id', $data['dimension_id'])
                    )),

                Filter::make('positive_only')
                    ->label('მხოლოდ დადებითი ნაშთი')
                    ->toggle()
                    ->query(fn ($query) => $query->havingRaw('total_quantity > 0')),
            ])
            ->actions([])
            ->paginated(false);
    }
}
