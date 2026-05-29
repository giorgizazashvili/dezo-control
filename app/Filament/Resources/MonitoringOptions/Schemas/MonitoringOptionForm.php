<?php

namespace App\Filament\Resources\MonitoringOptions\Schemas;

use App\Filament\Resources\MonitoringOptions\MonitoringOptionResource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MonitoringOptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('type')
                    ->label('კატეგორია')
                    ->options(MonitoringOptionResource::typeOptions())
                    ->required()
                    ->searchable(),

                TextInput::make('name')
                    ->label('სახელი')
                    ->required()
                    ->maxLength(255),
            ]);
    }
}
