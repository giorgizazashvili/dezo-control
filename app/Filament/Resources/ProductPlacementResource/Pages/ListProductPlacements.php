<?php

namespace App\Filament\Resources\ProductPlacementResource\Pages;

use App\Filament\Resources\ProductPlacementResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductPlacements extends ListRecords
{
    protected static string $resource = ProductPlacementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('ახალი განთავსება'),
        ];
    }
}
