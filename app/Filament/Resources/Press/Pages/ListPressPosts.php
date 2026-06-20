<?php

namespace App\Filament\Resources\Press\Pages;

use App\Filament\Resources\Press\PressResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPressPosts extends ListRecords
{
    protected static string $resource = PressResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
