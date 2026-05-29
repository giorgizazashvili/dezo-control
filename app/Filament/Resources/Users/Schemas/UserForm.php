<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\Role;
use App\Filament\Forms\Components\SignaturePad;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('სახელი')
                    ->required()
                    ->maxLength(255),

                TextInput::make('email')
                    ->label('ელ-ფოსტა')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                TextInput::make('password')
                    ->label('პაროლი')
                    ->password()
                    ->dehydrateStateUsing(fn (string $state) => bcrypt($state))
                    ->dehydrated(fn (?string $state) => filled($state))
                    ->required(fn (string $operation) => $operation === 'create')
                    ->maxLength(255),

                Select::make('role')
                    ->label('როლი')
                    ->options(collect(Role::cases())->mapWithKeys(fn (Role $r) => [$r->value => $r->label()]))
                    ->required()
                    ->default(Role::Technician->value),

                SignaturePad::make('signature')
                    ->label('ხელმოწერა')
                    ->columnSpanFull(),
            ]);
    }
}
