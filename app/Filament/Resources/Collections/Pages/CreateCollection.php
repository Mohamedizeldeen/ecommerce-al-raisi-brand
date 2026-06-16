<?php

namespace App\Filament\Resources\Collections\Pages;

use App\Filament\Concerns\HandlesTranslations;
use App\Filament\Resources\Collections\CollectionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCollection extends CreateRecord
{
    use HandlesTranslations;

    protected static string $resource = CollectionResource::class;

    /** @var list<string> */
    protected array $arFields = ['name', 'description'];
}
