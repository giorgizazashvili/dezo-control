<?php

namespace App\Filament\Resources\ProductSettlementResource\Pages;

use App\Filament\Resources\ProductSettlementResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProductSettlement extends CreateRecord
{
    protected static string $resource = ProductSettlementResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
