<?php

namespace App\Enums;

enum Role: string
{
    case Technician = 'technician';
    case OfficeManager = 'office_manager';
    case Admin = 'admin';

    public function label(): string
    {
        return match ($this) {
            Role::Technician => 'ტექნიკოსი',
            Role::OfficeManager => 'ოფის მენეჯერი',
            Role::Admin => 'ადმინი',
        };
    }
}
