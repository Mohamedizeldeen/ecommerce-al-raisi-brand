<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Concerns\HandlesTranslations;
use App\Filament\Resources\Categories\CategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCategory extends EditRecord
{
    use HandlesTranslations;

    protected static string $resource = CategoryResource::class;

    /** @var list<string> */
    protected array $arFields = ['name', 'description'];

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
