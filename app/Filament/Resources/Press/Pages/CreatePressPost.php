<?php

namespace App\Filament\Resources\Press\Pages;

use App\Filament\Concerns\HandlesTranslations;
use App\Filament\Resources\Press\PressResource;
use App\Models\Post;
use Filament\Resources\Pages\CreateRecord;

class CreatePressPost extends CreateRecord
{
    use HandlesTranslations;

    protected static string $resource = PressResource::class;

    /** @var list<string> */
    protected array $arFields = ['title', 'excerpt', 'body', 'meta_title', 'meta_description'];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['type'] = Post::TYPE_PRESS;

        return $this->packTranslations($data);
    }
}
