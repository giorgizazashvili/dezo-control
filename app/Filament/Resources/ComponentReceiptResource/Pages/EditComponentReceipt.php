<?php

namespace App\Filament\Resources\ComponentReceiptResource\Pages;

use App\Filament\Resources\ComponentReceiptResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditComponentReceipt extends EditRecord
{
    protected static string $resource = ComponentReceiptResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->label('წაშლა'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
