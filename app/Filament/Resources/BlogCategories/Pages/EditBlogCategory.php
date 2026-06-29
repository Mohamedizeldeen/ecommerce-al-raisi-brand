<?php

namespace App\Filament\Resources\BlogCategories\Pages;

use App\Filament\Concerns\HandlesTranslations;
use App\Filament\Resources\BlogCategories\BlogCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBlogCategory extends EditRecord
{
    use HandlesTranslations;

    protected static string $resource = BlogCategoryResource::class;

    /** @var list<string> */
    protected array $arFields = ['name', 'description'];

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
