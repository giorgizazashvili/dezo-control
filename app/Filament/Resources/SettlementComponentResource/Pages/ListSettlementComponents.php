<?php

namespace App\Filament\Resources\SettlementComponentResource\Pages;

use App\Filament\Resources\SettlementComponentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSettlementComponents extends ListRecords
{
    protected static string $resource = SettlementComponentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('ახალი კომპონენტი'),
        ];
    }
}
