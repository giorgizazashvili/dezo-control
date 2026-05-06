<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\RestrictedToOfficeManager;
use App\Filament\Resources\UnscannedDevicesReportResource\Pages;
use App\Models\MonitoringLog;
use App\Models\MovementProductPlacementItem;
use App\Models\Organization;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UnscannedDevicesReportResource extends Resource
{
    use RestrictedToOfficeManager;

    protected static ?string $model = MovementProductPlacementItem::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static \UnitEnum|string|null $navigationGroup = 'რეპორტები';

    protected static ?string $navigationLabel = 'დაუსკანირებელი მოწყობილობები';

    protected static ?string $modelLabel = 'ჩანაწერი';

    protected static ?string $pluralModelLabel = 'დაუსკანირებელი მოწყობილობები';

    protected static ?int $navigationSort = 12;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query
                ->whereNotNull('movement_product_placement_items.unique_code')
                ->with(['movement.organization', 'productSettlement'])
                ->addSelect([
                    'last_scan_at' => MonitoringLog::query()
                        ->selectRaw('MAX(created_at)')
                        ->whereColumn('unique_code', 'movement_product_placement_items.unique_code'),
                    'last_scan_type' => MonitoringLog::query()
                        ->select('type')
                        ->whereColumn('unique_code', 'movement_product_placement_items.unique_code')
                        ->latest()
                        ->limit(1),
                ])
            )
            ->columns([
                TextColumn::make('unique_code')
                    ->label('მოწყობილობის ID')
                    ->placeholder('—')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('movement.organization.name')
                    ->label('ობიექტი')
                    ->placeholder('—'),

                TextColumn::make('location_display')
                    ->label('ადგილმდებარეობა')
                    ->getStateUsing(fn (MovementProductPlacementItem $record): string => implode(' / ', array_filter([$record->zone, $record->location])) ?: '—'
                    ),

                TextColumn::make('productSettlement.name')
                    ->label('მოწყობილობა')
                    ->placeholder('—'),

                TextColumn::make('last_scan_at')
                    ->label('ბოლო სკანირება')
                    ->getStateUsing(fn (MovementProductPlacementItem $record): ?string => $record->last_scan_at
                            ? \Carbon\Carbon::parse($record->last_scan_at)->format('d.m.Y H:i')
                            : null
                    )
                    ->placeholder('არ სკანირებულა'),

                TextColumn::make('scan_status')
                    ->label('სკანირების სტატუსი')
                    ->getStateUsing(fn (MovementProductPlacementItem $record): string => $record->last_scan_type ?? 'unscanned'
                    )
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'inspection' => 'შემოწმება',
                        'replacement' => 'ამოცვლა',
                        'unscanned' => 'დაუსკანირებელი',
                        default => $state,
                    })
                    ->color(fn (string $state) => match ($state) {
                        'inspection' => 'success',
                        'replacement' => 'warning',
                        'unscanned' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->defaultSort('unique_code', 'asc')
            ->filters([
                SelectFilter::make('organization')
                    ->label('ობიექტი')
                    ->options(fn () => Organization::orderBy('name')->pluck('name', 'id'))
                    ->query(fn ($query, array $data) => $data['value']
                            ? $query->whereHas('movement', fn ($q) => $q->where('organization_id', $data['value']))
                            : $query
                    ),

                Filter::make('unscanned_only')
                    ->label('მხოლოდ დაუსკანირებელი')
                    ->default()
                    ->query(fn ($query) => $query->whereNotIn(
                        'movement_product_placement_items.unique_code',
                        MonitoringLog::select('unique_code')->whereNotNull('unique_code')
                    )),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUnscannedDevicesReports::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
