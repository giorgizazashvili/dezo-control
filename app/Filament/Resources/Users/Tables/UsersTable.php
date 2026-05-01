<?php

namespace App\Filament\Resources\Users\Tables;

use App\Enums\Role;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('სახელი')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('ელ-ფოსტა')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('role')
                    ->label('როლი')
                    ->badge()
                    ->formatStateUsing(fn (Role $state) => $state->label())
                    ->color(fn (Role $state) => match ($state) {
                        Role::Admin => 'danger',
                        Role::OfficeManager => 'warning',
                        Role::Technician => 'success',
                    }),

                TextColumn::make('created_at')
                    ->label('შექმნილია')
                    ->dateTime('d.m.Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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
