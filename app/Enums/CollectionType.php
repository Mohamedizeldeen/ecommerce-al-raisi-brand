<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum CollectionType: string implements HasLabel
{
    case Seasonal = 'seasonal';
    case Capsule = 'capsule';

    public function getLabel(): string
    {
        return ucfirst($this->value);
    }
}
