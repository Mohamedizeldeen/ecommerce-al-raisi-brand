<?php

namespace App\Filament\Resources\Collections\Pages;

use App\Filament\Concerns\HandlesTranslations;
use App\Filament\Resources\Collections\CollectionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCollection extends EditRecord
{
    use HandlesTranslations;

    protected static string $resource = CollectionResource::class;

    /** @var list<string> */
    protected array $arFields = ['name', 'description'];

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
