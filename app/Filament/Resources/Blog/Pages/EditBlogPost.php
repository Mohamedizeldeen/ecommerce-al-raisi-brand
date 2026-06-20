<?php

namespace App\Filament\Resources\Blog\Pages;

use App\Filament\Concerns\HandlesTranslations;
use App\Filament\Resources\Blog\BlogResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBlogPost extends EditRecord
{
    use HandlesTranslations;

    protected static string $resource = BlogResource::class;

    /** @var list<string> */
    protected array $arFields = ['title', 'excerpt', 'body', 'meta_title', 'meta_description'];

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
