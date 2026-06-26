<?php

namespace App\Filament\Concerns;

/**
 * Restrict a Filament resource/page to full administrators. Fulfilment/support staff
 * (is_admin but role=staff) can still access the panel, but not resources using this.
 */
trait AdminOnly
{
    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->isAdmin();
    }
}
