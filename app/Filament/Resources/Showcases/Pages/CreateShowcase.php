<?php

namespace App\Filament\Resources\Showcases\Pages;

use App\Filament\Concerns\HandlesTranslations;
use App\Filament\Resources\Showcases\ShowcaseResource;
use Filament\Resources\Pages\CreateRecord;

class CreateShowcase extends CreateRecord
{
    use HandlesTranslations;

    protected static string $resource = ShowcaseResource::class;

    /** @var list<string> */
    protected array $arFields = ['title', 'subtitle', 'description'];
}
