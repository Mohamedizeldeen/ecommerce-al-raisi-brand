<?php

namespace App\Filament\Exports;

use App\Models\Order;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class OrderExporter extends Exporter
{
    protected static ?string $model = Order::class;

    /**
     * @return array<ExportColumn>
     */
    public static function getColumns(): array
    {
        return [
            ExportColumn::make('order_number')->label('Order number'),
            ExportColumn::make('customer_name')->label('Customer name'),
            ExportColumn::make('customer_email')->label('Customer email'),
            ExportColumn::make('customer_phone')->label('Customer phone'),
            ExportColumn::make('status')->label('Status'),
            ExportColumn::make('payment_status')->label('Payment status'),
            // Money is stored as integer baisa (1 OMR = 1000 baisa).
            ExportColumn::make('total_baisa')
                ->label('Total (OMR)')
                ->formatStateUsing(fn ($state): string => number_format((int) $state / 1000, 3, '.', '')),
            ExportColumn::make('created_at')->label('Created at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = __('Your order export has completed and :count :rows exported.', [
            'count' => number_format($export->successful_rows),
            'rows' => str('row')->plural($export->successful_rows),
        ]);

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.__(':count :rows failed to export.', [
                'count' => number_format($failedRowsCount),
                'rows' => str('row')->plural($failedRowsCount),
            ]);
        }

        return $body;
    }
}
