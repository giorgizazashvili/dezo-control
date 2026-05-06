<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\RestrictedToOfficeManager;
use App\Filament\Resources\PesticideReportResource\Pages;
use App\Models\MonitoringComponentReplacement;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PesticideReportResource extends Resource
{
    use RestrictedToOfficeManager;

    protected static ?string $model = MonitoringComponentReplacement::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-beaker';

    protected static \UnitEnum|string|null $navigationGroup = 'რეპორტები';

    protected static ?string $navigationLabel = 'გამოყენებული პრეპარატები';

    protected static ?string $modelLabel = 'ჩანაწერი';

    protected static ?string $pluralModelLabel = 'გამოყენებული პრეპარატები';

    protected static ?int $navigationSort = 13;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query
                ->with([
                    'monitoring.organization',
                    'monitoring.movementProductItem.productSettlement',
                    'settlementComponent.dimension',
                ])
            )
            ->columns([
                TextColumn::make('monitoring.pest_type')
                    ->label('სამიზნე მავნებელი')
                    ->placeholder('—')
                    ->searchable(),

                TextColumn::make('settlementComponent.name')
                    ->label('პრეპარატი')
                    ->placeholder('—')
                    ->searchable(),

                TextColumn::make('quantity')
                    ->label('რაოდენობა')
                    ->formatStateUsing(fn ($state) => $state
                        ? rtrim(rtrim(number_format((float) $state, 4, '.', ''), '0'), '.')
                        : '—'
                    )
                    ->placeholder('—'),

                TextColumn::make('settlementComponent.dimension.name')
                    ->label('დოზა')
                    ->placeholder('—'),

                TextColumn::make('monitoring.action_taken')
                    ->label('მეთოდი')
                    ->placeholder('—')
                    ->wrap(),

                TextColumn::make('monitoring.movementProductItem.productSettlement.name')
                    ->label('მოწყობილობა')
                    ->placeholder('—'),

                TextColumn::make('monitoring.organization.name')
                    ->label('ობიექტი')
                    ->placeholder('—'),

                TextColumn::make('monitoring.created_at')
                    ->label('თარიღი')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('monitoring_component_replacements.created_at', 'desc')
            ->filters([
                SelectFilter::make('organization')
                    ->label('ობიექტი')
                    ->relationship('monitoring.organization', 'name')
                    ->searchable()
                    ->preload(),

                Filter::make('created_at')
                    ->label('თარიღის მიხედვით')
                    ->form([
                        DatePicker::make('from')->label('დან'),
                        DatePicker::make('until')->label('მდე'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn ($q, $d) => $q->whereHas('monitoring', fn ($mq) => $mq->whereDate('created_at', '>=', $d)))
                            ->when($data['until'], fn ($q, $d) => $q->whereHas('monitoring', fn ($mq) => $mq->whereDate('created_at', '<=', $d)));
                    }),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPesticideReports::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
