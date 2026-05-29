<?php

namespace App\Filament\Resources\MonitoringOptions\Tables;

use App\Filament\Resources\MonitoringOptions\MonitoringOptionResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MonitoringOptionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type')
                    ->label('კატეგორია')
                    ->formatStateUsing(fn (string $state) => MonitoringOptionResource::typeOptions()[$state] ?? $state)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('სახელი')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('კატეგორია')
                    ->options(MonitoringOptionResource::typeOptions()),
            ])
            ->recordActions([
                EditAction::make()->label('რედაქტირება'),
                DeleteAction::make()->label('წაშლა'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('წაშლა'),
                ]),
            ]);
    }
}
