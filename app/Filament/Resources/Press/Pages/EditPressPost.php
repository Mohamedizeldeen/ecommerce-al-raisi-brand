<?php

namespace App\Filament\Resources\Press\Pages;

use App\Filament\Concerns\HandlesTranslations;
use App\Filament\Resources\Press\PressResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPressPost extends EditRecord
{
    use HandlesTranslations;

    protected static string $resource = PressResource::class;

    /** @var list<string> */
    protected array $arFields = ['title', 'excerpt', 'body', 'meta_title', 'meta_description'];

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
