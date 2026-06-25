<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Concerns\HandlesTranslations;
use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    use HandlesTranslations;

    protected static string $resource = ProductResource::class;

    /** @var list<string> */
    protected array $arFields = ['name', 'description', 'fabric', 'meta_title', 'meta_description'];

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
