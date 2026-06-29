<?php

namespace App\Filament\Resources\Tags\Pages;

use App\Filament\Concerns\HandlesTranslations;
use App\Filament\Resources\Tags\TagResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTag extends EditRecord
{
    use HandlesTranslations;

    protected static string $resource = TagResource::class;

    /** @var list<string> */
    protected array $arFields = ['name', 'description'];

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
