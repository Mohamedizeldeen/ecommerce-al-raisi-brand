<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Exports\OrderExporter;
use App\Filament\Resources\Orders\OrderResource;
use Filament\Actions\ExportAction;
use Filament\Resources\Pages\ListRecords;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    // Orders are created by checkout, not in the panel — no Create action.

    protected function getHeaderActions(): array
    {
        return [
            ExportAction::make()->exporter(OrderExporter::class),
        ];
    }
}
