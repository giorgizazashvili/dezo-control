<?php

namespace App\Filament\Resources\ProductSettlementResource\Pages;

use App\Filament\Resources\ProductSettlementResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductSettlements extends ListRecords
{
    protected static string $resource = ProductSettlementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('ახალი დასახლება'),
        ];
    }
}
