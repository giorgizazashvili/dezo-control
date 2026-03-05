<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MonitoringLogResource\Pages;
use App\Models\MonitoringLog;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Filament\Forms\Components\DatePicker;

class MonitoringLogResource extends Resource
{
    protected static ?string $model = MonitoringLog::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'მონიტორინგის ლოგი';

    protected static ?string $modelLabel = 'ლოგი';

    protected static ?string $pluralModelLabel = 'მონიტორინგის ლოგი';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('თარიღი')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),

                TextColumn::make('organization.name')
                    ->label('ობიექტი')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('movementProductItem.productSettlement.name')
                    ->label('პროდუქტი')
                    ->placeholder('—'),

                TextColumn::make('type')
                    ->label('ტიპი')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'inspection'  => 'შემოწმება',
                        'replacement' => 'ამოცვლა',
                        default       => $state,
                    })
                    ->color(fn (string $state) => match ($state) {
                        'inspection'  => 'success',
                        'replacement' => 'warning',
                        default       => 'gray',
                    }),

                TextColumn::make('replacedComponent.name')
                    ->label('რა იყო')
                    ->getStateUsing(function (MonitoringLog $record): string {
                        if ($record->type !== 'replacement') {
                            return '—';
                        }
                        return $record->replacedComponent?->name ?? '—';
                    })
                    ->placeholder('—'),

                TextColumn::make('settlementComponent.name')
                    ->label('რითი ჩაანაცვლდა')
                    ->getStateUsing(function (MonitoringLog $record): string {
                        if ($record->type !== 'replacement') {
                            return '—';
                        }
                        return $record->settlementComponent?->name ?? '—';
                    })
                    ->placeholder('—'),

                TextColumn::make('quantity')
                    ->label('რაოდენობა')
                    ->formatStateUsing(fn ($state) => $state
                        ? rtrim(rtrim(number_format((float) $state, 4, '.', ''), '0'), '.')
                        : '—'
                    ),

                TextColumn::make('notes')
                    ->label('შენიშვნა')
                    ->placeholder('—')
                    ->limit(40),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('organization_id')
                    ->label('ობიექტი')
                    ->relationship('organization', 'name'),

                SelectFilter::make('type')
                    ->label('ტიპი')
                    ->options([
                        'inspection'  => 'შემოწმება',
                        'replacement' => 'ამოცვლა',
                    ]),

                Filter::make('created_at')
                    ->label('თარიღის მიხედვით')
                    ->form([
                        DatePicker::make('from')->label('დან'),
                        DatePicker::make('until')->label('მდე'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'],  fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
                            ->when($data['until'], fn ($q, $d) => $q->whereDate('created_at', '<=', $d));
                    }),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMonitoringLogs::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
