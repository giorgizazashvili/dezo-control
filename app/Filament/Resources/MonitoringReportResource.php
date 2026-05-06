<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\RestrictedToOfficeManager;
use App\Filament\Resources\MonitoringReportResource\Pages;
use App\Models\MonitoringLog;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MonitoringReportResource extends Resource
{
    use RestrictedToOfficeManager;

    protected static ?string $model = MonitoringLog::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static \UnitEnum|string|null $navigationGroup = 'რეპორტები';

    protected static ?string $navigationLabel = 'მონიტორინგის რეპორტი';

    protected static ?string $modelLabel = 'რეპორტი';

    protected static ?string $pluralModelLabel = 'მონიტორინგის რეპორტი';

    protected static ?int $navigationSort = 11;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('unique_code')
                    ->label('მოწყობილობის ID')
                    ->placeholder('—')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('location')
                    ->label('ადგილმდებარეობა')
                    ->getStateUsing(function (MonitoringLog $record): string {
                        $parts = array_filter([$record->zone, $record->location]);

                        return implode(' / ', $parts) ?: '—';
                    })
                    ->placeholder('—')
                    ->searchable(['zone', 'location']),

                TextColumn::make('movementProductItem.productSettlement.name')
                    ->label('მოწყობილობა')
                    ->placeholder('—'),

                TextColumn::make('bait_status')
                    ->label('სატყუარის მდგომარეობა')
                    ->placeholder('—'),

                TextColumn::make('created_at')
                    ->label('სკანირების დრო')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),

                TextColumn::make('type')
                    ->label('სკანირების სტატუსი')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'inspection' => 'შემოწმება',
                        'replacement' => 'ამოცვლა',
                        default => $state,
                    })
                    ->color(fn (string $state) => match ($state) {
                        'inspection' => 'success',
                        'replacement' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('inspection_status')
                    ->label('მოწყობილობის მდგომარეობა')
                    ->placeholder('—')
                    ->badge()
                    ->color('info'),

                TextColumn::make('pest_quantity')
                    ->label('რაოდენობა')
                    ->formatStateUsing(fn ($state) => $state
                        ? rtrim(rtrim(number_format((float) $state, 4, '.', ''), '0'), '.')
                        : '—'
                    )
                    ->placeholder('—'),

                TextColumn::make('risk_level')
                    ->label('რისკის დონე')
                    ->placeholder('—')
                    ->badge()
                    ->color(fn (?string $state) => match ($state) {
                        'მაღალი' => 'danger',
                        'საშუალო' => 'warning',
                        'დაბალი' => 'success',
                        default => 'gray',
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('organization_id')
                    ->label('ობიექტი')
                    ->relationship('organization', 'name'),

                Filter::make('created_at')
                    ->label('თარიღის მიხედვით')
                    ->form([
                        DatePicker::make('from')->label('დან'),
                        DatePicker::make('until')->label('მდე'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
                            ->when($data['until'], fn ($q, $d) => $q->whereDate('created_at', '<=', $d));
                    }),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMonitoringReports::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
