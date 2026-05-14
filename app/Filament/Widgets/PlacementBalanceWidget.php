<?php

namespace App\Filament\Widgets;

use App\Models\Movement;
use App\Models\Organization;
use App\Models\ProductSettlement;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Facades\DB;

class PlacementBalanceWidget extends TableWidget
{
    protected static ?string $heading = 'ობიექტზე განთავსებული მოწყობილობები';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 3;

    private function fetchRecords(array $filters = []): array
    {
        $query = DB::table('organizations')
            ->select([
                DB::raw('CONCAT(organizations.id, "_", product_settlements.id) as `key`'),
                'organizations.id as organization_id',
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
            ->groupBy('organizations.id', 'organizations.name', 'product_settlements.id', 'product_settlements.name')
            ->orderBy('organizations.name');

        if ($orgId = $filters['organization']['organization_id'] ?? null) {
            $query->where('organizations.id', $orgId);
        }

        if ($productId = $filters['product']['product_settlement_id'] ?? null) {
            $query->where('product_settlements.id', $productId);
        }

        $records = [];

        foreach ($query->get() as $row) {
            $records[$row->key] = (array) $row;
        }

        return $records;
    }

    public function table(Table $table): Table
    {
        return $table
            ->records(fn (array $filters): array => $this->fetchRecords($filters))
            ->heading(static::$heading)
            ->columns([
                TextColumn::make('organization_name')
                    ->label('ორგანიზაცია'),

                TextColumn::make('product_name')
                    ->label('მოწყობილობა'),

                TextColumn::make('dimension_name')
                    ->label('განზომილება'),

                TextColumn::make('total_quantity')
                    ->label('რაოდენობა')
                    ->numeric(decimalPlaces: 2),
            ])
            ->actions([])
            ->filters([
                Filter::make('organization')
                    ->form([
                        Select::make('organization_id')
                            ->label('ობიექტი')
                            ->options(Organization::pluck('name', 'id'))
                            ->searchable(),
                    ]),

                Filter::make('product')
                    ->form([
                        Select::make('product_settlement_id')
                            ->label('მოწყობილობა')
                            ->options(ProductSettlement::pluck('name', 'id'))
                            ->searchable(),
                    ]),
            ])
            ->paginated(false);
    }
}
