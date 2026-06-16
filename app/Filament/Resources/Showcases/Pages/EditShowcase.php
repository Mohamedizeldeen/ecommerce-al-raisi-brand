<?php

namespace App\Filament\Resources\Showcases\Pages;

use App\Filament\Concerns\HandlesTranslations;
use App\Filament\Resources\Showcases\ShowcaseResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditShowcase extends EditRecord
{
    use HandlesTranslations;

    protected static string $resource = ShowcaseResource::class;

    /** @var list<string> */
    protected array $arFields = ['title', 'subtitle', 'description'];

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
