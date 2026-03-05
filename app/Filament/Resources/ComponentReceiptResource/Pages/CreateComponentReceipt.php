<?php

namespace App\Filament\Resources\ComponentReceiptResource\Pages;

use App\Filament\Resources\ComponentReceiptResource;
use App\Models\Movement;
use Filament\Resources\Pages\CreateRecord;

class CreateComponentReceipt extends CreateRecord
{
    protected static string $resource = ComponentReceiptResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['operation_type'] = Movement::OPERATION_COMPONENT_RECEIPT;
        return $data;
    }
}
