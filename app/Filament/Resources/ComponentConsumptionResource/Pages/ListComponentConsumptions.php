<?php

namespace App\Filament\Resources\ComponentConsumptionResource\Pages;

use App\Filament\Resources\ComponentConsumptionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListComponentConsumptions extends ListRecords
{
    protected static string $resource = ComponentConsumptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('ახალი გახარჯვა'),
        ];
    }
}
