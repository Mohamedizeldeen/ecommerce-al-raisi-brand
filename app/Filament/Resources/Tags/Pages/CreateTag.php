<?php

namespace App\Filament\Resources\Tags\Pages;

use App\Filament\Concerns\HandlesTranslations;
use App\Filament\Resources\Tags\TagResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTag extends CreateRecord
{
    use HandlesTranslations;

    protected static string $resource = TagResource::class;

    /** @var list<string> */
    protected array $arFields = ['name', 'description'];
}
