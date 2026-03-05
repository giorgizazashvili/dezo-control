<?php

namespace App\Filament\Resources\ProductReceiptResource\Pages;

use App\Filament\Resources\ProductReceiptResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductReceipts extends ListRecords
{
    protected static string $resource = ProductReceiptResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('ახალი მიღება'),
        ];
    }
}
