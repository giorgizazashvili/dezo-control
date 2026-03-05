<?php

namespace App\Filament\Resources\ComponentReceiptResource\Pages;

use App\Filament\Resources\ComponentReceiptResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListComponentReceipts extends ListRecords
{
    protected static string $resource = ComponentReceiptResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('ახალი მიღება'),
        ];
    }
}
