<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum UserRole: string implements HasLabel
{
    case Admin = 'admin';   // full panel access
    case Staff = 'staff';   // fulfilment/support: orders, contact, reviews only

    public function getLabel(): string
    {
        return ucfirst($this->value);
    }
}
