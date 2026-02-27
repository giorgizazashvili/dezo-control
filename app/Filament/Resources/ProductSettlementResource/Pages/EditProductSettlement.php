<?php

namespace App\Filament\Resources\ProductSettlementResource\Pages;

use App\Filament\Resources\ProductSettlementResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProductSettlement extends EditRecord
{
    protected static string $resource = ProductSettlementResource::class;

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
