<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Concerns\HandlesTranslations;
use App\Filament\Resources\Products\ProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    use HandlesTranslations;

    protected static string $resource = ProductResource::class;

    /** @var list<string> */
    protected array $arFields = ['name', 'description', 'fabric'];
}
