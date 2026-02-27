<?php

namespace App\Filament\Resources\SettlementComponentResource\Pages;

use App\Filament\Resources\SettlementComponentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSettlementComponent extends CreateRecord
{
    protected static string $resource = SettlementComponentResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
