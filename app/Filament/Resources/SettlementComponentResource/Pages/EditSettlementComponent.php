<?php

namespace App\Filament\Resources\SettlementComponentResource\Pages;

use App\Filament\Resources\SettlementComponentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSettlementComponent extends EditRecord
{
    protected static string $resource = SettlementComponentResource::class;

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
