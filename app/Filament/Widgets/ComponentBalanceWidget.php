<?php

namespace App\Filament\Widgets;

use App\Models\Dimension;
use App\Models\Movement;
use App\Models\SettlementComponent;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
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
        $receipt     = Movement::OPERATION_COMPONENT_RECEIPT;
        $consumption = Movement::OPERATION_COMPONENT_CONSUMPTION;

        return SettlementComponent::query()
            ->select([
                'settlement_components.id',
                'settlement_components.name',
                DB::raw('MAX(dimensions.name) as dimension_name'),
                DB::raw("
                    COALESCE(SUM(CASE
                        WHEN m.operation_type = '{$receipt}'     THEN mci.quantity
                        WHEN m.operation_type = '{$consumption}' THEN -mci.quantity
                        ELSE 0
                    END), 0) as total_quantity
                "),
            ])
            ->leftJoin('dimensions', 'dimensions.id', '=', 'settlement_components.dimension_id')
            ->leftJoin('movement_component_items as mci', 'mci.settlement_component_id', '=', 'settlement_components.id')
            ->leftJoin('movements as m', 'm.id', '=', 'mci.movement_id')
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
                        fn ($q) => $q->where('settlement_components.dimension_id', $data['dimension_id'])
                    )),

                Filter::make('positive_only')
                    ->label('მხოლოდ დადებითი ნაშთი')
                    ->toggle()
                    ->query(fn ($query) => $query->havingRaw('total_quantity > 0')),
            ])
            ->paginated(false);
    }
}
