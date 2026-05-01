<?php

namespace App\Filament\Resources\Concerns;

trait RestrictedToOfficeManager
{
    public static function canViewAny(): bool
    {
        $user = auth()->user();

        return $user?->isAdmin() || $user?->isOfficeManager();
    }
}
