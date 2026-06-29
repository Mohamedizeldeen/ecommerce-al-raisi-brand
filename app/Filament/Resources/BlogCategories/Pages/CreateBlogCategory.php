<?php

namespace App\Filament\Resources\BlogCategories\Pages;

use App\Filament\Concerns\HandlesTranslations;
use App\Filament\Resources\BlogCategories\BlogCategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBlogCategory extends CreateRecord
{
    use HandlesTranslations;

    protected static string $resource = BlogCategoryResource::class;

    /** @var list<string> */
    protected array $arFields = ['name', 'description'];
}
