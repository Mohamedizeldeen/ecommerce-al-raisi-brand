<?php

namespace App\Filament\Resources\Blog\Pages;

use App\Filament\Concerns\HandlesTranslations;
use App\Filament\Resources\Blog\BlogResource;
use App\Models\Post;
use Filament\Resources\Pages\CreateRecord;

class CreateBlogPost extends CreateRecord
{
    use HandlesTranslations;

    protected static string $resource = BlogResource::class;

    /** @var list<string> */
    protected array $arFields = ['title', 'excerpt', 'body', 'meta_title', 'meta_description'];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['type'] = Post::TYPE_BLOG;

        return $this->packTranslations($data);
    }
}
