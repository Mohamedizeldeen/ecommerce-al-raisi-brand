<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Products\ProductResource;
use App\Models\ProductVariant;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LowStockVariants extends TableWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Low stock (3 or fewer)')
            ->query(
                ProductVariant::query()
                    ->where('is_active', true)
                    ->where('stock_qty', '<=', 3)
                    ->with('product')
                    ->orderBy('stock_qty')
            )
            ->emptyStateHeading('Everything is well stocked')
            ->emptyStateIcon('heroicon-o-check-circle')
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5)
            ->recordUrl(fn (ProductVariant $record) => $record->product
                ? ProductResource::getUrl('edit', ['record' => $record->product])
                : null)
            ->columns([
                TextColumn::make('product.name')->label('Product')->searchable(),
                TextColumn::make('label')->label('Variant')->placeholder('—'),
                TextColumn::make('sku')->label('SKU')->toggleable(),
                TextColumn::make('stock_qty')
                    ->label('Stock')
                    ->badge()
                    ->color(fn ($state) => (int) $state === 0 ? 'danger' : 'warning')
                    ->sortable(),
            ]);
    }
}
